<?php

namespace App\Services;

use App\Jobs\SendLowStockNotificationJob;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private InvoiceNumberService $invoiceNumbers) {}

    /**
     * Create a sale, persist line items and reduce stock atomically.
     *
     * @param  array{items: array<int, array{product_id: int, quantity: int}>, customer_id?: int|null, discount?: float, paid_amount?: float, payment_method?: string, notes?: string|null, transaction_date?: string|null}  $data
     */
    public function create(array $data, User $user): Transaction
    {
        $lowStockProducts = collect();

        $transaction = DB::transaction(function () use ($data, $user, &$lowStockProducts) {
            $tenant = Tenant::findOrFail($user->tenant_id);
            $items = collect($data['items'])->filter(fn ($item) => ! empty($item['product_id']));

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Keranjang belanja masih kosong.']);
            }

            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages(['items' => 'Salah satu produk tidak ditemukan pada bisnis Anda.']);
            }

            $subtotal = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $quantity = (int) ($item['quantity'] ?? 0);
                $product = $products->get($item['product_id']);

                if ($quantity < 1) {
                    throw ValidationException::withMessages(['items' => "Jumlah untuk {$product->name} harus lebih dari 0."]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi (tersisa {$product->stock} {$product->unit}).",
                    ]);
                }

                $lineSubtotal = (float) $product->selling_price * $quantity;
                $subtotal += $lineSubtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => (float) $product->selling_price,
                    'purchase_price' => (float) $product->purchase_price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = min((float) ($data['discount'] ?? 0), $subtotal);
            $taxPercent = (float) $tenant->tax_percent;
            $tax = round(($subtotal - $discount) * $taxPercent / 100, 2);
            $total = round($subtotal - $discount + $tax, 2);
            $paid = array_key_exists('paid_amount', $data) && $data['paid_amount'] !== null
                ? (float) $data['paid_amount']
                : $total;

            $paymentStatus = match (true) {
                $paid >= $total => 'paid',
                $paid > 0 => 'partial',
                default => 'unpaid',
            };

            $transaction = Transaction::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_number' => $this->invoiceNumbers->next($tenant),
                'type' => Transaction::TYPE_SALE,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total,
                'paid_amount' => max(0, $paid),
                'payment_status' => $paymentStatus,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => 'completed',
                'transaction_date' => isset($data['transaction_date'])
                    ? Carbon::parse($data['transaction_date'])
                    : now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($preparedItems as $prepared) {
                $product = $prepared['product'];
                $before = $product->stock;
                $after = $before - $prepared['quantity'];

                $transaction->details()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $prepared['quantity'],
                    'price' => $prepared['price'],
                    'purchase_price' => $prepared['purchase_price'],
                    'subtotal' => $prepared['subtotal'],
                ]);

                $product->update(['stock' => $after]);

                StockLog::create([
                    'tenant_id' => $user->tenant_id,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'sale',
                    'qty' => -$prepared['quantity'],
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference_type' => Transaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => 'Penjualan '.$transaction->invoice_number,
                ]);

                if ($after <= $product->min_stock_alert) {
                    $lowStockProducts->push($product->fresh());
                }
            }

            return $transaction;
        });

        $this->notifyLowStock($lowStockProducts);

        return $transaction;
    }

    public function void(Transaction $transaction, User $user): void
    {
        if ($transaction->type !== Transaction::TYPE_SALE || $transaction->is_void) {
            throw ValidationException::withMessages(['transaction' => 'Transaksi ini tidak dapat dibatalkan.']);
        }

        DB::transaction(function () use ($transaction, $user) {
            $transaction->load('details');

            foreach ($transaction->details as $detail) {
                $product = Product::withTrashed()->whereKey($detail->product_id)->lockForUpdate()->first();

                if (! $product) {
                    continue;
                }

                $before = $product->stock;
                $after = $before + $detail->quantity;

                $product->update(['stock' => $after]);

                StockLog::create([
                    'tenant_id' => $transaction->tenant_id,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'void',
                    'qty' => $detail->quantity,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference_type' => Transaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => 'Pembatalan '.$transaction->invoice_number,
                ]);
            }

            $transaction->update([
                'status' => 'void',
                'voided_at' => now(),
            ]);
        });
    }

    private function notifyLowStock(Collection $products): void
    {
        $products->unique('id')->each(function (Product $product) {
            SendLowStockNotificationJob::dispatch($product->id)
                ->onQueue('notifications');
        });
    }
}
