<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const THROTTLE_MESSAGE = 'Terlalu banyak percobaan login';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'username' => 'admin_kazain',
            'password' => 'password',
            'role' => 'Admin',
        ]);
    }

    public function test_login_is_blocked_after_three_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'username' => $this->user->username,
                    'password' => 'wrong-password',
                ])
                ->assertRedirect('/login')
                ->assertSessionHasErrors(['username' => 'Username atau Password Salah']);
        }

        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString(self::THROTTLE_MESSAGE, $this->sessionErrorMessage('username'));
        $this->assertGuest();
    }

    public function test_login_page_displays_throttle_message(): void
    {
        $this->view('auth.login', [
            'errors' => (new ViewErrorBag)->put('default', new MessageBag([
                'username' => ['Terlalu banyak percobaan login. Silakan coba lagi dalam 120 detik.'],
            ])),
        ])->assertSee(self::THROTTLE_MESSAGE);
    }

    public function test_login_remains_blocked_for_two_minutes(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->from('/login')->post('/login', [
                'username' => $this->user->username,
                'password' => 'wrong-password',
            ]);
        }

        $this->travel(119)->seconds();

        $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();

        $this->travel(2)->seconds();

        $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'username' => $this->user->username,
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors(['username' => 'Username atau Password Salah']);
        }

        $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->user);

        $this->post('/logout')->assertRedirect('/login');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'username' => $this->user->username,
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors(['username' => 'Username atau Password Salah']);
        }

        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $this->assertStringContainsString(self::THROTTLE_MESSAGE, $this->sessionErrorMessage('username'));
        $this->assertGuest();
    }

    private function sessionErrorMessage(string $field): string
    {
        $errors = session('errors');

        if (is_array($errors)) {
            return $errors['default']['messages'][$field][0];
        }

        return $errors->first($field);
    }
}
