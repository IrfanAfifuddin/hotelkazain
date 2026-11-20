<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#d1d5db">
    <title>405 — Metode Tidak Diizinkan — Kazain Hotel</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="min-h-screen bg-gray-300 font-sans text-kazain-text antialiased">
    <main class="flex min-h-screen items-center justify-center px-3 py-6 sm:px-6 sm:py-10">
        <section class="w-full max-w-lg overflow-hidden rounded-[1.75rem] bg-white shadow-[0_28px_80px_-36px_rgba(29,47,37,0.3)]">
            <div class="bg-kazain-primary-dark px-6 py-8 text-center text-white sm:px-10">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-[#e5d9b9]">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 0 0-9 0V10.5m-.75 0h10.5c.828 0 1.5.672 1.5 1.5v7.125c0 .828-.672 1.5-1.5 1.5H6.75c-.828 0-1.5-.672-1.5-1.5V12c0-.828.672-1.5 1.5-1.5Z" />
                    </svg>
                </div>
                <p class="mt-5 text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Kazain Hotel</p>
                <h1 class="mt-2 text-5xl font-semibold leading-none tracking-tight">405</h1>
            </div>

            <div class="login-form-panel px-6 py-8 text-center sm:px-10 sm:py-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-kazain-muted">Metode Tidak Diizinkan</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight tracking-tight text-kazain-text">Akses tidak sesuai</h2>
                <p class="mt-3 text-sm leading-6 text-kazain-muted">
                    Halaman ini tidak dapat dibuka dengan metode tersebut. Jika Anda ingin keluar, gunakan tombol Keluar di dalam aplikasi.
                </p>

                @php
                    $dashboardUrl = match (auth()->user()?->role) {
                        'Admin' => route('admin.dashboard'),
                        'Receptionist' => route('receptionist.dashboard'),
                        default => route('login'),
                    };
                @endphp

                <div class="mt-7 flex justify-center">
                    <a
                        href="{{ $dashboardUrl }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-kazain-primary px-6 text-sm font-semibold text-white shadow-sm transition duration-150 hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/30"
                    >
                        Kembali ke Dashboard
                    </a>
                </div>

                <p class="mt-8 text-xs leading-5 text-kazain-muted">
                    Kazain Hotel <span class="px-1 text-[#c1c6bf]">·</span> Sistem Manajemen Internal
                </p>
            </div>
        </section>
    </main>
</body>
</html>
