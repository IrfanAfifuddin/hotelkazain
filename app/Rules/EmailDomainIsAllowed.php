<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class EmailDomainIsAllowed implements ValidationRule
{
    /**
     * Tolak domain email yang secara DNS aktif tetapi tidak boleh digunakan.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $domain = Str::lower(Str::afterLast($value, '@'));

        if (in_array($domain, config('booking.blocked_email_domains', []), true)) {
            $fail('Domain email tidak valid atau tidak aktif.');
        }
    }
}
