<?php

namespace Tests\Feature;

use App\Jobs\SendLowStockNotificationJob;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesTenantUsers;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use CreatesTenantUsers, RefreshDatabase;

    private function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'sku' => 'SKU-'.uniqid(),
            'name' => 'Kopi Susu',
            'unit' => 'cup',
            'purchase_price' => 60000,
            'selling_price' => 100000,
            'stock' => 10,
            'min_stock_alert' => 3,
            'is_active' => true,
        ], $attributes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->makeTenant(['tax_percent' => 11, 'invoice_prefix' => 'KSN']);
        $this->owner = $this->makeUser($this->tenant);
    }

    public function test_sale_persists_transaction_details_and_reduces_stock_atomically(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $service = app(SaleService::class);

        $transaction = $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
        ], $this->owner);

        $this->assertSame(Transaction::TYPE_SALE, $transaction->type);
        $this->assertSame('completed', $transaction->status);
        $this->assertSame('paid', $transaction->payment_status);

        // 2 x 100.000 + 11% tax
        $this->assertEquals(200000, (float) $transaction->subtotal);
        $this->assertEquals(22000, (float) $transaction->tax);
        $this->assertEquals(222000, (float) $transaction->total_amount);
        $this->assertEquals(222000, (float) $transaction->paid_amount);

        $this->assertStringStartsWith('KSN-'.now()->format('Ymd').'-', $transaction->invoice_number);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100000,
        ]);

        $this->assertSame(8, $product->fresh()->stock);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type' => 'sale',
            'qty' => -2,
            'stock_before' => 10,
            'stock_after' => 8,
        ]);
    }

    public function test_sale_discount_is_applied_before_tax(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $service = app(SaleService::class);

        $transaction = $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'discount' => 10000,
            'payment_method' => 'cash',
        ], $this->owner);

        // 100.000 - 10.000 = 90.000; tax 11% = 9.900; total = 99.900
        $this->assertEquals(10000, (float) $transaction->discount);
        $this->assertEquals(9900, (float) $transaction->tax);
        $this->assertEquals(99900, (float) $transaction->total_amount);
    }

    public function test_sale_rejects_quantity_above_available_stock(): void
    {
        Queue::fake();

        $product = $this->makeProduct(['stock' => 2]);
        $service = app(SaleService::class);

        $this->expectException(ValidationException::class);

        $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'payment_method' => 'cash',
        ], $this->owner);

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_low_stock_job_is_dispatched_when_stock_reaches_minimum(): void
    {
        Queue::fake();

        $product = $this->makeProduct(['stock' => 5, 'min_stock_alert' => 3]);
        $service = app(SaleService::class);

        $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'payment_method' => 'cash',
        ], $this->owner);

        $this->assertSame(2, $product->fresh()->stock);
        Queue::assertPushed(SendLowStockNotificationJob::class, function (SendLowStockNotificationJob $job) use ($product) {
            return $job->productId === $product->id;
        });
    }

    public function test_void_restores_stock_and_marks_transaction_as_void(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $service = app(SaleService::class);

        $transaction = $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
            'payment_method' => 'cash',
        ], $this->owner);

        $this->assertSame(6, $product->fresh()->stock);

        $service->void($transaction, $this->owner);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('void', $transaction->fresh()->status);
        $this->assertNotNull($transaction->fresh()->voided_at);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type' => 'void',
            'qty' => 4,
            'stock_after' => 10,
        ]);
    }

    public function test_voiding_the_same_transaction_twice_is_rejected(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $service = app(SaleService::class);

        $transaction = $service->create([
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'payment_method' => 'cash',
        ], $this->owner);

        $service->void($transaction, $this->owner);

        $this->expectException(ValidationException::class);

        $service->void($transaction->fresh(), $this->owner);
    }

    public function test_invoice_numbers_are_sequential_per_day(): void
    {
        Queue::fake();

        $product = $this->makeProduct(['stock' => 100]);
        $service = app(SaleService::class);

        Carbon::setTestNow('2026-11-02 10:00:00');

        $first = $service->create([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
        ], $this->owner);

        $second = $service->create([
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
        ], $this->owner);

        $this->assertSame('KSN-20261102-0001', $first->invoice_number);
        $this->assertSame('KSN-20261102-0002', $second->invoice_number);

        Carbon::setTestNow();
    }
}
