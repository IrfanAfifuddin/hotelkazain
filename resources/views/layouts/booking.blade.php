<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="data:,">
    <title>@yield('title', 'Pemesanan') — Kazain Hotel</title>

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
        // Langkah aktif dikirim dari halaman: @section('step', '1'), '2', atau '3'
        $currentStep = (int) trim($__env->yieldContent('step', '1'));

        // Tombol kembali di header: @section('back_url', ...) dan @section('back_label', ...)
        $backUrl = trim($__env->yieldContent('back_url')) ?: route('customer.home');
        $backLabel = trim($__env->yieldContent('back_label')) ?: 'Beranda';
        $steps = [
            1 => 'Cek Ketersediaan & Kamar',
            2 => 'Isi Data Diri',
            3 => 'Pembayaran',
        ];
    @endphp

    {{-- ===================== HEADER (senada dengan Beranda) ===================== --}}
    <header class="bg-ink text-white sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
            <a href="{{ $backUrl }}" id="backLink" class="flex items-center gap-1.5 text-white/75 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>{{ $backLabel }}</span>
            </a>

            <span class="h-6 w-px bg-white/20"></span>

            <a href="{{ route('customer.home') }}" class="font-display text-xl sm:text-2xl font-semibold tracking-wide">
                Kazain Hotel
            </a>
        </div>
    </header>

    {{-- ===================== STEPPER ===================== --}}
    <div class="bg-sand border-b border-gray-200">
        <ol class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
            @foreach ($steps as $number => $label)
                @php
                    $isActive = $number === $currentStep;
                    $isDone = $number < $currentStep;
                @endphp
                <li class="flex items-center gap-2 {{ $isActive ? 'text-gray-900 font-semibold' : ($isDone ? 'text-gray-700' : 'text-gray-400') }}" @if ($isActive) aria-current="step" @endif>
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs
                        {{ $isDone ? 'bg-gray-900 text-white' : ($isActive ? 'border-2 border-gray-900' : 'border border-gray-300') }}">
                        @if ($isDone)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="{{ $isActive ? '' : 'hidden sm:inline' }}">{{ $label }}</span>
                </li>

                @if (! $loop->last)
                    <li class="text-gray-300" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" /></svg>
                    </li>
                @endif
            @endforeach
        </ol>
    </div>

    {{-- ===================== KONTEN ===================== --}}
    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-ink py-6 text-center text-sm text-white/60">
        &copy; {{ date('Y') }} Kazain Hotel. Hak cipta dilindungi undang-undang.
    </footer>

    @stack('scripts')
</body>
</html>