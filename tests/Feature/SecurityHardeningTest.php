<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesTenantUsers;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use CreatesTenantUsers, RefreshDatabase;

    public function test_security_headers_and_script_nonce_are_present(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-[^']+'/", $csp);

        preg_match('/script-src ([^;]+)/', $csp, $matches);
        $this->assertStringNotContainsString("'unsafe-inline'", $matches[1] ?? '');

        // Every rendered script tag must carry the nonce; an inline handler or
        // un-nonced <script> would be blocked by the policy at runtime.
        preg_match_all('/<script\b[^>]*>/i', $response->getContent(), $tags);
        $this->assertNotEmpty($tags[0]);

        foreach ($tags[0] as $tag) {
            $this->assertMatchesRegularExpression('/\bnonce="[^"]+"/i', $tag);
        }
    }

    public function test_authenticated_responses_are_not_cacheable(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_deactivated_user_is_signed_out_on_the_next_request(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant, 'owner', ['is_active' => false]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant, 'owner', ['is_active' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_throttled(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Even the correct password is refused while the limiter is active.
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_notification_redirect_cannot_leave_the_application(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\LowStockNotification',
            'data' => ['url' => 'https://evil.example/phish'],
        ]);

        $this->actingAs($owner)
            ->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_relative_notification_redirect_is_kept(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\LowStockNotification',
            'data' => ['url' => '/products?page=2'],
        ]);

        $this->actingAs($owner)
            ->get(route('notifications.read', $notification->id))
            ->assertRedirect('/products?page=2');
    }

    public function test_financial_dashboard_data_is_owner_only(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);
        $cashier = $this->makeUser($tenant, 'kasir');

        Transaction::create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'invoice_number' => 'EXP-1',
            'type' => Transaction::TYPE_EXPENSE,
            'description' => 'Sewa',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'payment_status' => 'paid',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        $this->actingAs($cashier)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Laba bersih');

        $this->actingAs($cashier)->getJson('/dashboard/chart')->assertForbidden();
        $this->actingAs($owner)->getJson('/dashboard/chart')->assertOk();
    }

    public function test_csv_export_neutralises_formula_injection(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        Transaction::create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'invoice_number' => 'EXP-2',
            'type' => Transaction::TYPE_EXPENSE,
            'description' => '=HYPERLINK("https://evil.example","klik")',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'payment_status' => 'paid',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($owner)->get('/reports/export');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $content);
        $this->assertStringNotContainsString("\n=HYPERLINK", $content);
    }

    public function test_stock_movement_cannot_push_stock_below_zero(): void
    {
        Queue::fake();

        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'sku' => 'SAFE-1',
            'name' => 'Produk Aman',
            'unit' => 'pcs',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 3,
            'min_stock_alert' => 0,
            'is_active' => true,
        ]);

        try {
            app(StockService::class)->record($product, $owner, 'out', 4);
            $this->fail('Stock keluar melebihi stok seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_logs', 0);
    }

    public function test_sale_service_rejects_a_customer_from_another_tenant(): void
    {
        Queue::fake();

        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);
        $product = $this->makeProductFor($tenant);
        $otherTenant = $this->makeTenant();
        $foreignCustomer = Customer::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Pelanggan Tenant Lain',
        ]);

        $this->expectException(ValidationException::class);

        app(SaleService::class)->create([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'customer_id' => $foreignCustomer->id,
            'payment_method' => 'cash',
        ], $owner);
    }

    public function test_profile_avatar_rejects_a_disguised_executable(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $this->actingAs($owner)
            ->post('/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('payload.php', 20, 'image/png'),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($owner->fresh()->avatar);
    }

    public function test_transactions_cannot_be_read_across_tenants(): void
    {
        $tenantA = $this->makeTenant();
        $tenantB = $this->makeTenant();
        $ownerA = $this->makeUser($tenantA);
        $ownerB = $this->makeUser($tenantB);

        $transaction = Transaction::create([
            'tenant_id' => $tenantB->id,
            'user_id' => $ownerB->id,
            'invoice_number' => 'INV-FOREIGN-1',
            'type' => Transaction::TYPE_SALE,
            'subtotal' => 10000,
            'total_amount' => 10000,
            'paid_amount' => 10000,
            'payment_status' => 'paid',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        $this->actingAs($ownerA)
            ->get('/transactions/'.$transaction->id)
            ->assertNotFound();

        $this->actingAs($ownerA)
            ->get('/transactions/'.$transaction->id.'/pdf')
            ->assertNotFound();

        $this->actingAs($ownerA)
            ->get('/transactions')
            ->assertOk()
            ->assertDontSee('INV-FOREIGN-1');
    }

    public function test_last_owner_cannot_delete_their_own_account(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $this->actingAs($owner)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNotNull($owner->fresh());
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
    }

    public function test_password_reset_request_does_not_reveal_missing_accounts(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $known = $this->post('/forgot-password', ['email' => $owner->email]);
        $unknown = $this->post('/forgot-password', ['email' => 'tidak-ada@example.com']);

        $known->assertSessionHasNoErrors()->assertSessionHas('status');
        $unknown->assertSessionHasNoErrors()->assertSessionHas('status');
        $this->assertSame(
            $known->getSession()->get('status'),
            $unknown->getSession()->get('status')
        );
    }

    private function makeProductFor(Tenant $tenant): Product
    {
        return Product::create([
            'tenant_id' => $tenant->id,
            'sku' => 'SELL-'.Str::upper(Str::random(6)),
            'name' => 'Produk Uji',
            'unit' => 'pcs',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 5,
            'min_stock_alert' => 0,
            'is_active' => true,
        ]);
    }
}
