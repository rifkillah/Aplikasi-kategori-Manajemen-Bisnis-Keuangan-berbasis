<?php

namespace Tests\Feature\Auth;

use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_get_their_own_workspace(): void
    {
        $response = $this->post('/register', [
            'business_name' => 'Warung Nusantara',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $tenant = Tenant::where('business_name', 'Warung Nusantara')->firstOrFail();

        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertTrue($user->hasRole('owner'));
        $this->assertSame(4, Category::where('tenant_id', $tenant->id)->count());
    }

    public function test_business_name_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('business_name');
        $this->assertGuest();
    }
}
