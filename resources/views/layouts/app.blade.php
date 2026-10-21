<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f3ef">
    <title>@yield('title', 'Kazain Hotel') — Kazain Hotel</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-kazain-neutral font-sans text-kazain-text antialiased">
    <nav class="print:hidden fixed inset-x-0 top-0 z-30 h-16 border-b border-kazain-border/80 bg-white/95 backdrop-blur">
        <div class="flex h-full items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button
                    id="sidebarToggle"
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-kazain-muted transition hover:bg-[#edf1ec] hover:text-kazain-primary focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 md:hidden"
                    aria-label="Buka navigasi"
                    aria-controls="sidebar"
                    aria-expanded="false"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-kazain-primary text-[#e8dec3] shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V5.25L12 2l6.75 3.25V21M8.25 8.25h.008v.008H8.25V8.25Zm0 3.75h.008v.008H8.25V12Zm0 3.75h.008v.008H8.25V15.75Zm7.5-7.5h.008v.008h-.008V8.25Zm0 3.75h.008v.008h-.008V12Zm0 3.75h.008v.008h-.008V15.75ZM10.5 21v-3.375A1.125 1.125 0 0 1 11.625 16.5h.75a1.125 1.125 0 0 1 1.125 1.125V21" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold tracking-wide text-kazain-text">Kazain Hotel</span>
                        <span class="hidden text-[10px] font-medium uppercase tracking-[0.14em] text-kazain-muted sm:block">Hotel management</span>
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-kazain-text">{{ auth()->user()->user_name }}</p>
                    <p class="text-xs text-kazain-muted">{{ auth()->user()->role === 'Admin' ? 'Administrator' : 'Resepsionis' }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#dce2db] bg-[#edf1ec] text-sm font-semibold text-kazain-primary" aria-label="Akun {{ auth()->user()->user_name }}">
                    {{ mb_strtoupper(mb_substr(auth()->user()->user_name, 0, 1)) }}
                </span>
            </div>
        </div>
    </nav>

    <aside id="sidebar" class="print:hidden fixed bottom-0 left-0 top-16 z-40 flex h-[calc(100vh-4rem)] w-72 -translate-x-full flex-col border-r border-white/10 bg-gradient-to-b from-kazain-primary to-kazain-primary-dark px-4 py-5 shadow-xl transition-transform duration-200 md:z-20 md:w-64 md:translate-x-0">
        <nav class="flex min-h-0 flex-1 flex-col" aria-label="Navigasi utama">
            <p class="px-3 pb-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/55">Menu utama</p>
            <div class="flex-1 space-y-1">
                @php
                    $isDashboardActive = request()->routeIs('dashboard', 'admin.dashboard', 'receptionist.dashboard');
                    $dashboardLinkClass = $isDashboardActive
                        ? 'bg-kazain-accent text-kazain-text shadow-sm'
                        : 'text-white/75 hover:bg-white/10 hover:text-white';
                    $roomStatusRoute = auth()->user()->role === 'Admin' ? 'admin.status-kamar.index' : 'receptionist.status-kamar.index';
                    $bookingIndexRoute = auth()->user()->role === 'Admin' ? 'admin.pemesanan.index' : 'receptionist.pemesanan.index';
                @endphp

                <a href="{{ route('dashboard') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40', $dashboardLinkClass]) @if ($isDashboardActive) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route($roomStatusRoute) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40', request()->routeIs('admin.status-kamar.*', 'receptionist.status-kamar.*') ? 'bg-kazain-accent text-kazain-text shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white']) @if (request()->routeIs('admin.status-kamar.*', 'receptionist.status-kamar.*')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V5.25L12 2l6.75 3.25V21M8.25 8.25h.008v.008H8.25V8.25Zm7.5 0h.008v.008h-.008V8.25Z" />
                    </svg>
                    Status kamar
                </a>

                <a href="{{ route($bookingIndexRoute) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40', request()->routeIs('admin.pemesanan.*', 'receptionist.pemesanan.*') ? 'bg-kazain-accent text-kazain-text shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white']) @if (request()->routeIs('admin.pemesanan.*', 'receptionist.pemesanan.*')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5M5.25 3.75h13.5A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                    </svg>
                    Data pemesanan
                </a>

                @if (auth()->user()->role === 'Admin')
                    <a href="{{ route('admin.reports.index') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40', request()->routeIs('admin.reports.*') ? 'bg-kazain-accent text-kazain-text shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white']) @if (request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125V4.125Z" />
                        </svg>
                        Laporan penjualan
                    </a>
                    <a href="{{ route('admin.users.index') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40', request()->routeIs('admin.users.*') ? 'bg-kazain-accent text-kazain-text shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white']) @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                        </svg>
                        Kelola akun
                    </a>
                @endif
            </div>

            <div class="mt-5 border-t border-white/15 pt-4">
                <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                    @csrf
                    <button type="button" data-logout-open class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-[#f0c2b5] transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-accent/40">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <div id="sidebarBackdrop" class="print:hidden fixed inset-0 z-30 hidden bg-[#1d3027]/35 backdrop-blur-[2px] md:hidden" aria-hidden="true"></div>

    <div id="logoutModal" class="print:hidden fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
        <button type="button" data-logout-close class="absolute inset-0 cursor-default bg-[#1d3027]/35 backdrop-blur-[2px]" aria-label="Tutup konfirmasi"></button>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-sm rounded-2xl border border-kazain-border bg-kazain-surface p-6 shadow-[0_24px_70px_-30px_rgba(29,48,39,0.45)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4eae7] text-[#975b50]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </span>
                <h2 id="logoutModalTitle" class="mt-4 text-lg font-semibold text-kazain-text">Keluar dari akun?</h2>
                <p class="mt-1 text-sm leading-6 text-kazain-muted">Anda akan keluar dari sistem manajemen Kazain Hotel.</p>
                <div class="mt-6 flex gap-3">
                    <button type="button" data-logout-close class="flex-1 rounded-xl border border-kazain-border bg-white px-4 py-2.5 text-sm font-medium text-kazain-text transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
                        Batal
                    </button>
                    <button type="button" data-logout-submit class="flex-1 rounded-xl bg-kazain-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <main class="min-h-screen pt-16 print:ml-0 print:pt-0 md:ml-64">
        <div class="p-4 print:p-0 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
