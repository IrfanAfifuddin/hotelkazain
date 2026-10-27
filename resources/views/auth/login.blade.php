<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#d1d5db">
    <title>Masuk — Kazain Hotel</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-300 font-sans text-kazain-text antialiased">
    <main class="flex min-h-screen items-center justify-center px-3 py-6 sm:px-6 sm:py-10">
        <section class="login-card grid w-full max-w-5xl grid-cols-1 overflow-hidden rounded-[1.75rem] bg-white shadow-[0_28px_80px_-36px_rgba(29,47,37,0.3)] lg:min-h-[620px] lg:grid-cols-2">
            <aside class="login-panel relative isolate flex items-center justify-between gap-4 overflow-hidden px-5 py-4 text-white lg:min-h-[620px] lg:flex-col lg:items-stretch lg:px-12 lg:py-12">
                <img
                    src="{{ asset('images/foto_login1.jpg') }}"
                    alt=""
                    aria-hidden="true"
                    class="absolute inset-0 z-0 h-full w-full object-cover object-center"
                >
                <div aria-hidden="true" class="absolute inset-0 z-10 bg-gradient-to-br from-[#23392d]/55 to-[#1d3027]/70"></div>

                <div class="relative z-20 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-[#e5d9b9] lg:h-11 lg:w-11 lg:rounded-2xl">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V5.25L12 2l6.75 3.25V21M8.25 8.25h.008v.008H8.25V8.25Zm0 3.75h.008v.008H8.25V12Zm0 3.75h.008v.008H8.25V15.75Zm7.5-7.5h.008v.008h-.008V8.25Zm0 3.75h.008v.008h-.008V12Zm0 3.75h.008v.008h-.008V15.75ZM10.5 21v-3.375A1.125 1.125 0 0 1 11.625 16.5h.75a1.125 1.125 0 0 1 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold leading-5 tracking-[0.16em] text-white">KAZAIN</p>
                        <p class="text-xs leading-4 tracking-[0.12em] text-white/85">HOTEL MANAGEMENT</p>
                    </div>
                </div>

            </aside>

            <div class="login-form-panel flex items-center justify-center px-5 py-8 sm:px-10 sm:py-12 lg:px-12 lg:py-14">
                <div class="w-full max-w-md">
                    <div class="mb-8">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-kazain-muted">Masuk</p>
                        <h2 class="text-3xl font-semibold leading-tight tracking-tight text-kazain-text">Selamat datang</h2>
                        <p class="mt-2 text-sm leading-6 text-kazain-muted">Masuk untuk melanjutkan pekerjaan Anda.</p>
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="mb-6 flex gap-3 rounded-xl border border-[#ead6d1] bg-[#fbf3f1] px-4 py-3 text-sm text-[#984f42]">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008ZM10.29 3.86 1.82 18.25A1.5 1.5 0 0 0 3.11 20.5h17.78a1.5 1.5 0 0 0 1.29-2.25L13.71 3.86a1.98 1.98 0 0 0-3.42 0Z" />
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label for="username" class="mb-2 block text-sm font-medium text-kazain-text">Username</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-kazain-muted">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.65 0-5.17-.58-7.5-1.65Z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    name="username"
                                    id="username"
                                    value="{{ old('username') }}"
                                    placeholder="Masukkan username"
                                    autocomplete="username"
                                    required
                                    class="login-input h-12 w-full rounded-xl border border-kazain-border bg-kazain-surface pl-11 pr-4 text-sm text-kazain-text outline-none transition duration-150 placeholder:text-kazain-muted focus:border-kazain-primary focus:bg-white focus:ring-4 focus:ring-kazain-primary/20"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-kazain-text">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-kazain-muted">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 0 0-9 0V10.5m-.75 0h10.5c.828 0 1.5.672 1.5 1.5v7.125c0 .828-.672 1.5-1.5 1.5H6.75c-.828 0-1.5-.672-1.5-1.5V12c0-.828.672-1.5 1.5-1.5Z" />
                                    </svg>
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required
                                    class="login-input h-12 w-full rounded-xl border border-kazain-border bg-kazain-surface pl-11 pr-12 text-sm text-kazain-text outline-none transition duration-150 placeholder:text-kazain-muted focus:border-kazain-primary focus:bg-white focus:ring-4 focus:ring-kazain-primary/20"
                                >
                                <button
                                    type="button"
                                    id="togglePassword"
                                    aria-label="Tampilkan password"
                                    aria-pressed="false"
                                    class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3.5 text-kazain-muted transition duration-150 hover:text-kazain-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-kazain-primary"
                                >
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg id="eyeSlashIcon" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="h-12 w-full rounded-xl bg-kazain-primary px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/30"
                        >
                            Masuk ke akun
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs leading-5 text-kazain-muted">
                        Kazain Hotel <span class="px-1 text-[#c1c6bf]">·</span> Sistem Manajemen Internal
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordButton = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        togglePasswordButton.addEventListener('click', () => {
            const isPasswordHidden = passwordInput.type === 'password';

            passwordInput.type = isPasswordHidden ? 'text' : 'password';
            eyeIcon.classList.toggle('hidden', !isPasswordHidden);
            eyeSlashIcon.classList.toggle('hidden', isPasswordHidden);
            togglePasswordButton.setAttribute('aria-pressed', String(isPasswordHidden));
            togglePasswordButton.setAttribute(
                'aria-label',
                isPasswordHidden ? 'Sembunyikan password' : 'Tampilkan password',
            );
        });
    </script>
</body>
</html>
