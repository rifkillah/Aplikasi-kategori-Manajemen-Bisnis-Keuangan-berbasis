<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_SALE = 'sale';

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'invoice_number',
        'type',
        'description',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'status',
        'transaction_date',
        'due_date',
        'notes',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'transaction_date' => 'datetime',
            'due_date' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSales(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SALE);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('transaction_date', '<=', $to));
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sale' => 'Penjualan',
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            default => ucfirst($this->type),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Sebagian',
            'unpaid' => 'Belum Bayar',
            default => ucfirst($this->payment_status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'other' => 'Lainnya',
            default => $this->payment_method ? ucfirst($this->payment_method) : '—',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            default => 'gray',
        };
    }

    public function getIsVoidAttribute(): bool
    {
        return $this->status === 'void';
    }

    public function getGrossProfitAttribute(): float
    {
        return (float) $this->details->sum(function (TransactionDetail $detail) {
            return (float) $detail->subtotal - ((float) $detail->purchase_price * $detail->quantity);
        });
    }
}
