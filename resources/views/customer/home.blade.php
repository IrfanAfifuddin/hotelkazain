@extends('layouts.customer')

@section('title', 'Beranda')

@php
    // ================= DATA STATIS HALAMAN BERANDA =================
    // Ganti/lengkapi di sini kalau ada perubahan info dari hotel.

    $alamat = 'Jl. Prawirotaman I, Timuran No. 562, Brontokusuman, Mergangsan, Yogyakarta 55153';

    // Ikon memakai path SVG (stroke) agar tidak perlu library tambahan.
    $fasilitas = [
        ['label' => 'Kamar Mandi Dalam', 'icon' => '<path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M10 5 8 7"/><path d="M2 12h20"/><path d="M7 19v2"/><path d="M17 19v2"/>'],
        ['label' => 'Air Panas (Water Heater)', 'icon' => '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>'],
        ['label' => 'AC', 'icon' => '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>'],
        ['label' => 'Wi-Fi Gratis', 'icon' => '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.86a10 10 0 0 1 14 0"/><path d="M8.5 16.43a5 5 0 0 1 7 0"/>'],
        ['label' => 'Handuk Bersih', 'icon' => '<path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>'],
        ['label' => 'Perlengkapan Mandi', 'icon' => '<path d="M9.94 15.5A2 2 0 0 0 8.5 14.06l-6.14-1.58a.5.5 0 0 1 0-.96L8.5 9.94A2 2 0 0 0 9.94 8.5l1.58-6.14a.5.5 0 0 1 .96 0l1.58 6.14a2 2 0 0 0 1.44 1.44l6.14 1.58a.5.5 0 0 1 0 .96l-6.14 1.58a2 2 0 0 0-1.44 1.44l-1.58 6.14a.5.5 0 0 1-.96 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/>'],
        ['label' => 'Tisu', 'icon' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>'],
        ['label' => 'Air Mineral', 'icon' => '<path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>'],
        ['label' => 'Kopi & Teh Gratis', 'icon' => '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M6 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/>'],
        ['label' => 'Smart TV', 'icon' => '<rect width="20" height="15" x="2" y="7" rx="2"/><path d="m17 2-5 5-5-5"/>'],
    ];

    // TODO: konfirmasi jawaban FAQ dengan pihak hotel (yang ditandai belum pasti).
    $faq = [
        ['q' => 'Apakah tersedia parkir untuk mobil?', 'a' => 'Saat ini Kazain Hotel hanya menyediakan parkir gratis untuk sepeda motor. Parkir untuk mobil belum tersedia.'],
        ['q' => 'Apakah tersedia sarapan?', 'a' => 'Tidak, Kazain Hotel tidak menyediakan fasilitas sarapan. Namun kawasan Prawirotaman memiliki banyak warung makan, kafe, dan restoran yang mudah dijangkau dengan berjalan kaki.'],
        ['q' => 'Jam berapa check-in dan check-out?', 'a' => 'Untuk pemesanan Full Day, check-in mulai pukul 14.00 dan check-out paling lambat pukul 12.00 keesokan harinya. Untuk Short Time (5 atau 8 jam), jam check-out mengikuti jam check-in yang Anda pilih.'],
        ['q' => 'Apakah kamar dilengkapi AC dan air panas?', 'a' => 'Ya, semua kamar dilengkapi AC dan kamar mandi dalam dengan air panas (water heater).'],
        ['q' => 'Apakah tersedia Wi-Fi di kamar?', 'a' => 'Ya, tersedia Wi-Fi gratis yang dapat digunakan selama Anda menginap.'],
        ['q' => 'Bolehkah membawa tamu dari luar masuk ke kamar?', 'a' => 'Demi kenyamanan dan keamanan seluruh tamu, silakan konfirmasi terlebih dahulu ke resepsionis sebelum membawa tamu dari luar.'],
        ['q' => 'Bisakah memesan untuk lebih dari satu malam?', 'a' => 'Bisa. Pilih tipe pemesanan Full Day saat memesan, lalu sesuaikan tanggal menginap Anda.'],
    ];

    $kontak = [
        ['label' => 'Alamat', 'value' => $alamat, 'icon' => '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>'],
        // TODO: ganti dengan nomor WhatsApp asli hotel
        ['label' => 'Telepon / WhatsApp', 'value' => '+62 8xx-xxxx-xxxx', 'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>'],
        ['label' => 'Email', 'value' => 'info@kazainhotel.com', 'icon' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'],
        ['label' => 'Jam Operasional', 'value' => 'Resepsionis tersedia 24 jam', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
        ['label' => 'Parkir', 'value' => 'Tersedia parkir gratis untuk sepeda motor. Tidak tersedia parkir untuk mobil.', 'icon' => '<path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>'],
    ];

    $destinasi = [
        ['nama' => 'Keraton Yogyakarta', 'jarak' => '± 2,5 km'],
        ['nama' => 'Malioboro', 'jarak' => '± 3 km'],
        ['nama' => 'Museum Perjuangan', 'jarak' => '± 0,8 km'],
        ['nama' => 'Pantai Parangtritis', 'jarak' => '± 25 km'],
        ['nama' => 'Bandara YIA', 'jarak' => '± 40 km'],
    ];

    $mapsQuery = urlencode('Kazain Hotel Prawirotaman Yogyakarta');

    // Gambar: taruh file di public/images/ dengan nama di bawah. Kalau belum ada, tampil placeholder.
    $heroImage = file_exists(public_path('images/hero.jpg')) ? asset('images/hero.jpg') : null;
    $hotelImage = file_exists(public_path('images/hotel.jpg')) ? asset('images/hotel.jpg') : null;

    $iconPin = '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>';
    $iconImage = '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>';
@endphp

@section('content')

    {{-- ===================== HERO ===================== --}}
    <section class="relative bg-gray-100 overflow-hidden">
        @if ($heroImage)
            <img src="{{ $heroImage }}" alt="Kazain Hotel" class="absolute inset-0 w-full h-full object-cover opacity-20">
        @endif

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 sm:pt-28 sm:pb-32 text-center">
            <span class="inline-block px-5 py-1.5 border border-gray-300 rounded-full bg-white/60 text-xs sm:text-sm font-medium tracking-[0.3em] text-gray-700">
                SELAMAT DATANG
            </span>

            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-tight">
                Menginap Nyaman di Jantung Prawirotaman
            </h1>

            <p class="mt-6 max-w-2xl mx-auto text-base sm:text-lg text-gray-600 leading-relaxed">
                Rasakan pengalaman menginap yang tenang dan ramah bersama Kazain Hotel, penginapan bernuansa homey di kawasan wisata Yogyakarta.
            </p>

            <a href="{{ route('customer.availability') }}" class="mt-8 inline-flex items-center px-10 py-3 border border-gray-400 rounded-lg bg-white text-base font-semibold text-gray-900 hover:bg-gray-50 transition">
                Pesan Kamar
            </a>

            {{-- Statistik singkat --}}
            <div class="mt-12 inline-grid grid-cols-2 bg-white border border-gray-200 rounded-lg divide-x divide-gray-200">
                <div class="px-6 sm:px-12 py-4">
                    <p class="text-lg font-bold text-gray-900">5,0 &#9733;</p>
                    <p class="text-sm text-gray-500">Rating Tamu</p>
                </div>
                <div class="px-6 sm:px-12 py-4">
                    <p class="text-lg font-bold text-gray-900">Yogyakarta</p>
                    <p class="text-sm text-gray-500">Lokasi Strategis</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TENTANG KAMI ===================== --}}
    <section id="tentang" class="scroll-mt-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-sm font-medium tracking-[0.3em] text-gray-500">TENTANG KAMI</p>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">
                    Penginapan Nyaman dan Ramah di Yogyakarta
                </h2>

                <p class="mt-6 text-gray-600 leading-relaxed">
                    Kazain Hotel adalah penginapan yang menawarkan kenyamanan dengan suasana tenang dan pelayanan ramah. Berlokasi strategis di kawasan Prawirotaman, tamu dapat dengan mudah menjangkau Keraton Yogyakarta, Malioboro, Museum Perjuangan, dan berbagai tempat wisata budaya lainnya.
                </p>
                <p class="mt-4 text-gray-600 leading-relaxed">
                    Dengan kamar ber-AC yang bersih dan fasilitas lengkap, didukung layanan resepsionis, Kazain Hotel siap menjadi tempat istirahat nyaman selama Anda menjelajahi Kota Yogyakarta.
                </p>

                <div class="mt-8 flex items-start gap-3 text-gray-600">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconPin !!}</svg>
                    <span>{{ $alamat }}</span>
                </div>
            </div>

            <div class="relative aspect-[4/3] bg-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                @if ($hotelImage)
                    <img src="{{ $hotelImage }}" alt="Foto Kazain Hotel" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconImage !!}</svg>
                        <span class="text-sm tracking-wider">FOTO HOTEL</span>
                    </div>
                @endif

                <span class="absolute top-4 right-4 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm font-semibold text-gray-900 shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                    5,0 / 5
                </span>
            </div>
        </div>
    </section>

    {{-- ===================== FASILITAS ===================== --}}
    <section id="fasilitas" class="scroll-mt-16 bg-gray-50 border-y border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <p class="text-sm font-medium tracking-[0.3em] text-gray-500">FASILITAS</p>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-gray-900">Fasilitas Kamar yang Kami Sediakan</h2>
            </div>

            <div class="mt-12 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
                @foreach ($fasilitas as $item)
                    <div class="bg-white border border-gray-200 rounded-lg px-4 py-8 flex flex-col items-center text-center gap-4">
                        <span class="w-14 h-14 border border-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        </span>
                        <span class="text-sm sm:text-base text-gray-700">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== PERTANYAAN UMUM (FAQ) ===================== --}}
    <section id="faq" class="scroll-mt-16 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <p class="text-sm font-medium tracking-[0.3em] text-gray-500">PERTANYAAN UMUM</p>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-gray-900">Yang Sering Ditanyakan</h2>
            </div>

            <div class="mt-12 border border-gray-200 rounded-lg divide-y divide-gray-200 overflow-hidden">
                @foreach ($faq as $i => $item)
                    <details class="group" @if ($i === 1) open @endif>
                        <summary class="flex items-center justify-between gap-4 px-6 py-5 cursor-pointer list-none [&::-webkit-details-marker]:hidden hover:bg-gray-50 group-open:bg-gray-50 transition">
                            <span class="font-medium text-gray-900">{{ $item['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="px-6 pb-5">
                            <p class="border-l-2 border-gray-200 pl-6 text-gray-600 leading-relaxed">{{ $item['a'] }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== KONTAK ===================== --}}
    <section id="kontak" class="scroll-mt-16 bg-gray-50 border-y border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <p class="text-sm font-medium tracking-[0.3em] text-gray-500">KONTAK</p>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-gray-900">Hubungi Kami</h2>
                <p class="mt-4 text-gray-600">Ada pertanyaan? Jangan ragu untuk menghubungi kami.</p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-2 lg:items-start">

                {{-- Kolom kiri: info kontak --}}
                <div class="space-y-4">
                    @foreach ($kontak as $item)
                        <div class="bg-white border border-gray-200 rounded-lg p-6 flex items-start gap-4">
                            <span class="w-12 h-12 shrink-0 border border-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium tracking-wider text-gray-500 uppercase">{{ $item['label'] }}</p>
                                <p class="mt-1 text-gray-900">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach

                    {{-- Jarak ke destinasi wisata --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <p class="text-xs font-medium tracking-wider text-gray-500 uppercase">Jarak ke Destinasi Wisata</p>
                        <ul class="mt-4 divide-y divide-gray-100">
                            @foreach ($destinasi as $tempat)
                                <li class="flex items-center justify-between gap-4 py-3">
                                    <span class="flex items-center gap-3 text-gray-800">
                                        <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconPin !!}</svg>
                                        {{ $tempat['nama'] }}
                                    </span>
                                    <span class="shrink-0 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded text-sm text-gray-500">{{ $tempat['jarak'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Kolom kanan: peta --}}
                <div class="lg:sticky lg:top-24 space-y-4">
                    <div class="aspect-[4/3] bg-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                        <iframe
                            src="https://www.google.com/maps?q={{ $mapsQuery }}&output=embed"
                            class="w-full h-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi Kazain Hotel di Google Maps"
                            allowfullscreen
                        ></iframe>
                    </div>

                    <a href="https://www.google.com/maps/search/?api=1&query={{ $mapsQuery }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 w-full px-4 py-3.5 bg-white border border-gray-200 rounded-lg text-gray-900 font-medium hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconPin !!}</svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-6 py-14 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Siap Menginap Bersama Kami?</h2>
                <p class="mt-4 max-w-xl mx-auto text-gray-600 leading-relaxed">
                    Pesan kamar sekarang dan nikmati pengalaman menginap yang nyaman dan berkesan di Yogyakarta.
                </p>
                <a href="{{ route('customer.availability') }}" class="mt-8 inline-flex items-center px-14 py-3 border border-gray-400 rounded-lg bg-white font-semibold text-gray-900 hover:bg-gray-50 transition">
                    Pesan Kamar Sekarang
                </a>
            </div>
        </div>
    </section>

@endsection