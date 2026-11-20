<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domain Email yang Diblokir
    |--------------------------------------------------------------------------
    |
    | Domain di bawah ini tetap ditolak meskipun memiliki DNS record aktif.
    | Gunakan daftar ini untuk domain parkir, domain sementara, atau domain
    | lain yang tidak boleh dipakai sebagai email pelanggan.
    |
    */

    'blocked_email_domains' => [
        'mial.com',
        'moal.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Email Domain Policy
    |--------------------------------------------------------------------------
    |
    | Fallback policy when an email domain is not explicitly listed in
    | blocked_email_domains. Set to true to block unlisted domains,
    | false to allow all unlisted domains.
    |
    */
    'default_block_email_domain' => false,

];
