<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — Kazain Hotel</title>

    {{-- Font Inter (sesuai Figma) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    {{-- ===================== NAVBAR ===================== --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('customer.home') }}" class="flex items-center gap-3 shrink-0">
                <span class="w-9 h-9 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M2 4v16" /><path d="M2 8h18a2 2 0 0 1 2 2v10" /><path d="M2 17h20" /><path d="M6 8v9" />
                    </svg>
                </span>
                <span class="font-semibold text-gray-900">Kazain Hotel</span>
            </a>

            {{-- Menu desktop --}}
            <div class="hidden md:flex items-center gap-8 text-sm text-gray-600">
                <a href="{{ route('customer.home') }}#tentang" class="hover:text-gray-900 transition">Tentang</a>
                <a href="{{ route('customer.home') }}#fasilitas" class="hover:text-gray-900 transition">Fasilitas</a>
                <a href="{{ route('customer.home') }}#faq" class="hover:text-gray-900 transition">Pertanyaan Umum</a>
                <a href="{{ route('customer.home') }}#kontak" class="hover:text-gray-900 transition">Kontak</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('customer.availability') }}" class="hidden sm:inline-flex items-center px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-900 bg-white hover:bg-gray-50 transition">
                    Pesan Kamar
                </a>

                {{-- Tombol menu HP --}}
                <button type="button" id="mobileMenuButton" class="md:hidden p-2 text-gray-600 hover:text-gray-900" aria-label="Buka menu" aria-expanded="false">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </nav>

        {{-- Menu HP --}}
        <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 bg-white">
            <div class="px-4 py-3 flex flex-col text-sm text-gray-700">
                <a href="{{ route('customer.home') }}#tentang" class="py-2.5">Tentang</a>
                <a href="{{ route('customer.home') }}#fasilitas" class="py-2.5">Fasilitas</a>
                <a href="{{ route('customer.home') }}#faq" class="py-2.5">Pertanyaan Umum</a>
                <a href="{{ route('customer.home') }}#kontak" class="py-2.5">Kontak</a>
                <a href="{{ route('customer.availability') }}" class="mt-2 border border-gray-300 text-center py-2.5 rounded-lg font-medium text-gray-900">
                    Pesan Kamar
                </a>
            </div>
        </div>
    </header>

    {{-- ===================== KONTEN HALAMAN ===================== --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <a href="{{ route('customer.home') }}" class="w-9 h-9 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600" aria-label="Kazain Hotel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M2 4v16" /><path d="M2 8h18a2 2 0 0 1 2 2v10" /><path d="M2 17h20" /><path d="M6 8v9" />
                </svg>
            </a>

            <p class="text-center">&copy; {{ date('Y') }} Kazain Hotel. Hak cipta dilindungi undang-undang.</p>

            <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
                <a href="{{ route('customer.home') }}#tentang" class="hover:text-gray-900 transition">Tentang</a>
                <a href="{{ route('customer.home') }}#fasilitas" class="hover:text-gray-900 transition">Fasilitas</a>
                <a href="{{ route('customer.home') }}#faq" class="hover:text-gray-900 transition">Pertanyaan Umum</a>
                <a href="{{ route('customer.home') }}#kontak" class="hover:text-gray-900 transition">Kontak</a>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const button = document.getElementById('mobileMenuButton');
            const menu = document.getElementById('mobileMenu');

            button.addEventListener('click', function () {
                const isHidden = menu.classList.toggle('hidden');
                button.setAttribute('aria-expanded', String(!isHidden));
            });

            // Tutup menu HP setelah salah satu link diklik
            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    menu.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>