<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%2326211D'/%3E%3Ctext x='50%25' y='54%25' text-anchor='middle' dominant-baseline='middle' font-family='Georgia,serif' font-size='40' font-weight='700' fill='%23A8844F'%3EK%3C/text%3E%3C/svg%3E">
    <title>@yield('title', 'Beranda') — Kazain Hotel</title>

    {{-- Inter untuk teks, Playfair Display untuk judul --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('styles')
    @include('customer.partials.theme')
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    @php
        $waNumber = '6282142331906';
        $waText = urlencode('Halo Kazain Hotel, saya ingin bertanya tentang pemesanan kamar.');
    @endphp

    {{-- ===================== HEADER ===================== --}}
    {{-- Header: halaman yang memakai @section('transparent_header') mendapat header transparan di atas foto hero --}}
    @php $transparentHeader = $__env->hasSection('transparent_header'); @endphp
    <header id="siteHeader" class="text-white sticky top-0 z-40 transition-colors duration-300 {{ $transparentHeader ? 'bg-transparent' : 'bg-ink' }}"
            @if ($transparentHeader) data-transparent @endif>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            <a href="{{ route('customer.home') }}" class="flex flex-col leading-none">
                <span class="font-display text-2xl sm:text-3xl font-semibold tracking-wide">Kazain Hotel</span>
                <span class="hidden sm:block mt-1 text-[11px] tracking-[0.3em] text-white/70 uppercase">Prawirotaman · Yogyakarta</span>
            </a>

            <a href="{{ route('customer.availability') }}"
               class="btn-gold shrink-0 whitespace-nowrap px-4 sm:px-7 py-2 sm:py-2.5 text-xs sm:text-sm">
                Pesan Kamar
            </a>
        </div>
    </header>

    {{-- ===================== KONTEN HALAMAN ===================== --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="bg-ink text-white/70 border-t border-white/10 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-8 grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <a href="{{ route('customer.home') }}" class="font-display text-2xl font-semibold text-white">Kazain Hotel</a>
                <p class="mt-1 text-[11px] tracking-[0.3em] uppercase text-white/50">Prawirotaman · Yogyakarta</p>
                <p class="mt-4 max-w-xs leading-relaxed text-white/60">Penginapan nyaman dengan pelayanan ramah di jantung kawasan wisata budaya Yogyakarta.</p>
            </div>

            <div>
                <p class="text-xs font-semibold tracking-[0.2em] uppercase" style="color: #D9BC8C">Hubungi</p>
                <ul class="mt-4 space-y-2.5 leading-relaxed">
                    <li>Jl. Prawirotaman I, Timuran No. 562, Yogyakarta</li>
                    <li><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="hover:text-white transition">WhatsApp 0821-4233-1906</a></li>
                    <li>Resepsionis 24 jam</li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold tracking-[0.2em] uppercase" style="color: #D9BC8C">Jelajahi</p>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="{{ route('customer.home') }}#tentang" class="hover:text-white transition">Tentang</a></li>
                    <li><a href="{{ route('customer.home') }}#fasilitas" class="hover:text-white transition">Fasilitas</a></li>
                    <li><a href="{{ route('customer.home') }}#faq" class="hover:text-white transition">Pertanyaan Umum</a></li>
                    <li><a href="{{ route('customer.home') }}#kontak" class="hover:text-white transition">Kontak</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-24 md:pb-5 text-xs text-white/45">&copy; {{ date('Y') }} Kazain Hotel. Hak cipta dilindungi undang-undang.</p>
        </div>
    </footer>

    {{-- Tombol WhatsApp melayang --}}
    <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank" rel="noopener"
       class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-ink text-white ring-2 ring-white/80 shadow-lg flex items-center justify-center hover:scale-105 transition"
       aria-label="Hubungi kami lewat WhatsApp">
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.7 8.23-8.22 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.35-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/>
        </svg>
    </a>

    @if ($transparentHeader)
        <script>
            // Header transparan di atas foto, berubah gelap setelah halaman di-scroll
            (function () {
                const header = document.getElementById('siteHeader');
                const update = () => {
                    const solid = window.scrollY > 40;
                    header.classList.toggle('bg-ink', solid);
                    header.classList.toggle('shadow-lg', solid);
                    header.classList.toggle('bg-transparent', !solid);
                };
                window.addEventListener('scroll', update, { passive: true });
                update();
            })();
        </script>
    @endif

    @stack('scripts')
</body>
</html>