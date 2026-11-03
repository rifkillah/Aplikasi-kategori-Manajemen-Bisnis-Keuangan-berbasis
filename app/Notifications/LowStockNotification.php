<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Stok Menipis: '.$this->product->name)
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Stok produk **{$this->product->name}** ({$this->product->sku}) sudah menipis.")
            ->line("Stok saat ini: **{$this->product->stock} {$this->product->unit}** — batas minimum: {$this->product->min_stock_alert} {$this->product->unit}.")
            ->action('Buka Inventaris', route('products.show', $this->product))
            ->line('Segera lakukan restock agar penjualan tidak terhambat.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'stock' => $this->product->stock,
            'min_stock_alert' => $this->product->min_stock_alert,
            'message' => "Stok {$this->product->name} tinggal {$this->product->stock} {$this->product->unit}.",
            'url' => route('products.show', $this->product),
        ];
    }
}
