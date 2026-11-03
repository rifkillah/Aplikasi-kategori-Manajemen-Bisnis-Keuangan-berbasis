<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantUsers;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use CreatesTenantUsers, RefreshDatabase;

    public function test_products_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenantA = $this->makeTenant(['business_name' => 'Toko A']);
        $tenantB = $this->makeTenant(['business_name' => 'Toko B']);
        $ownerA = $this->makeUser($tenantA);
        $this->makeUser($tenantB);

        Product::create([
            'tenant_id' => $tenantA->id,
            'sku' => 'A-001',
            'name' => 'Produk Milik A',
            'selling_price' => 10000,
            'purchase_price' => 7000,
            'stock' => 10,
        ]);

        $productB = Product::create([
            'tenant_id' => $tenantB->id,
            'sku' => 'B-001',
            'name' => 'Produk Milik B',
            'selling_price' => 20000,
            'purchase_price' => 15000,
            'stock' => 8,
        ]);

        $this->actingAs($ownerA)
            ->get('/products')
            ->assertOk()
            ->assertSee('Produk Milik A')
            ->assertDontSee('Produk Milik B');

        $this->actingAs($ownerA)
            ->get('/products/'.$productB->id)
            ->assertNotFound();
    }

    public function test_cashier_cannot_manage_inventory_or_business_finance(): void
    {
        $tenant = $this->makeTenant();
        $cashier = $this->makeUser($tenant, 'kasir');

        $this->actingAs($cashier)->get('/pos')->assertOk();
        $this->actingAs($cashier)->get('/customers')->assertOk();
        $this->actingAs($cashier)->get('/products')->assertForbidden();
        $this->actingAs($cashier)->get('/stock')->assertForbidden();
        $this->actingAs($cashier)->get('/finance')->assertForbidden();
        $this->actingAs($cashier)->get('/reports')->assertForbidden();
        $this->actingAs($cashier)->get('/team')->assertForbidden();
        $this->actingAs($cashier)->get('/settings')->assertForbidden();
    }

    public function test_warehouse_staff_can_manage_inventory_but_not_cashier_or_finance(): void
    {
        $tenant = $this->makeTenant();
        $warehouse = $this->makeUser($tenant, 'gudang');

        $this->actingAs($warehouse)->get('/products')->assertOk();
        $this->actingAs($warehouse)->get('/stock')->assertOk();
        $this->actingAs($warehouse)->get('/pos')->assertForbidden();
        $this->actingAs($warehouse)->get('/transactions')->assertForbidden();
        $this->actingAs($warehouse)->get('/finance')->assertForbidden();
        $this->actingAs($warehouse)->get('/reports')->assertForbidden();
    }

    public function test_owner_can_access_every_business_module(): void
    {
        $tenant = $this->makeTenant();
        $owner = $this->makeUser($tenant);

        foreach (['/pos', '/transactions', '/customers', '/products', '/categories', '/stock', '/finance', '/reports', '/team', '/settings'] as $path) {
            $this->actingAs($owner)->get($path)->assertOk();
        }
    }
}
