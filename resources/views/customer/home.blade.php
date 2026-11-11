@extends('layouts.customer')

@section('title', 'Beranda')
@section('transparent_header', '1')

@php
    // ================= DATA STATIS HALAMAN BERANDA =================
    // Ganti/lengkapi di sini kalau ada perubahan info dari hotel.

    $alamat = 'Jl. Prawirotaman I, Timuran No. 562, Brontokusuman, Mergangsan, Yogyakarta 55153';

    // Foto: taruh file di public/images/. Kalau file belum ada, tampil kotak abu-abu.
    $img = fn (string $path) => file_exists(public_path('images/' . $path)) ? asset('images/' . $path) : null;

    $heroImage = $img('hero.jpg');

    // Foto slider di bagian Tentang (file yang belum ada otomatis dilewati)
    $fotoTentang = array_values(array_filter([
        ['src' => $img('hotel.jpg'), 'alt' => 'Area depan Kazain Hotel', 'label' => 'Area Depan'],
        ['src' => $img('taman.jpg'), 'alt' => 'Taman dalam Kazain Hotel', 'label' => 'Taman'],
        ['src' => $img('kolam-koi.jpg'), 'alt' => 'Kolam ikan koi Kazain Hotel', 'label' => 'Kolam Koi'],
    ], fn ($f) => $f['src']));

    $keunggulan = [
        ['label' => 'Resepsionis 24 Jam', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
        ['label' => 'Kamar Ber-AC', 'icon' => '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>'],
        ['label' => 'Wi-Fi Gratis', 'icon' => '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.86a10 10 0 0 1 14 0"/><path d="M8.5 16.43a5 5 0 0 1 7 0"/>'],
    ];

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

    // Foto destinasi: download dari Wikimedia Commons (640px), simpan di public/images/destinasi/
    // dengan nama sesuai 'foto'. Kredit wajib dicantumkan (tampil di bawah daftar).
    $destinasi = [
        ['nama' => 'Museum Perjuangan', 'kategori' => 'Museum', 'jarak' => '± 0,8 km',
         'foto' => 'museum-perjuangan.jpg', 'kredit' => 'Foto: Sabjan Badio', 'lisensi' => 'CC BY-SA 4.0',
         'sumber' => 'https://commons.wikimedia.org/wiki/File:Museum_Perjuangan_Yogyakarta.jpg'],
        ['nama' => 'Keraton Yogyakarta', 'kategori' => 'Wisata Budaya', 'jarak' => '± 2,5 km',
         'foto' => 'keraton.jpg', 'kredit' => 'Foto: Chainwit.', 'lisensi' => 'CC BY 4.0',
         'sumber' => 'https://commons.wikimedia.org/wiki/File:Jogja_-_Kraton_Yogyakarta_-_Donopratono_gate_(2025)_-_img_01.jpg'],
        ['nama' => 'Malioboro', 'kategori' => 'Belanja & Kuliner', 'jarak' => '± 3 km',
         'foto' => 'malioboro.jpg', 'kredit' => 'Foto: Terryosano1', 'lisensi' => 'CC0',
         'sumber' => 'https://commons.wikimedia.org/wiki/File:Papan_Jalan_Malioboro_-_2024.jpg'],
        ['nama' => 'Pantai Parangtritis', 'kategori' => 'Pantai', 'jarak' => '± 25 km',
         'foto' => 'parangtritis.jpg', 'kredit' => 'Foto: Jones Averino', 'lisensi' => 'CC BY-SA 4.0',
         'sumber' => 'https://commons.wikimedia.org/wiki/File:Pantai_Parangtritis.jpg'],
        ['nama' => 'Bandara YIA', 'kategori' => 'Transportasi', 'jarak' => '± 40 km',
         'foto' => 'bandara-yia.jpg', 'kredit' => 'Foto: Zhou Guanhuai', 'lisensi' => 'CC0',
         'sumber' => 'https://commons.wikimedia.org/wiki/File:Yogyakarta_International_Airport_Main_Entrance.jpg'],
    ];

    $faq = [
        ['q' => 'Apakah tersedia parkir untuk mobil?', 'icon' => '<path d="M19 17h2v-4l-2-5H5L3 13v4h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M9 17h6"/>', 'a' => 'Saat ini Kazain Hotel hanya menyediakan parkir gratis untuk sepeda motor. Parkir untuk mobil belum tersedia.'],
        ['q' => 'Apakah tersedia sarapan?', 'icon' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>', 'a' => 'Tidak, Kazain Hotel tidak menyediakan fasilitas sarapan. Namun kawasan Prawirotaman memiliki banyak warung makan, kafe, dan restoran yang mudah dijangkau dengan berjalan kaki.'],
        ['q' => 'Jam berapa check-in dan check-out?', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>', 'a' => 'Untuk pemesanan Full Day, check-in mulai pukul 14.00 dan check-out paling lambat pukul 12.00 keesokan harinya. Untuk Short Time (5 atau 8 jam), jam check-out mengikuti jam check-in yang Anda pilih.'],
        ['q' => 'Apakah kamar dilengkapi AC dan air panas?', 'icon' => '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>', 'a' => 'Ya, semua kamar dilengkapi AC dan kamar mandi dalam dengan air panas (water heater).'],
        ['q' => 'Apakah tersedia Wi-Fi di kamar?', 'icon' => '<path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.86a10 10 0 0 1 14 0"/><path d="M8.5 16.43a5 5 0 0 1 7 0"/>', 'a' => 'Ya, tersedia Wi-Fi gratis yang dapat digunakan selama Anda menginap.'],
        ['q' => 'Bolehkah membawa tamu dari luar masuk ke kamar?', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'a' => 'Demi kenyamanan dan keamanan seluruh tamu, silakan konfirmasi terlebih dahulu ke resepsionis sebelum membawa tamu dari luar.'],
        ['q' => 'Apakah ada deposit saat menginap?', 'icon' => '<rect width="20" height="14" x="2" y="5" rx="2"/><circle cx="8" cy="12" r="2"/><path d="M14 10h4"/><path d="M14 14h4"/>', 'a' => 'Sebagai deposit, tamu wajib menitipkan KTP asli kepada resepsionis saat check-in. KTP akan dikembalikan saat check-out.'],
        ['q' => 'Apakah pemesanan bisa dibatalkan?', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>', 'a' => 'Pemesanan yang sudah dibayar tidak dapat dibatalkan dan dana tidak dapat dikembalikan. Mohon pastikan tanggal, jam, dan tipe kamar sudah sesuai sebelum melakukan pembayaran.'],
    ];

    $kontak = [
        ['label' => 'Alamat', 'value' => $alamat, 'icon' => '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>'],
        ['label' => 'Telepon / WhatsApp', 'value' => '0821-4233-1906', 'url' => 'https://wa.me/6282142331906', 'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>'],
        ['label' => 'Email', 'value' => 'info@kazainhotel.com', 'icon' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'],
        ['label' => 'Jam Operasional', 'value' => 'Resepsionis tersedia 24 jam', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
        ['label' => 'Parkir', 'value' => 'Tersedia parkir gratis untuk sepeda motor. Tidak tersedia parkir untuk mobil.', 'icon' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/>'],
    ];

    $menu = [
        'tentang' => 'Tentang',
        'fasilitas' => 'Fasilitas',
        'faq' => 'Pertanyaan Umum',
        'kontak' => 'Kontak',
    ];

    $mapsQuery = urlencode('Kazain Hotel Prawirotaman Yogyakarta');
    $iconPin = '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>';
    $iconImage = '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>';
@endphp

@section('content')

    {{-- ===================== HERO (satu foto) ===================== --}}
    <section class="relative -mt-16 sm:-mt-20 h-[620px] sm:h-[680px] bg-ink overflow-hidden">
        @if ($heroImage)
            <img src="{{ $heroImage }}" alt="Tampak depan Kazain Hotel" class="hero-zoom absolute inset-0 w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-black/65"></div>

        <div class="absolute inset-0 pt-16 sm:pt-20 flex flex-col items-center justify-center px-6 sm:px-10 text-center text-white">
            <p class="text-xs sm:text-sm tracking-[0.35em] uppercase text-white/80">Selamat Datang</p>
            <h1 class="mt-3 max-w-3xl text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight">Menginap Nyaman di Jantung Prawirotaman</h1>
            <p class="mt-4 max-w-xl text-sm sm:text-base text-white/85 leading-relaxed">Penginapan bernuansa homey dengan pelayanan ramah, dekat Keraton, Malioboro, dan kawasan wisata budaya Yogyakarta.</p>

            <a href="{{ route('customer.availability') }}" class="btn-gold mt-8 px-10 py-3.5 text-sm sm:text-base">
                Pesan Kamar
            </a>

            {{-- Statistik singkat: teks tipis tanpa kotak --}}
            <div class="mt-8 flex items-center divide-x divide-white/30 whitespace-nowrap">
                <div class="px-5 sm:px-8">
                    <p class="text-base sm:text-xl font-semibold">4,5 <span class="text-[#E0C38F]">&#9733;</span></p>
                    <p class="text-xs sm:text-sm text-white/75">Rating Google Maps</p>
                </div>
                <div class="px-5 sm:px-8">
                    <p class="text-base sm:text-xl font-semibold">Yogyakarta</p>
                    <p class="text-xs sm:text-sm text-white/75">Lokasi Strategis</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== MENU BAGIAN (lengket di bawah header) ===================== --}}
    <nav id="sectionNav" class="sticky top-16 sm:top-20 z-30 bg-white border-b border-gray-200 shadow-sm">
        {{-- Bayangan pudar di kanan (HP) sebagai tanda menu bisa digeser --}}
        <span class="pointer-events-none absolute right-0 inset-y-0 w-10 bg-gradient-to-l from-white to-transparent sm:hidden" aria-hidden="true"></span>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pr-10 sm:pr-6 lg:pr-8 flex gap-6 sm:gap-10 overflow-x-auto no-scrollbar">
            @foreach ($menu as $id => $label)
                <a href="#{{ $id }}" data-section-link="{{ $id }}"
                   class="shrink-0 py-4 border-b-2 border-transparent text-gray-500 font-semibold whitespace-nowrap hover:text-gray-900 transition">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- ===================== TENTANG ===================== --}}
    <section id="tentang" class="scroll-mt-32 sm:scroll-mt-36">
        <div data-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 grid gap-10 lg:gap-14 lg:grid-cols-[5fr_6fr] lg:items-center">
            <div>
                <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase text-gold">Tentang Kami</p>
                <h2 class="font-display mt-3 text-2xl sm:text-3xl font-semibold text-gray-900 leading-snug">Penginapan Nyaman dan Ramah di Yogyakarta</h2>
                <span class="mt-5 block w-12 h-0.5" style="background-color: var(--kz-gold)" aria-hidden="true"></span>
                <p class="mt-5 text-[15px] text-gray-600 leading-relaxed">
                    Kazain Hotel adalah penginapan yang menawarkan kenyamanan dengan suasana tenang dan pelayanan ramah. Berlokasi strategis di kawasan Prawirotaman, tamu dapat dengan mudah menjangkau Keraton Yogyakarta, Malioboro, Museum Perjuangan, dan berbagai tempat wisata budaya lainnya.
                </p>
                <p class="mt-3 text-[15px] text-gray-600 leading-relaxed">
                    Dengan kamar ber-AC yang bersih dan fasilitas lengkap, didukung resepsionis 24 jam, Kazain Hotel siap menjadi tempat istirahat nyaman selama Anda menjelajahi Kota Yogyakarta.
                </p>

                {{-- Keunggulan singkat --}}
                <ul class="mt-7 grid grid-cols-3 gap-3">
                    @foreach ($keunggulan as $item)
                        <li class="flex flex-col items-center gap-2 px-2 py-4 bg-sand rounded-xl text-center">
                            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            <span class="text-xs sm:text-sm font-medium text-gray-800 leading-tight">{{ $item['label'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-6 flex items-start gap-3 text-sm text-gray-600">
                    <svg class="w-5 h-5 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconPin !!}</svg>
                    <span>{{ $alamat }}</span>
                </div>
            </div>

            {{-- Slider foto: bisa digeser, pakai panah, atau titik --}}
            <div class="relative aspect-[4/3] bg-gray-100 rounded-2xl overflow-hidden shadow-xl shadow-black/10" id="aboutSlider">
                @if (count($fotoTentang))
                    <div class="flex h-full overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth" data-slider-track>
                        @foreach ($fotoTentang as $foto)
                            <figure class="relative w-full h-full shrink-0 snap-center">
                                <img src="{{ $foto['src'] }}" alt="{{ $foto['alt'] }}" class="w-full h-full object-cover" @if (! $loop->first) loading="lazy" @endif>
                                <figcaption class="absolute left-0 bottom-0 m-4 px-3 py-1 rounded-full bg-black/55 text-white text-xs font-semibold tracking-wider uppercase">{{ $foto['label'] }}</figcaption>
                            </figure>
                        @endforeach
                    </div>

                    @if (count($fotoTentang) > 1)
                        <button type="button" data-slider-nav="-1" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-900 shadow flex items-center justify-center transition" aria-label="Foto sebelumnya">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button type="button" data-slider-nav="1" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-900 shadow flex items-center justify-center transition" aria-label="Foto berikutnya">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        <div class="absolute right-4 bottom-4 flex items-center gap-1.5 px-2.5 py-2 rounded-full bg-black/40">
                            @foreach ($fotoTentang as $i => $foto)
                                <button type="button" data-slider-dot="{{ $i }}" class="h-2 rounded-full transition-all {{ $i === 0 ? 'w-6 bg-white' : 'w-2 bg-white/55' }}" aria-label="Lihat foto {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <span class="flex h-full items-center justify-center text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconImage !!}</svg>
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== FASILITAS ===================== --}}
    <section id="fasilitas" class="scroll-mt-32 sm:scroll-mt-36 bg-sand">
        <div data-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <div class="text-center">
                <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase text-gold">Fasilitas</p>
                <h2 class="font-display mt-3 text-2xl sm:text-3xl font-semibold text-gray-900">Fasilitas Kamar</h2>
                <p class="mt-3 text-[15px] text-gray-600">Semua yang Anda butuhkan untuk beristirahat dengan nyaman.</p>
            </div>

            <div class="mt-12 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-4 gap-y-9 sm:gap-y-10">
                @foreach ($fasilitas as $item)
                    <div class="group flex flex-col items-center text-center">
                        <span class="w-16 h-16 rounded-full bg-white border border-[#EADFCB] shadow-[0_10px_24px_-12px_rgba(38,33,29,0.45)] flex items-center justify-center text-gold transition duration-300 group-hover:-translate-y-1 group-hover:bg-[#26211D] group-hover:border-transparent group-hover:text-[#E0C38F]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        </span>
                        <span class="mt-3 text-sm sm:text-[15px] text-gray-700 leading-snug">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    <section id="faq" class="scroll-mt-32 sm:scroll-mt-36">
        <div data-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 grid gap-10 lg:grid-cols-[2fr_3fr] lg:gap-14 lg:items-start">

            {{-- Kolom kiri: judul + info bantuan --}}
            <div class="lg:sticky lg:top-40">
                <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase text-gold">Pertanyaan Umum</p>
                <h2 class="font-display mt-3 text-2xl sm:text-3xl font-semibold text-gray-900">Yang Sering Ditanyakan</h2>
                <span class="mt-5 block w-12 h-0.5" style="background-color: var(--kz-gold)" aria-hidden="true"></span>
                <p class="mt-5 text-[15px] text-gray-600 leading-relaxed">Informasi penting seputar menginap di Kazain Hotel, mulai dari parkir, jam check-in, hingga kebijakan pemesanan.</p>

                <div class="mt-7 relative rounded-2xl overflow-hidden bg-ink text-white">
                    @if ($img('taman.jpg'))
                        <img src="{{ $img('taman.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35" loading="lazy">
                    @endif
                    <div class="relative p-6 bg-gradient-to-t from-black/70 via-black/40 to-black/10">
                        <span class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center" aria-hidden="true">
                            <svg class="w-5 h-5 text-[#D9BC8C]" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <p class="font-display mt-4 text-xl font-semibold">Masih ada pertanyaan?</p>
                        <p class="mt-1.5 text-sm text-white/80 leading-relaxed">Resepsionis kami siap membantu 24 jam melalui WhatsApp di
                            <a href="https://wa.me/6282142331906" target="_blank" rel="noopener" class="font-semibold text-[#E0C38F] underline decoration-[#E0C38F]/40 underline-offset-4 hover:decoration-[#E0C38F]">0821-4233-1906</a>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: daftar pertanyaan --}}
            <div class="space-y-3" id="faqList">
                @foreach ($faq as $item)
                    <details class="faq-item group rounded-2xl border border-gray-200 bg-white transition-colors open:bg-[#F7F3EC] open:border-[#EADFCB] open:shadow-sm">
                        <summary class="flex items-center gap-4 px-5 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            <span class="w-10 h-10 shrink-0 rounded-xl bg-[#F7F3EC] flex items-center justify-center text-gold transition group-open:bg-[#26211D] group-open:text-[#E0C38F]" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            </span>
                            <span class="flex-1 text-[15px] sm:text-base font-medium text-gray-900 group-open:font-semibold">{{ $item['q'] }}</span>
                            <span class="w-8 h-8 shrink-0 rounded-full border border-gray-200 bg-white flex items-center justify-center text-gold transition group-open:bg-[#26211D] group-open:border-transparent group-open:text-white">
                                <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
                            </span>
                        </summary>
                        <div class="faq-answer mr-5 mb-5 ml-[4.75rem] pt-4 border-t border-[#EADFCB]">
                            <p class="text-[15px] text-gray-600 leading-relaxed">{{ $item['a'] }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== KONTAK (info + jarak destinasi + peta) ===================== --}}
    <section id="kontak" class="scroll-mt-32 sm:scroll-mt-36 bg-sand">
        <div data-reveal class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <div class="text-center">
                <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase text-gold">Kontak</p>
                <h2 class="font-display mt-3 text-2xl sm:text-3xl font-semibold text-gray-900">Hubungi Kami</h2>
                <p class="mt-3 text-[15px] text-gray-600">Ada pertanyaan? Jangan ragu untuk menghubungi kami.</p>
            </div>

            {{-- Baris 1: info kontak (kartu kecil berjajar) --}}
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($kontak as $item)
                    <div class="bg-white rounded-xl border border-black/5 p-5 {{ $loop->first ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                        <span class="w-10 h-10 rounded-full bg-sand flex items-center justify-center text-gold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        </span>
                        <p class="mt-4 text-xs font-semibold tracking-[0.2em] uppercase text-gray-500">{{ $item['label'] }}</p>
                        @if (! empty($item['url']))
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm text-gray-900 underline decoration-gray-300 underline-offset-4 hover:decoration-gray-900">{{ $item['value'] }}</a>
                        @else
                            <p class="mt-1 text-sm text-gray-900 leading-relaxed">{{ $item['value'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Baris 2: jarak destinasi + peta, tinggi sejajar --}}
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-xl border border-black/5 p-5 sm:p-6">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-500">Jarak ke Destinasi Wisata</p>
                    <ul class="mt-4 divide-y divide-gray-200">
                    @foreach ($destinasi as $tempat)
                        @php $fotoTempat = $img('destinasi/' . $tempat['foto']); @endphp
                        <li class="flex items-center gap-4 py-4">
                            <div class="w-20 h-20 shrink-0 bg-gray-200 rounded-lg overflow-hidden">
                                @if ($fotoTempat)
                                    <img src="{{ $fotoTempat }}" alt="{{ $tempat['nama'] }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">{!! $iconImage !!}</svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900">{{ $tempat['nama'] }}</p>
                                <p class="text-sm text-gray-500">{{ $tempat['jarak'] }} dari hotel</p>
                                @if ($fotoTempat)
                                    {{-- Kredit foto ditaruh tepat di dekat fotonya (syarat lisensi CC BY-SA) --}}
                                    <a href="{{ $tempat['sumber'] }}" target="_blank" rel="noopener" class="mt-0.5 inline-block text-[11px] text-gray-400 hover:text-gray-600 hover:underline">
                                        {{ $tempat['kredit'] }} · {{ $tempat['lisensi'] }} · Wikimedia Commons
                                    </a>
                                @endif
                            </div>
                            <span class="hidden sm:block shrink-0 px-3 py-1 rounded-full bg-sand text-xs text-gray-600">{{ $tempat['kategori'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-xs text-gray-400 leading-relaxed">
                    Foto destinasi dari Wikimedia Commons dan digunakan sesuai lisensinya (CC0 / Creative Commons BY &amp; BY-SA). Klik keterangan di bawah nama tempat untuk melihat sumber asli.
                </p>
                </div>

                {{-- Peta (tombol "Open in Maps" sudah ada di dalam peta) --}}
                <div class="min-h-[360px] bg-gray-200 rounded-xl overflow-hidden">
                    <iframe src="https://www.google.com/maps?q={{ $mapsQuery }}&output=embed" class="w-full h-full min-h-[360px] border-0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Kazain Hotel di Google Maps" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== AJAKAN PESAN ===================== --}}
    <section class="bg-ink text-white">
        <div data-reveal class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
            <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase" style="color: #D9BC8C">Kazain Hotel</p>
            <h2 class="font-display mt-3 text-2xl sm:text-3xl font-semibold">Siap Menginap Bersama Kami?</h2>
            <p class="mt-4 text-[15px] text-white/75 leading-relaxed">Pesan kamar sekarang dan nikmati pengalaman menginap yang nyaman dan berkesan di Yogyakarta.</p>
            <a href="{{ route('customer.availability') }}" class="btn-gold mt-8 px-10 py-3.5 text-sm sm:text-base">
                Pesan Kamar Sekarang
            </a>
        </div>
    </section>

@endsection

@push('styles')
    <style>
        /* Jawaban FAQ muncul halus saat dibuka */
        @keyframes kz-fade-down { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .faq-item[open] .faq-answer { animation: kz-fade-down .3s ease-out; }
        @media (prefers-reduced-motion: reduce) { .faq-item[open] .faq-answer { animation: none; } }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            // ---------- Garis aktif di menu bagian ----------
            const links = document.querySelectorAll('[data-section-link]');
            const setActive = (id) => links.forEach((link) => {
                const active = link.dataset.sectionLink === id;
                link.classList.toggle('text-gray-900', active);
                link.classList.toggle('border-gold', active);
                link.classList.toggle('text-gray-500', !active);
                link.classList.toggle('border-transparent', !active);
            });

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => { if (entry.isIntersecting) setActive(entry.target.id); });
                }, { rootMargin: '-45% 0px -50% 0px' });
                links.forEach((link) => {
                    const section = document.getElementById(link.dataset.sectionLink);
                    if (section) observer.observe(section);
                });
            }

            // ---------- FAQ: hanya satu pertanyaan terbuka sekaligus ----------
            const faqItems = document.querySelectorAll('#faqList .faq-item');
            faqItems.forEach((item) => {
                item.addEventListener('toggle', () => {
                    if (item.open) faqItems.forEach((other) => { if (other !== item) other.open = false; });
                });
            });

            // ---------- Animasi muncul saat di-scroll ----------
            const revealItems = document.querySelectorAll('[data-reveal]');
            if ('IntersectionObserver' in window && revealItems.length) {
                document.documentElement.classList.add('js-reveal');
                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            revealObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                revealItems.forEach((item) => revealObserver.observe(item));
            }

            // ---------- Slider foto di bagian Tentang ----------
            const slider = document.getElementById('aboutSlider');
            const track = slider?.querySelector('[data-slider-track]');
            if (track && track.children.length > 1) {
                const total = track.children.length;
                const dots = slider.querySelectorAll('[data-slider-dot]');
                const current = () => Math.round(track.scrollLeft / track.clientWidth);
                const goTo = (i) => track.scrollTo({ left: ((i + total) % total) * track.clientWidth });

                const updateDots = () => {
                    const active = current();
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('w-6', i === active);
                        dot.classList.toggle('bg-white', i === active);
                        dot.classList.toggle('w-2', i !== active);
                        dot.classList.toggle('bg-white/55', i !== active);
                    });
                };

                track.addEventListener('scroll', () => window.requestAnimationFrame(updateDots), { passive: true });
                slider.querySelectorAll('[data-slider-nav]').forEach((btn) => {
                    btn.addEventListener('click', () => goTo(current() + Number(btn.dataset.sliderNav)));
                });
                dots.forEach((dot) => dot.addEventListener('click', () => goTo(Number(dot.dataset.sliderDot))));

                // Geser otomatis tiap 5 detik, berhenti saat kursor di atas foto
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                let paused = false;
                slider.addEventListener('mouseenter', () => { paused = true; });
                slider.addEventListener('mouseleave', () => { paused = false; });
                slider.addEventListener('touchstart', () => { paused = true; }, { passive: true });
                if (!reduceMotion) {
                    setInterval(() => { if (!paused && !document.hidden) goTo(current() + 1); }, 5000);
                }
            }
        })();
    </script>
@endpush