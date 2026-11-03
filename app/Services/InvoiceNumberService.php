<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class InvoiceNumberService
{
    public function next(Tenant $tenant, ?string $prefix = null, ?Carbon $date = null): string
    {
        $date ??= now();
        $prefix = strtoupper($prefix ?: $tenant->invoice_prefix ?: 'INV');
        $sequence = Transaction::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereDate('transaction_date', $date->toDateString())
            ->count() + 1;

        do {
            $number = sprintf('%s-%s-%04d', $prefix, $date->format('Ymd'), $sequence);
            $exists = Transaction::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('invoice_number', $number)
                ->exists();
            $sequence++;
        } while ($exists);

        return $number;
    }
}
