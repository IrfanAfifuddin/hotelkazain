<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_LENGTH_MESSAGE = 'Password melebihi batas maksimal 64 karakter yang diizinkan.';

    private const INVALID_CHARACTER_MESSAGE = 'Password mengandung karakter yang tidak diizinkan. Gunakan huruf, angka, spasi, dan simbol standar.';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'username' => 'admin_kazain',
            'password' => 'P@ssw0rd!_-',
            'role' => 'Admin',
        ]);
    }

    public function test_password_longer_than_maximum_length_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => str_repeat('a', 65),
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => self::MAX_LENGTH_MESSAGE]);

        $this->assertGuest();
    }

    public function test_password_with_control_character_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => "pass\0word",
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => self::INVALID_CHARACTER_MESSAGE]);

        $this->assertGuest();
    }

    public function test_password_sent_as_array_is_rejected_without_server_error(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => ['P@ssw0rd!_-'],
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => 'Password harus berupa teks.']);

        $this->assertGuest();
    }

    public function test_password_with_non_ascii_character_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'pässword',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => self::INVALID_CHARACTER_MESSAGE]);

        $this->assertGuest();
    }

    public function test_password_with_trailing_newline_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => "P@ssw0rd!_-\n",
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => self::INVALID_CHARACTER_MESSAGE]);

        $this->assertGuest();
    }

    public function test_password_at_maximum_length_can_still_login(): void
    {
        $password = str_repeat('a', 64);

        $user = User::factory()->create([
            'username' => 'manager_kazain',
            'password' => $password,
            'role' => 'Admin',
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => $password,
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_with_allowed_symbols_and_spaces_can_still_login(): void
    {
        $user = User::factory()->create([
            'username' => 'receptionist_kazain',
            'password' => 'P@ss w0rd!_-',
            'role' => 'Receptionist',
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'P@ss w0rd!_-',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_format_does_not_consume_rate_limit_attempts(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'username' => $this->user->username,
                    'password' => str_repeat('a', 65),
                ])
                ->assertSessionHasErrors('password');
        }

        $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'P@ssw0rd!_-',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->user);
    }
}
