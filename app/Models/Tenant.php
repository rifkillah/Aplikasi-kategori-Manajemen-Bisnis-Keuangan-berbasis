<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'slug',
        'logo',
        'address',
        'phone',
        'email',
        'invoice_prefix',
        'currency',
        'tax_percent',
    ];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function owners(): HasMany
    {
        return $this->users()
            ->whereHas('roles', fn ($query) => $query->where('name', 'owner'));
    }
}
