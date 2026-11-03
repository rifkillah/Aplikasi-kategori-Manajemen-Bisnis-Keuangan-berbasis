<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

trait CreatesTenantUsers
{
    protected function makeTenant(array $attributes = []): Tenant
    {
        $name = $attributes['business_name'] ?? 'Toko '.Str::random(6);

        return Tenant::create(array_merge([
            'business_name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'invoice_prefix' => 'TST',
            'tax_percent' => 11,
        ], $attributes));
    }

    protected function makeUser(Tenant $tenant, string $role = 'owner', array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ], $attributes));

        $user->assignRole($role);

        return $user;
    }
}
