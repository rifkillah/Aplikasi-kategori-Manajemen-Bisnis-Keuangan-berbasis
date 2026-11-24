<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'attacker@example.com',
            ])
            ->assertSessionHasErrorsIn('default', 'current_password')
            ->assertRedirect('/profile');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_changing_email_rejects_a_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'attacker@example.com',
                'current_password' => 'wrong-password',
            ])
            ->assertSessionHasErrorsIn('default', 'current_password')
            ->assertRedirect('/profile');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatar_url);
    }

    public function test_uploaded_file_urls_follow_the_current_request_host(): void
    {
        $user = User::factory()->create([
            'avatar' => 'avatars/photo.jpg',
            'background_image' => 'wallpapers/background.jpg',
            'background_preset' => 'custom',
        ]);

        // Regression: URLs used to be pinned to APP_URL, so opening the app on
        // another host (127.0.0.1, LAN IP, custom domain) made the browser
        // request images from the wrong origin and CSP blocked them.
        $response = $this->actingAs($user)->get('http://127.0.0.1:8000/profile');

        $response->assertOk();
        $response->assertSee('src="/storage/avatars/photo.jpg"', false);
        $response->assertSee('/storage/wallpapers/background.jpg', false);
        $response->assertDontSee('http://localhost/storage/avatars/photo.jpg', false);
    }

    public function test_uploading_a_new_photo_replaces_the_previous_one(): void
    {
        Storage::fake('public');

        $old = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');

        $user = User::factory()->create(['avatar' => $old]);

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('new.jpg', 400, 400),
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_remove_their_profile_photo(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('avatar.jpg')->store('avatars', 'public');

        $user = User::factory()->create(['avatar' => $path]);

        $this->actingAs($user)
            ->delete('/profile/avatar')
            ->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_profile_photo_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('notes.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->refresh()->avatar);
    }
}
