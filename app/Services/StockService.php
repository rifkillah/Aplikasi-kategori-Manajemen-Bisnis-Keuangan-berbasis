<?php

namespace App\Services;

use App\Jobs\SendLowStockNotificationJob;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Record a stock movement.
     *
     * @param  string  $type  in|out|adjustment
     * @param  int  $quantity  positive value; for adjustment this is the new physical stock
     */
    public function record(Product $product, User $user, string $type, int $quantity, ?string $notes = null): StockLog
    {
        if (! in_array($type, ['in', 'out', 'adjustment'], true)) {
            throw ValidationException::withMessages(['type' => 'Jenis pergerakan stok tidak valid.']);
        }

        $log = DB::transaction(function () use ($product, $user, $type, $quantity, $notes) {
            // Re-read under the row lock so two concurrent movements cannot both
            // pass the stock check against a stale value and drive it negative.
            $locked = Product::query()
                ->where('tenant_id', $product->tenant_id)
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($type === 'out' && $locked->stock < abs($quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$locked->name} hanya tersisa {$locked->stock} {$locked->unit}.",
                ]);
            }

            if ($type === 'adjustment' && $quantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok fisik tidak boleh negatif.']);
            }

            $delta = match ($type) {
                'in' => abs($quantity),
                'out' => -abs($quantity),
                'adjustment' => $quantity - $locked->stock,
            };

            $before = $locked->stock;
            $after = $before + $delta;

            $locked->update(['stock' => $after]);

            return StockLog::create([
                'tenant_id' => $locked->tenant_id,
                'product_id' => $locked->id,
                'user_id' => $user->id,
                'type' => $type,
                'qty' => $delta,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $notes,
            ]);
        });

        if ($product->fresh()->is_low_stock) {
            SendLowStockNotificationJob::dispatch($product->id)->onQueue('notifications');
        }

        return $log;
    }
}
