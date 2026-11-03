<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        [$user, $tenant] = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'business_name' => $request->business_name,
                'slug' => $this->uniqueSlug($request->business_name),
                'email' => $request->email,
                'invoice_prefix' => 'INV',
                'tax_percent' => 0,
            ]);

            foreach (['Umum', 'Makanan', 'Minuman', 'Elektronik'] as $category) {
                Category::create([
                    'tenant_id' => $tenant->id,
                    'name' => $category,
                ]);
            }

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'is_active' => true,
            ]);

            Role::findOrCreate('owner', 'web');
            $user->assignRole('owner');

            return [$user, $tenant];
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', "Selamat datang di Kasirin! Bisnis {$tenant->business_name} siap digunakan.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'bisnis';
        $slug = $base;
        $counter = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
