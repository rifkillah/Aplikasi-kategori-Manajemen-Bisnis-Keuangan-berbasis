<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public int $productId)
    {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $product = Product::withoutGlobalScopes()->find($this->productId);

        if (! $product || $product->is_active === false || $product->stock > $product->min_stock_alert) {
            return;
        }

        $alreadyNotified = StockAlert::where('product_id', $product->id)
            ->where('notified_at', '>=', now()->subHours(6))
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $owners = User::query()
            ->where('tenant_id', $product->tenant_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'owner'))
            ->get();

        if ($owners->isEmpty()) {
            return;
        }

        Notification::send($owners, new LowStockNotification($product));

        StockAlert::create([
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
            'stock' => $product->stock,
            'min_stock_alert' => $product->min_stock_alert,
            'notified_at' => now(),
        ]);
    }
}
