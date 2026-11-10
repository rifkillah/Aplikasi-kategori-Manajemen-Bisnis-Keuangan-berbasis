<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_appearance_settings(): void
    {
        $this->get('/appearance')->assertRedirect('/login');
    }

    public function test_appearance_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/appearance')
            ->assertOk()
            ->assertSee('Mode warna')
            ->assertSee('Wallpaper kustom');
    }

    public function test_theme_mode_and_background_preset_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/appearance', [
                'theme_mode' => 'dark',
                'background_preset' => 'ocean',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/appearance');

        $user->refresh();

        $this->assertSame('dark', $user->theme_mode);
        $this->assertSame('ocean', $user->background_preset);
        $this->assertNull($user->background_image);
    }

    public function test_uploading_a_wallpaper_switches_to_custom_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/appearance', [
                'theme_mode' => 'system',
                'background_preset' => 'aurora',
                'wallpaper' => UploadedFile::fake()->image('wallpaper.jpg', 1600, 900),
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('custom', $user->background_preset);
        $this->assertNotNull($user->background_image);
        Storage::disk('public')->assertExists($user->background_image);
    }

    public function test_removing_a_wallpaper_deletes_the_file_and_falls_back_to_aurora(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('wallpaper.jpg')->store('wallpapers', 'public');

        $user = User::factory()->create([
            'background_preset' => 'custom',
            'background_image' => $path,
        ]);

        $this->actingAs($user)
            ->patch('/appearance', [
                'theme_mode' => 'system',
                'background_preset' => 'custom',
                'remove_wallpaper' => 1,
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertNull($user->background_image);
        $this->assertSame('aurora', $user->background_preset);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_custom_preset_without_a_wallpaper_falls_back_to_aurora(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/appearance', [
                'theme_mode' => 'light',
                'background_preset' => 'custom',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('aurora', $user->refresh()->background_preset);
    }

    public function test_quick_theme_toggle_endpoint_updates_the_mode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/appearance/theme', ['theme_mode' => 'dark'])
            ->assertOk()
            ->assertJson(['theme_mode' => 'dark']);

        $this->assertSame('dark', $user->refresh()->theme_mode);
    }

    public function test_invalid_theme_mode_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/appearance/theme', ['theme_mode' => 'neon'])
            ->assertUnprocessable();

        $this->assertSame('system', $user->refresh()->theme_mode);
    }
}
