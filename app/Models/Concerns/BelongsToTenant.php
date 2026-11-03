<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->hasUser() && auth()->user()->tenant_id) {
                $builder->where(
                    $builder->getModel()->getTable().'.tenant_id',
                    auth()->user()->tenant_id
                );
            }
        });

        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->hasUser()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
