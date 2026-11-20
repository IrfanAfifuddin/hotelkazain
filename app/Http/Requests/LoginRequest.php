<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Maximum number of failed login attempts before the account/IP is throttled.
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Number of seconds the login throttle remains active.
     */
    private const DECAY_SECONDS = 120;

    /**
     * Maximum number of characters allowed in a password.
     *
     * Kept below Bcrypt's 72-byte limit so passwords are never silently truncated.
     */
    private const MAX_PASSWORD_LENGTH = 64;

    /**
     * Allowed password characters: printable ASCII, including spaces.
     *
     * This rejects control characters (including null bytes) and non-ASCII input
     * before it reaches the hasher, preventing runtime errors during authentication.
     */
    private const PASSWORD_PATTERN = '/\A[\x20-\x7E]+\z/';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => [
                'bail',
                'required',
                'string',
                'max:'.self::MAX_PASSWORD_LENGTH,
                'regex:'.self::PASSWORD_PATTERN,
            ],
        ];
    }

    /**
     * Get custom validation messages for the login request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.max' => 'Password melebihi batas maksimal '.self::MAX_PASSWORD_LENGTH.' karakter yang diizinkan.',
            'password.regex' => 'Password mengandung karakter yang tidak diizinkan. Gunakan huruf, angka, spasi, dan simbol standar.',
        ];
    }

    /**
     * Ensure the login request has not exceeded the failed attempt limit.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam '.$seconds.' detik.',
        ]);
    }

    /**
     * Record a failed login attempt.
     */
    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);
    }

    /**
     * Clear the failed login attempts after a successful login.
     */
    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Get the rate limiting key for the username and IP address.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('username')).'|'.$this->ip(),
        );
    }
}
