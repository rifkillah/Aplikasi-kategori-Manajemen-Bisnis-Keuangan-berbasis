<?php

namespace Tests\Feature;

use App\Jobs\SendLowStockNotificationJob;
use App\Models\Product;
use App\Models\StockAlert;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesTenantUsers;
use Tests\TestCase;

class LowStockNotificationJobTest extends TestCase
{
    use CreatesTenantUsers, RefreshDatabase;

    public function test_it_notifies_owners_and_records_a_stock_alert(): void
    {
        Notification::fake();

        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant, 'owner');
        $kasir = $this->makeUser($tenant, 'kasir');

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'sku' => 'LOW-001',
            'name' => 'Biji Kopi',
            'selling_price' => 50000,
            'purchase_price' => 30000,
            'stock' => 2,
            'min_stock_alert' => 5,
        ]);

        (new SendLowStockNotificationJob($product->id))->handle();

        Notification::assertSentTo($owner, LowStockNotification::class);
        Notification::assertNotSentTo($kasir, LowStockNotification::class);

        $this->assertDatabaseHas('stock_alerts', [
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'stock' => 2,
            'min_stock_alert' => 5,
        ]);
    }

    public function test_it_does_not_send_duplicate_notifications_within_six_hours(): void
    {
        Notification::fake();

        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'sku' => 'LOW-002',
            'name' => 'Gula Aren',
            'selling_price' => 20000,
            'purchase_price' => 12000,
            'stock' => 1,
            'min_stock_alert' => 4,
        ]);

        (new SendLowStockNotificationJob($product->id))->handle();
        (new SendLowStockNotificationJob($product->id))->handle();

        Notification::assertSentToTimes($owner, LowStockNotification::class, 1);
        $this->assertSame(1, StockAlert::where('product_id', $product->id)->count());
    }

    public function test_it_does_nothing_when_stock_is_safe(): void
    {
        Notification::fake();

        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'sku' => 'SAFE-001',
            'name' => 'Susu UHT',
            'selling_price' => 18000,
            'purchase_price' => 14000,
            'stock' => 40,
            'min_stock_alert' => 10,
        ]);

        (new SendLowStockNotificationJob($product->id))->handle();

        Notification::assertNotSentTo($owner, LowStockNotification::class);
        $this->assertSame(0, StockAlert::where('product_id', $product->id)->count());
    }
}
