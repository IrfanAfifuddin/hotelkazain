<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_get_logout_returns_clean_405_page_regardless_of_debug_setting(bool $debug): void
    {
        config(['app.debug' => $debug]);

        $user = User::factory()->create([
            'role' => 'Admin',
        ]);

        $response = $this->actingAs($user)->get('/logout');

        $response->assertStatus(405)
            ->assertSeeText('405')
            ->assertSeeText('Metode Tidak Diizinkan')
            ->assertSeeText('Kembali ke Dashboard')
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertDontSeeText('Kembali ke Login')
            ->assertDontSeeText('Halaman Utama')
            ->assertDontSee('MethodNotAllowedHttpException')
            ->assertDontSee('Symfony\Component\HttpKernel\Exception')
            ->assertDontSee('Stack trace')
            ->assertDontSee('vendor/laravel');

        $this->assertAuthenticatedAs($user);
    }

    #[TestWith(['Admin', 'admin.dashboard'])]
    #[TestWith(['Receptionist', 'receptionist.dashboard'])]
    public function test_get_logout_dashboard_button_targets_role_specific_dashboard(string $role, string $routeName): void
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $this->actingAs($user)
            ->get('/logout')
            ->assertStatus(405)
            ->assertSeeText('Kembali ke Dashboard')
            ->assertSee('href="'.route($routeName).'"', false)
            ->assertDontSeeText('Kembali ke Login')
            ->assertDontSeeText('Halaman Utama');
    }

    public function test_get_logout_dashboard_button_targets_login_for_guest(): void
    {
        $this->get('/logout')
            ->assertStatus(405)
            ->assertSeeText('Kembali ke Dashboard')
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSeeText('Kembali ke Login')
            ->assertDontSeeText('Halaman Utama');
    }

    public function test_post_logout_logs_out_authenticated_user(): void
    {
        $user = User::factory()->create([
            'role' => 'Admin',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
