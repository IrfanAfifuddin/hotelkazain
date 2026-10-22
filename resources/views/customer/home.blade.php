@extends('layouts.customer')

@section('title', 'Beranda')

@php
    // ================= DATA STATIS HALAMAN BERANDA =================
    // Ganti/lengkapi di sini kalau ada perubahan info dari hotel.

    $alamat = 'Jl. Prawirotaman I, Timuran No. 562, Brontokusuman, Mergangsan, Yogyakarta 55153';

    // Foto: taruh file di public/images/. Kalau file belum ada, tampil kotak abu-abu.
    $img = fn (string $path) => file_exists(public_path('images/' . $path)) ? asset('images/' . $path) : null;

    $galeri = [
        'utama' => $img('hero.jpg'),
        'kanan_atas' => $img('taman.jpg'),
        'kanan_bawah' => $img('kolam-koi.jpg'),
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
        ['q' => 'Apakah tersedia parkir untuk mobil?', 'a' => 'Saat ini Kazain Hotel hanya menyediakan parkir gratis untuk sepeda motor. Parkir untuk mobil belum tersedia.'],
        ['q' => 'Apakah tersedia sarapan?', 'a' => 'Tidak, Kazain Hotel tidak menyediakan fasilitas sarapan. Namun kawasan Prawirotaman memiliki banyak warung makan, kafe, dan restoran yang mudah dijangkau dengan berjalan kaki.'],
        ['q' => 'Jam berapa check-in dan check-out?', 'a' => 'Untuk pemesanan Full Day, check-in mulai pukul 14.00 dan check-out paling lambat pukul 12.00 keesokan harinya. Untuk Short Time (5 atau 8 jam), jam check-out mengikuti jam check-in yang Anda pilih.'],
        ['q' => 'Apakah kamar dilengkapi AC dan air panas?', 'a' => 'Ya, semua kamar dilengkapi AC dan kamar mandi dalam dengan air panas (water heater).'],
        ['q' => 'Apakah tersedia Wi-Fi di kamar?', 'a' => 'Ya, tersedia Wi-Fi gratis yang dapat digunakan selama Anda menginap.'],
        ['q' => 'Bolehkah membawa tamu dari luar masuk ke kamar?', 'a' => 'Demi kenyamanan dan keamanan seluruh tamu, silakan konfirmasi terlebih dahulu ke resepsionis sebelum membawa tamu dari luar.'],
        ['q' => 'Apakah ada deposit saat menginap?', 'a' => 'Sebagai deposit, tamu wajib menitipkan KTP asli kepada resepsionis saat check-in. KTP akan dikembalikan saat check-out.'],
        ['q' => 'Apakah pemesanan bisa dibatalkan?', 'a' => 'Pemesanan yang sudah dibayar tidak dapat dibatalkan dan dana tidak dapat dikembalikan. Mohon pastikan tanggal, jam, dan tipe kamar sudah sesuai sebelum melakukan pembayaran.'],
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

    {{-- ===================== GALERI HERO ===================== --}}
    <section class="grid gap-1 lg:grid-cols-3 lg:h-[600px] bg-ink">
        {{-- Foto utama + judul --}}
        <div class="relative lg:col-span-2 h-[560px] sm:h-[540px] lg:h-full bg-gray-300 overflow-hidden">
            @if ($galeri['utama'])
                <img src="{{ $galeri['utama'] }}" alt="Tampak depan Kazain Hotel" class="absolute inset-0 w-full h-full object-cover">
            @endif
            <div class="absolute inset-0 bg-black/50"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center px-6 sm:px-10 text-center text-white">
                <p class="text-xs sm:text-sm tracking-[0.35em] uppercase text-white/80">Selamat Datang</p>
                <h1 class="mt-3 max-w-2xl text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight">Menginap Nyaman di Jantung Prawirotaman</h1>
                <p class="mt-4 max-w-xl text-sm sm:text-base text-white/85 leading-relaxed">Penginapan bernuansa homey dengan pelayanan ramah, dekat Keraton, Malioboro, dan kawasan wisata budaya Yogyakarta.</p>

                <a href="{{ route('customer.availability') }}" class="mt-7 inline-flex items-center px-9 py-3 bg-white text-gray-900 text-sm sm:text-base font-semibold uppercase tracking-wide rounded-sm hover:bg-white/90 transition">
                    Pesan Kamar
                </a>

                {{-- Statistik singkat: teks tipis tanpa kotak --}}
                <div class="mt-8 flex items-center divide-x divide-white/30">
                    <div class="px-6 sm:px-8">
                        <p class="text-lg sm:text-xl font-semibold">4,5 <span class="text-[#E0C38F]">&#9733;</span></p>
                        <p class="text-xs sm:text-sm text-white/75">Rating Google Maps</p>
                    </div>
                    <div class="px-6 sm:px-8">
                        <p class="text-lg sm:text-xl font-semibold">Yogyakarta</p>
                        <p class="text-xs sm:text-sm text-white/75">Lokasi Strategis</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dua foto kecil di kanan (desktop) --}}
        <div class="hidden lg:grid grid-rows-2 gap-1">
            @foreach (['kanan_atas' => 'Taman dalam Kazain Hotel', 'kanan_bawah' => 'Kolam ikan koi Kazain Hotel'] as $key => $alt)
                <div class="relative bg-gray-300 overflow-hidden">
                    @if ($galeri[$key])
                        <img src="{{ $galeri[$key] }}" alt="{{ $alt }}" class="absolute inset-0 w-full h-full object-cover">
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== MENU BAGIAN (lengket di bawah header) ===================== --}}
    <nav id="sectionNav" class="sticky top-16 sm:top-20 z-30 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-6 sm:gap-10 overflow-x-auto no-scrollbar">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-sm font-semibold tracking-[0.3em] uppercase text-gold">Tentang Kami</p>
                <h2 class="font-display mt-4 text-3xl sm:text-4xl font-semibold text-gray-900 leading-tight">Penginapan Nyaman dan Ramah di Yogyakarta</h2>
                <p class="mt-6 text-gray-600 leading-relaxed">
                    Kazain Hotel adalah penginapan yang menawarkan kenyamanan dengan suasana tenang dan pelayanan ramah. Berlokasi strategis di kawasan Prawirotaman, tamu dapat dengan mudah menjangkau Keraton Yogyakarta, Malioboro, Museum Perjuangan, dan berbagai tempat wisata budaya lainnya.
                </p>
                <p class="mt-4 text-gray-600 leading-relaxed">
                    Dengan kamar ber-AC yang bersih dan fasilitas lengkap, didukung resepsionis 24 jam, Kazain Hotel siap menjadi tempat istirahat nyaman selama Anda menjelajahi Kota Yogyakarta.
                </p>
                <div class="mt-8 flex items-start gap-3 text-gray-600">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconPin !!}</svg>
                    <span>{{ $alamat }}</span>
                </div>
            </div>

            <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
                @if ($img('hotel.jpg'))
                    <img src="{{ $img('hotel.jpg') }}" alt="Area depan Kazain Hotel" class="w-full h-full object-cover">
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== FASILITAS ===================== --}}
    <section id="fasilitas" class="scroll-mt-32 sm:scroll-mt-36 bg-sand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <p class="text-sm font-semibold tracking-[0.3em] uppercase text-gold">Fasilitas</p>
            <h2 class="font-display mt-4 text-3xl sm:text-4xl font-semibold text-gray-900">Fasilitas Kamar</h2>

            <div class="mt-10 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 border-t border-l border-gray-200">
                @foreach ($fasilitas as $item)
                    <div class="bg-white border-r border-b border-gray-200 px-4 py-8 flex flex-col items-center text-center gap-4">
                        <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span class="text-sm sm:text-base text-gray-700">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    <section id="faq" class="scroll-mt-32 sm:scroll-mt-36">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <p class="text-sm font-semibold tracking-[0.3em] uppercase text-gold text-center">Pertanyaan Umum</p>
            <h2 class="font-display mt-4 text-3xl sm:text-4xl font-semibold text-gray-900 text-center">Yang Sering Ditanyakan</h2>

            <div class="mt-10 border-t border-gray-200">
                @foreach ($faq as $item)
                    <details class="group border-b border-gray-200">
                        <summary class="flex items-center justify-between gap-4 py-5 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            <span class="text-lg text-gray-900">{{ $item['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-gold transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
                        </summary>
                        <p class="pb-6 pr-10 text-gray-600 leading-relaxed">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== KONTAK (info + jarak destinasi + peta) ===================== --}}
    <section id="kontak" class="scroll-mt-32 sm:scroll-mt-36 bg-sand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="text-center">
                <p class="text-sm font-semibold tracking-[0.3em] uppercase text-gold">Kontak</p>
                <h2 class="font-display mt-4 text-3xl sm:text-4xl font-semibold text-gray-900">Hubungi Kami</h2>
                <p class="mt-3 text-gray-600">Ada pertanyaan? Jangan ragu untuk menghubungi kami.</p>
            </div>

            {{-- Baris 1: info kontak (kartu kecil berjajar) --}}
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($kontak as $item)
                    <div class="bg-white p-5 {{ $loop->first ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                        <span class="w-10 h-10 border border-gray-200 flex items-center justify-center text-gold">
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
                <div class="bg-white p-5 sm:p-6">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-500">Jarak ke Destinasi Wisata</p>
                    <ul class="mt-4 divide-y divide-gray-200">
                    @foreach ($destinasi as $tempat)
                        @php $fotoTempat = $img('destinasi/' . $tempat['foto']); @endphp
                        <li class="flex items-center gap-4 py-4">
                            <div class="w-20 h-20 shrink-0 bg-gray-200 overflow-hidden">
                                @if ($fotoTempat)
                                    <img src="{{ $fotoTempat }}" alt="{{ $tempat['nama'] }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">{!! $iconImage !!}</svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-lg text-gray-900">{{ $tempat['nama'] }}</p>
                                <p class="text-gray-500">{{ $tempat['jarak'] }} dari hotel</p>
                                @if ($fotoTempat)
                                    {{-- Kredit foto ditaruh tepat di dekat fotonya (syarat lisensi CC BY-SA) --}}
                                    <a href="{{ $tempat['sumber'] }}" target="_blank" rel="noopener" class="mt-0.5 inline-block text-[11px] text-gray-400 hover:text-gray-600 hover:underline">
                                        {{ $tempat['kredit'] }} · {{ $tempat['lisensi'] }} · Wikimedia Commons
                                    </a>
                                @endif
                            </div>
                            <span class="hidden sm:block shrink-0 text-gray-500">{{ $tempat['kategori'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-xs text-gray-400 leading-relaxed">
                    Foto destinasi dari Wikimedia Commons dan digunakan sesuai lisensinya (CC0 / Creative Commons BY &amp; BY-SA). Klik keterangan di bawah nama tempat untuk melihat sumber asli.
                </p>
                </div>

                {{-- Peta (tombol "Open in Maps" sudah ada di dalam peta) --}}
                <div class="min-h-[360px] bg-gray-200 overflow-hidden">
                    <iframe src="https://www.google.com/maps?q={{ $mapsQuery }}&output=embed" class="w-full h-full min-h-[360px] border-0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Kazain Hotel di Google Maps" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== AJAKAN PESAN ===================== --}}
    <section class="bg-ink text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display text-3xl sm:text-4xl font-semibold">Siap Menginap Bersama Kami?</h2>
            <p class="mt-4 text-white/75 leading-relaxed">Pesan kamar sekarang dan nikmati pengalaman menginap yang nyaman dan berkesan di Yogyakarta.</p>
            <a href="{{ route('customer.availability') }}" class="mt-8 inline-flex items-center px-10 py-3.5 bg-white text-gray-900 font-semibold uppercase tracking-wide rounded-sm hover:bg-white/90 transition">
                Pesan Kamar Sekarang
            </a>
        </div>
    </section>

@endsection

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
        })();
    </script>
@endpush