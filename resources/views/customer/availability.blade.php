@extends('layouts.booking')

@section('title', 'Cek Ketersediaan')
@section('step', '1')

@php
    // Foto slider per tipe kamar (file di public/images/). Foto yang filenya belum ada dilewati.
    $foto = fn (array $files) => array_values(array_filter(array_map(
        fn ($f) => file_exists(public_path('images/' . $f)) ? asset('images/' . $f) : null, $files)));
    $roomPhotos = [
        'King' => $foto(['kamar-king.jpg', 'kamar-mandi.jpg', 'hotel.jpg']),
        'Twin' => $foto(['kamar-twin.jpg', 'kamar-mandi.jpg', 'taman.jpg']),
    ];
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

        {{-- Info parkir --}}
        <div class="flex items-start gap-3 border border-[#EADFCB] bg-sand rounded-2xl p-5 text-[15px] text-gray-700 leading-relaxed">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" />
            </svg>
            <p>
                <strong class="text-gray-900">Perhatian:</strong> Kazain Hotel <strong class="text-gray-900">tidak menyediakan parkir untuk kendaraan roda empat (mobil)</strong>. Area parkir hanya tersedia untuk sepeda motor. Untuk tamu yang membawa mobil, silakan gunakan area parkir umum di sekitar lokasi.
                Saat check-in, tamu wajib <strong class="text-gray-900">menitipkan KTP asli kepada resepsionis sebagai deposit</strong>, yang akan dikembalikan saat check-out.
                Pemesanan yang sudah dibayar <strong class="text-gray-900">tidak dapat dibatalkan</strong>.
            </p>
        </div>

        {{-- Judul --}}
        <div class="mt-10">
            <p class="text-xs sm:text-sm font-semibold tracking-[0.3em] text-gold">LANGKAH 1</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-semibold text-gray-900">Cek Ketersediaan Kamar</h1>
            <p class="mt-2 text-gray-600">Pilih tanggal, tipe pemesanan, dan jam check-in yang tersedia.</p>
        </div>

        {{-- ===================== KALENDER + RINGKASAN TANGGAL ===================== --}}
        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-start">

            {{-- Kalender (ringkas) --}}
            <div class="bg-white border border-black/5 rounded-3xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 sm:px-5 py-4 bg-gradient-to-br from-[#26211D] via-[#3A2F25] to-[#7A5F3A] text-white">
                    <button type="button" id="prevMonth" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Bulan sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" /></svg>
                    </button>

                    <label class="relative flex items-center gap-2 font-display text-lg font-semibold cursor-pointer">
                        <span id="monthLabel">-</span>
                        <svg class="w-4 h-4 text-[#D9BC8C]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" /></svg>
                        <select id="monthSelect" class="absolute inset-0 opacity-0 cursor-pointer text-gray-900" aria-label="Pilih bulan"></select>
                    </label>

                    <button type="button" id="nextMonth" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Bulan berikutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" /></svg>
                    </button>
                </div>

                <div class="grid grid-cols-7 px-3 sm:px-4 pt-4 pb-1 text-center text-[11px] font-semibold tracking-wider text-gray-400">
                    @foreach (['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'] as $hari)
                        <div class="py-1">{{ $hari }}</div>
                    @endforeach
                </div>

                <div id="calendarGrid" class="grid grid-cols-7 gap-1 px-3 sm:px-4 pb-4"></div>
            </div>

            <div class="space-y-4">
                {{-- Belum pilih tanggal --}}
                <p id="noDateHint" class="px-5 py-4 bg-white border border-black/5 rounded-2xl text-center text-gray-500">Pilih tanggal pada kalender untuk melanjutkan.</p>

                {{-- Ringkasan tanggal --}}
                <div id="dateSummaryCard" class="hidden bg-white border border-black/5 rounded-3xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-br from-[#26211D] via-[#3A2F25] to-[#7A5F3A] text-white">
                        <p class="text-xs font-semibold tracking-[0.2em] uppercase text-[#D9BC8C]">Tanggal dipilih</p>
                        <p class="font-display mt-1 text-xl font-semibold" id="selectedDateLabel">-</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="flex items-center justify-between px-6 py-3.5">
                            <span class="text-gray-700">Short Time</span>
                            <span id="shortStatus" class="px-3 py-1 bg-sand rounded-full text-sm font-semibold text-gray-800">-</span>
                        </div>
                        <div class="flex items-center justify-between px-6 py-3.5">
                            <span class="text-gray-700">Full Day</span>
                            <span id="fullStatus" class="px-3 py-1 bg-sand rounded-full text-sm font-semibold text-gray-800">-</span>
                        </div>
                        <div class="flex items-center justify-between px-6 py-4 bg-[#FCFAF7]">
                            <span class="text-gray-600">Sisa kamar tersedia</span>
                            <span class="font-bold text-gray-900"><span id="totalRemaining">0</span> kamar</span>
                        </div>
                    </div>
                </div>

                {{-- Legenda kalender --}}
                <div class="px-5 py-4 bg-white border border-black/5 rounded-2xl">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-gold">Keterangan</p>
                    <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm text-gray-600">
                        <span class="flex items-center gap-2"><span class="w-5 h-5 flex items-center justify-center"><span class="w-2 h-2 rounded-full bg-[#10B981]"></span></span>Tersedia</span>
                        <span class="flex items-center gap-2"><span class="w-5 h-5 flex items-center justify-center"><span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span></span>Hampir Penuh</span>
                        <span class="flex items-center gap-2"><span class="w-5 h-5 rounded-lg bg-[#FEF2F2] flex items-center justify-center"><span class="w-1.5 h-1.5 rounded-full bg-[#F87171]"></span></span>Penuh</span>
                        <span class="flex items-center gap-2"><span class="w-5 h-5 rounded-lg bg-ink"></span>Dipilih</span>
                        <span class="flex items-center gap-2"><span class="w-5 h-5 rounded-lg day-past border border-black/5"></span>Sudah lewat</span>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">Status kamar tampil setelah tanggal dipilih.</p>
                </div>
            </div>
        </div>

        {{-- ===================== SETELAH PILIH TANGGAL ===================== --}}
        <div id="afterDate" class="hidden">

            {{-- Pilih tipe pemesanan --}}
            <h2 id="typeHeading" class="font-display mt-10 text-xl font-semibold text-gray-900 scroll-mt-28">Pilih Tipe Pemesanan</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <button type="button" data-booking-type="short" class="type-card text-left border-2 rounded-2xl p-5 transition hover:shadow-md">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center gap-3 font-semibold text-gray-900 text-lg">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                            Short Time
                        </span>
                        <svg class="type-check w-5 h-5 text-gray-900 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                    </span>
                    <span class="mt-2 block text-gray-600">Sewa per sesi · 5 jam atau 8 jam</span>
                </button>

                <button type="button" data-booking-type="full" class="type-card text-left border-2 rounded-2xl p-5 transition hover:shadow-md">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center gap-3 font-semibold text-gray-900 text-lg">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" /></svg>
                            Full Day
                        </span>
                        <svg class="type-check w-5 h-5 text-gray-900 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                    </span>
                    <span class="mt-2 block text-gray-600">Per malam · Check-in 14:00 WIB</span>
                </button>
            </div>

            {{-- Panel durasi, jam, dan harga --}}
            <div class="mt-6 bg-white border border-black/5 rounded-2xl shadow-sm divide-y divide-gray-100">

                {{-- Durasi (khusus Short Time) --}}
                <div id="durationSection" class="p-6">
                    <p class="text-xs font-semibold tracking-[0.2em] text-gold uppercase">Pilih Durasi</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button type="button" data-duration="5" class="duration-btn py-3 rounded-full border text-lg font-medium transition">5 Jam</button>
                        <button type="button" data-duration="8" class="duration-btn py-3 rounded-full border text-lg font-medium transition">8 Jam</button>
                    </div>
                </div>

                {{-- Jam check-in --}}
                <div class="p-6">
                    <p class="text-xs font-semibold tracking-[0.2em] text-gold uppercase">Pilih Jam Check-in · <span id="timeDateLabel">-</span></p>

                    <div id="hourGrid" class="mt-3 space-y-4"></div>

                    <div id="fullDayTimeInfo" class="hidden">
                        <p class="mt-3 text-gray-600">
                            Untuk Full Day, check-in mulai pukul <strong class="text-gray-900">14:00 WIB</strong> dan check-out pukul <strong class="text-gray-900">12:00 WIB</strong> setelah malam terakhir.
                        </p>

                        {{-- Jumlah malam --}}
                        <p class="mt-5 text-xs font-semibold tracking-[0.2em] text-gold uppercase">Jumlah Malam</p>
                        <div class="mt-3 flex items-center gap-4">
                            <button type="button" id="nightsMinus" class="w-12 h-12 bg-white border border-gray-300 rounded-full text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Kurangi malam">&minus;</button>
                            <span id="nightsCount" class="w-8 text-center text-2xl font-semibold">1</span>
                            <button type="button" id="nightsPlus" class="w-12 h-12 bg-white border border-gray-300 rounded-full text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Tambah malam">+</button>
                            <span class="text-gray-600">malam</span>
                        </div>
                    </div>

                    <div id="hourLegend" class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border border-gray-300 bg-white"></span>Tersedia</span>
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border border-gray-200 bg-gray-100"></span>Penuh</span>
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-gray-900"></span>Dipilih</span>
                    </div>

                    <div id="timeSummary" class="hidden mt-4 flex items-center gap-3 px-4 py-3 bg-sand rounded-xl text-gray-700">
                        <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                        <span>Check-in <strong id="checkInLabel" class="text-gray-900">-</strong> · Check-out <strong id="checkOutLabel" class="text-gray-900">-</strong></span>
                    </div>
                </div>

                {{-- Harga kamar --}}
                <div class="p-6">
                    <p class="text-xs font-semibold tracking-[0.2em] text-gold uppercase">Harga Kamar</p>
                    <div id="priceCards" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            {{-- Jumlah tamu --}}
            <div class="mt-6 bg-white border border-black/5 rounded-2xl shadow-sm p-6">
                <p class="text-xs font-semibold tracking-[0.2em] text-gold uppercase">Jumlah Tamu</p>
                <div class="mt-3 flex items-center gap-4">
                    <button type="button" id="guestMinus" class="w-12 h-12 bg-white border border-gray-300 rounded-full text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Kurangi tamu">&minus;</button>
                    <span id="guestCount" class="w-8 text-center text-2xl font-semibold">1</span>
                    <button type="button" id="guestPlus" class="w-12 h-12 bg-white border border-gray-300 rounded-full text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Tambah tamu">+</button>
                    <span class="text-gray-600">orang</span>
                </div>
                <p id="guestHint" class="mt-3 text-sm text-gray-500">Pilih kamar di bawah untuk melihat kapasitas</p>
            </div>

            {{-- Pilih kamar --}}
            <h2 id="roomsHeading" class="font-display mt-10 text-xl font-semibold text-gray-900 scroll-mt-28">Pilih Kamar <span class="font-sans text-base font-normal text-gray-500">(bisa pilih beberapa kamar)</span></h2>
            <div id="roomCards" class="mt-4 space-y-4"></div>

            {{-- Total + tombol lanjut (di HP menempel di bawah layar) --}}
            <div id="totalBar" class="bottom-0 z-20 -mx-4 px-4 pt-3 pb-4 mt-6 bg-gradient-to-t from-[#FCFAF7] via-[#FCFAF7] to-[#FCFAF7]/0 sm:static sm:mx-0 sm:px-0 sm:pt-0 sm:pb-0 sm:bg-none">
                <div class="rounded-2xl bg-ink text-white px-5 sm:px-6 py-3.5 sm:py-5 flex items-center sm:items-start justify-between gap-4 shadow-lg shadow-black/10">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs font-semibold tracking-[0.2em] uppercase text-[#D9BC8C]">Total estimasi</p>
                        <p id="totalDetail" class="mt-1 sm:mt-1.5 text-xs sm:text-sm text-white/70 truncate sm:whitespace-normal">Belum ada kamar dipilih</p>
                    </div>
                    <p id="totalPrice" class="shrink-0 whitespace-nowrap text-xl sm:text-2xl font-bold">Rp 0</p>
                </div>

                <button type="button" id="submitButton" disabled
                        class="mt-3 sm:mt-6 w-full py-3.5 sm:py-4 rounded-full text-base font-semibold uppercase tracking-wide transition bg-gray-200 text-gray-400 cursor-not-allowed">
                    Lanjut ke Isi Data Diri &rarr;
                </button>
                <p id="submitHint" class="mt-2 sm:mt-3 text-center text-sm text-gray-500"></p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .day-full {
            background-image: repeating-linear-gradient(135deg, #f5f1ea 0 5px, #ffffff 5px 10px);
        }

        /* Tanggal yang sudah lewat: garis-garis samar */
        .day-past {
            background-image: repeating-linear-gradient(135deg, #f3efe8 0 3px, #ffffff 3px 7px);
        }

        /* Angka total berdenyut saat berubah */
        @keyframes kz-pulse { 0% { transform: scale(1); } 40% { transform: scale(1.08); color: #D9BC8C; } 100% { transform: scale(1); } }
        .kz-pulse { animation: kz-pulse .45s ease-out; display: inline-block; }

        /* Kartu berkilau saat ketersediaan sedang dicek */
        @keyframes kz-shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
        .kz-skeleton {
            background: linear-gradient(90deg, #f3eee6 0%, #faf7f2 40%, #f3eee6 80%);
            background-size: 800px 100%;
            animation: kz-shimmer 1.2s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .kz-pulse, .kz-skeleton { animation: none; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            // =====================================================
            // DATA
            // =====================================================
            const ROOM_TYPES = @json($roomTypes);
            const ROOM_PHOTOS = @json($roomPhotos);
            const photoIndex = {}; // posisi foto per tipe kamar, supaya tidak reset saat render ulang
            const CHECKOUT_URL = @json(route('customer.checkout'));
            const AVAILABILITY_URL = @json(route('customer.availability'));

            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const MONTHS_AHEAD = 5; // bisa pesan sampai 5 bulan ke depan

            const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
            const pad = (n) => String(n).padStart(2, '0');
            const dateKey = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            const totalOf = (type) => ROOM_TYPES.find((r) => r.type === type).total_rooms;

            function getDayInfo(date) {
                if (date < today) return { status: 'past', remaining: {} };

                if (state.availability && state.availability.date === dateKey(date)) {
                    const remaining = Object.fromEntries(
                        Object.entries(state.availability.summary.by_room_type)
                            .map(([type, summary]) => [type, summary.available]),
                    );
                    const available = state.availability.summary.available_rooms;

                    return {
                        status: available === 0 ? 'full' : available <= 2 ? 'almost' : 'available',
                        remaining,
                    };
                }

                return { status: 'unknown', remaining: {} };
            }

            function isHourAvailable(date, hour, duration) {
                const sameDay = dateKey(date) === dateKey(today);
                if (sameDay && hour <= now.getHours()) return false;
                return true;
            }

            // =====================================================
            // STATE
            // =====================================================
            const state = {
                viewYear: today.getFullYear(),
                viewMonth: today.getMonth(),
                selectedDate: null,
                bookingType: 'short', // 'short' | 'full'
                duration: 5,
                nights: 1, // jumlah malam untuk Full Day (backend menerima 1–30 malam)
                hour: null,
                guests: 1,
                quantities: Object.fromEntries(ROOM_TYPES.map((r) => [r.type, 0])),
                availability: @json($availability),
                availabilityError: null,
                availabilityRequestId: 0,
                loading: false,
            };

            // =====================================================
            // ELEMEN
            // =====================================================
            const $ = (id) => document.getElementById(id);
            const el = {
                prevMonth: $('prevMonth'),
                nextMonth: $('nextMonth'),
                monthLabel: $('monthLabel'),
                monthSelect: $('monthSelect'),
                calendarGrid: $('calendarGrid'),
                noDateHint: $('noDateHint'),
                dateSummaryCard: $('dateSummaryCard'),
                typeHeading: $('typeHeading'),
                roomsHeading: $('roomsHeading'),
                afterDate: $('afterDate'),
                selectedDateLabel: $('selectedDateLabel'),
                shortStatus: $('shortStatus'),
                fullStatus: $('fullStatus'),
                totalRemaining: $('totalRemaining'),
                durationSection: $('durationSection'),
                timeDateLabel: $('timeDateLabel'),
                hourGrid: $('hourGrid'),
                hourLegend: $('hourLegend'),
                fullDayTimeInfo: $('fullDayTimeInfo'),
                nightsMinus: $('nightsMinus'),
                nightsPlus: $('nightsPlus'),
                nightsCount: $('nightsCount'),
                timeSummary: $('timeSummary'),
                checkInLabel: $('checkInLabel'),
                checkOutLabel: $('checkOutLabel'),
                priceCards: $('priceCards'),
                guestMinus: $('guestMinus'),
                guestPlus: $('guestPlus'),
                guestCount: $('guestCount'),
                guestHint: $('guestHint'),
                roomCards: $('roomCards'),
                totalDetail: $('totalDetail'),
                totalPrice: $('totalPrice'),
                submitButton: $('submitButton'),
                submitHint: $('submitHint'),
                totalBar: $('totalBar'),
            };

            // =====================================================
            // HELPER PERHITUNGAN
            // =====================================================
            const priceKey = () => (state.bookingType === 'full' ? 'full' : String(state.duration));
            const durationText = () => (state.bookingType === 'full' ? `${state.nights} malam` : `${state.duration} jam`);
            const MAX_NIGHTS = 30;
            const durationHours = () => (state.bookingType === 'full' ? 24 * state.nights : state.duration);
            const checkInHour = () => (state.bookingType === 'full' ? 14 : state.hour);

            function getCheckOut() {
                const hour = checkInHour();
                if (!state.selectedDate || hour === null) return null;
                const start = new Date(state.selectedDate);
                start.setHours(hour, 0, 0, 0);
                const end = new Date(start);
                if (state.bookingType === 'full') {
                    end.setDate(end.getDate() + state.nights);
                    end.setHours(12, 0, 0, 0);
                } else {
                    end.setHours(end.getHours() + state.duration);
                }
                return { start, end };
            }

            function timeText(date, withDateIfDifferentFrom) {
                let text = `${pad(date.getHours())}:${pad(date.getMinutes())} WIB`;
                if (withDateIfDifferentFrom && dateKey(date) !== dateKey(withDateIfDifferentFrom)) {
                    text += ` (${date.getDate()} ${MONTHS_SHORT[date.getMonth()]})`;
                }
                return text;
            }

            const selectedRooms = () => ROOM_TYPES.filter((r) => state.quantities[r.type] > 0);
            const totalRoomsSelected = () => Object.values(state.quantities).reduce((a, b) => a + b, 0);
            const totalCapacity = () => ROOM_TYPES.reduce((sum, r) => sum + r.capacity * state.quantities[r.type], 0);
            const totalPrice = () => ROOM_TYPES.reduce((sum, r) => sum + r.prices[priceKey()] * state.quantities[r.type], 0) * (state.bookingType === 'full' ? state.nights : 1);

            function statusLabel(status) {
                return {
                    available: 'Tersedia',
                    almost: 'Terbatas',
                    full: 'Penuh',
                    unknown: 'Cek ketersediaan',
                }[status] || '-';
            }

            // =====================================================
            // RENDER
            // =====================================================
            function renderMonthSelect() {
                el.monthSelect.innerHTML = '';
                for (let i = 0; i <= MONTHS_AHEAD; i++) {
                    const d = new Date(today.getFullYear(), today.getMonth() + i, 1);
                    const option = document.createElement('option');
                    option.value = `${d.getFullYear()}-${d.getMonth()}`;
                    option.textContent = `${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
                    el.monthSelect.appendChild(option);
                }
            }

            function renderCalendar() {
                const { viewYear, viewMonth } = state;
                el.monthLabel.textContent = `${MONTHS[viewMonth]} ${viewYear}`;
                el.monthSelect.value = `${viewYear}-${viewMonth}`;

                const offset = (viewYear - today.getFullYear()) * 12 + (viewMonth - today.getMonth());
                el.prevMonth.disabled = offset <= 0;
                el.nextMonth.disabled = offset >= MONTHS_AHEAD;

                const firstDay = new Date(viewYear, viewMonth, 1).getDay();
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                let html = '';
                const cell = 'h-11 sm:h-12 rounded-xl flex flex-col items-center justify-center text-sm transition';
                for (let i = 0; i < firstDay; i++) {
                    html += '<div class="h-11 sm:h-12"></div>';
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(viewYear, viewMonth, day);
                    const info = getDayInfo(date);
                    const isSelected = state.selectedDate && dateKey(state.selectedDate) === dateKey(date);

                    if (info.status === 'past') {
                        html += `<div class="${cell} day-past text-gray-400 font-medium cursor-not-allowed" aria-label="${day}, sudah lewat" title="Sudah lewat">${day}</div>`;
                        continue;
                    }
                    if (info.status === 'full') {
                        html += `<div class="${cell} bg-[#FEF2F2] text-[#B91C1C]/60 font-medium cursor-not-allowed" aria-label="${day}, penuh" title="Penuh">
                                    <span class="line-through">${day}</span>
                                    <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-[#F87171]"></span>
                                 </div>`;
                        continue;
                    }

                    const label = info.status === 'almost' ? 'Hampir Penuh' : info.status === 'unknown' ? 'Cek ketersediaan' : 'Tersedia';
                    const dot = info.status === 'almost' ? 'bg-[#F59E0B]' : info.status === 'available' ? 'bg-[#10B981]' : 'bg-transparent';
                    const color = isSelected
                        ? 'bg-ink text-white shadow-md shadow-black/20'
                        : 'text-gray-900 hover:bg-sand';

                    html += `<button type="button" data-date="${dateKey(date)}" class="${cell} ${color} font-semibold" aria-pressed="${isSelected}" aria-label="${day}, ${label}" title="${label}">
                                <span>${day}</span>
                                <span class="mt-0.5 w-1.5 h-1.5 rounded-full ${dot}"></span>
                             </button>`;
                }

                const trailing = (7 - ((firstDay + daysInMonth) % 7)) % 7;
                for (let i = 0; i < trailing; i++) {
                    html += '<div class="h-11 sm:h-12"></div>';
                }

                el.calendarGrid.innerHTML = html;
            }

            function renderDateSummary() {
                const hasDate = !!state.selectedDate;
                el.afterDate.classList.toggle('hidden', !hasDate);
                el.noDateHint.classList.toggle('hidden', hasDate);
                el.dateSummaryCard.classList.toggle('hidden', !hasDate);
                if (!hasDate) return;

                const d = state.selectedDate;
                const info = getDayInfo(d);
                const label = `${DAYS[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;

                el.selectedDateLabel.textContent = label;
                el.timeDateLabel.textContent = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
                const chip = {
                    available: 'bg-[#D1FAE5] text-[#065F46]',
                    almost: 'bg-[#FEF3C7] text-[#92400E]',
                    full: 'bg-[#FEE2E2] text-[#991B1B]',
                    unknown: 'bg-sand text-gray-700',
                }[info.status] || 'bg-sand text-gray-700';
                [el.shortStatus, el.fullStatus].forEach((badge) => {
                    badge.textContent = statusLabel(info.status);
                    badge.className = `px-3 py-1 rounded-full text-sm font-semibold ${chip}`;
                });
                el.totalRemaining.textContent = Object.values(info.remaining).reduce((a, b) => a + b, 0);
            }

            function renderTypeCards() {
                document.querySelectorAll('.type-card').forEach((card) => {
                    const active = card.dataset.bookingType === state.bookingType;
                    card.classList.toggle('border-gray-900', active);
                    card.classList.toggle('bg-gray-50', active);
                    card.classList.toggle('border-gray-200', !active);
                    card.classList.toggle('bg-white', !active);
                    card.setAttribute('aria-pressed', String(active));
                    card.querySelector('.type-check').classList.toggle('hidden', !active);
                });
            }

            function renderTimePanel() {
                const isFull = state.bookingType === 'full';
                el.durationSection.classList.toggle('hidden', isFull);
                el.hourGrid.classList.toggle('hidden', isFull);
                el.hourLegend.classList.toggle('hidden', isFull);
                el.fullDayTimeInfo.classList.toggle('hidden', !isFull);
                el.nightsCount.textContent = state.nights;
                el.nightsMinus.disabled = state.nights <= 1;
                el.nightsPlus.disabled = state.nights >= MAX_NIGHTS;

                document.querySelectorAll('.duration-btn').forEach((btn) => {
                    const active = Number(btn.dataset.duration) === state.duration;
                    btn.className = 'duration-btn py-3 rounded-full border text-lg font-medium transition ' +
                        (active ? 'bg-gray-900 border-gray-900 text-white' : 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50');
                });

                if (!isFull && state.selectedDate) {
                    // Jam dikelompokkan supaya lebih mudah dipilih
                    const groups = [
                        { name: 'Pagi', from: 5, to: 10 },
                        { name: 'Siang', from: 11, to: 14 },
                        { name: 'Sore', from: 15, to: 17 },
                        { name: 'Malam', from: 18, to: 23 },
                        { name: 'Dini Hari', from: 0, to: 4 },
                    ];
                    let html = '';
                    groups.forEach((group) => {
                        const hours = [];
                        for (let h = group.from; h <= group.to; h++) hours.push(h);
                        // Kelompok yang semua jamnya sudah lewat tidak ditampilkan
                        if (!hours.some((h) => isHourAvailable(state.selectedDate, h, state.duration))) return;

                        html += `<div>
                                    <p class="mb-2 text-xs font-medium text-gray-500">${group.name} <span class="text-gray-400">· ${pad(group.from)}:00–${pad(group.to)}:59</span></p>
                                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">`;
                        hours.forEach((h) => {
                            const available = isHourAvailable(state.selectedDate, h, state.duration);
                            const selected = state.hour === h;
                            const label = `${pad(h)}:00`;
                            if (!available) {
                                html += `<button type="button" disabled class="py-2.5 rounded-full border border-gray-100 bg-gray-50 text-gray-300 line-through cursor-not-allowed">${label}</button>`;
                            } else {
                                html += `<button type="button" data-hour="${h}" class="py-2.5 rounded-full border transition ${selected ? 'bg-gray-900 border-gray-900 text-white' : 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50'}">${label}</button>`;
                            }
                        });
                        html += '</div></div>';
                    });
                    el.hourGrid.innerHTML = html;
                }

                const times = getCheckOut();
                el.timeSummary.classList.toggle('hidden', !times);
                if (times) {
                    el.checkInLabel.textContent = timeText(times.start);
                    el.checkOutLabel.textContent = timeText(times.end, times.start);
                }
            }

            function renderPrices() {
                const info = state.selectedDate ? getDayInfo(state.selectedDate) : { remaining: {} };
                const unit = state.bookingType === 'full' ? 'per malam' : `per sesi · ${state.duration} jam`;

                el.priceCards.innerHTML = ROOM_TYPES.map((room) => `
                    <div class="bg-[#FCFAF7] border border-black/5 rounded-xl p-5">
                        <p class="text-gray-600">${room.short_name}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">${rupiah(room.prices[priceKey()])}</p>
                        <p class="mt-1 text-sm text-gray-400">${unit}</p>
                        <p class="text-sm text-gray-400">Sisa: ${info.remaining[room.type] ?? 0} kamar</p>
                    </div>
                `).join('');
            }

            function renderGuests() {
                el.guestCount.textContent = state.guests;
                el.guestMinus.disabled = state.guests <= 1;
                el.guestPlus.disabled = state.guests >= 10;

                const totalRooms = Object.values(state.quantities).reduce((sum, qty) => sum + qty, 0);
                const capacity = totalRooms * 2; // Each room max 2 guests
                if (totalRooms === 0) {
                    el.guestHint.className = 'mt-3 text-sm text-gray-500';
                    el.guestHint.textContent = 'Pilih kamar di bawah untuk melihat kapasitas';
                } else if (state.guests > capacity) {
                    el.guestHint.className = 'mt-3 text-sm text-red-600';
                    el.guestHint.textContent = `Kamar yang dipilih hanya muat ${capacity} orang. Tambah kamar atau kurangi jumlah tamu.`;
                } else {
                    el.guestHint.className = 'mt-3 text-sm text-gray-500';
                    el.guestHint.textContent = `Kapasitas kamar yang dipilih: maksimal ${capacity} orang`;
                }
            }

            function renderRooms() {
                const info = state.selectedDate ? getDayInfo(state.selectedDate) : { remaining: {} };
                const unit = state.bookingType === 'full' ? 'malam' : `${state.duration} jam`;

                if (state.loading) {
                    el.roomCards.innerHTML = ROOM_TYPES.map(() => `
                        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row gap-4 sm:gap-5" aria-hidden="true">
                            <div class="kz-skeleton w-full sm:w-72 h-52 sm:h-48 rounded-xl"></div>
                            <div class="flex-1 space-y-3 py-1">
                                <div class="kz-skeleton h-5 w-1/2 rounded-full"></div>
                                <div class="kz-skeleton h-4 w-3/4 rounded-full"></div>
                                <div class="kz-skeleton h-4 w-2/3 rounded-full"></div>
                                <div class="kz-skeleton h-7 w-1/3 rounded-full mt-6"></div>
                            </div>
                        </div>`).join('') + '<p class="text-center text-sm text-gray-500">Memeriksa ketersediaan kamar…</p>';
                    return;
                }

                el.roomCards.innerHTML = ROOM_TYPES.map((room) => {
                    const remaining = info.remaining[room.type] ?? 0;
                    const availableRooms = state.availability?.date === dateKey(state.selectedDate)
                        ? state.availability.data.filter((availableRoom) => availableRoom.room_type === room.type && availableRoom.is_available)
                        : [];
                    const soldOut = !availableRooms.length;
                    const selectedCount = state.quantities[room.type] || 0;

                    let description = room.description;
                    if (soldOut) description = 'Tidak tersedia pada tanggal ini';
                    else if (remaining <= 2) description = `${room.description.split('·')[0].trim()} · hanya ${remaining} kamar tersedia`;

                    const roomDbType = room.type.split(' ')[0]; // "King Bed" -> "King", "Twin Bed" -> "Twin"
                    const photos = ROOM_PHOTOS[roomDbType]?.length ? ROOM_PHOTOS[roomDbType] : (room.image ? [room.image] : []);
                    const photo = photos.length
                        ? `<div class="flex h-full overflow-x-auto snap-x snap-mandatory no-scrollbar" data-photo-track="${roomDbType}">
                               ${photos.map((src, i) => `<img src="${src}" alt="${room.name} – foto ${i + 1}" class="w-full h-full shrink-0 snap-center object-cover" loading="lazy">`).join('')}
                           </div>
                           ${photos.length > 1 ? `
                           <button type="button" data-photo-nav="-1" data-photo-type="${roomDbType}" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white flex items-center justify-center" aria-label="Foto sebelumnya">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                           </button>
                           <button type="button" data-photo-nav="1" data-photo-type="${roomDbType}" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white flex items-center justify-center" aria-label="Foto berikutnya">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                           </button>
                           <span class="absolute left-2 bottom-2 px-2 py-0.5 rounded-full bg-black/55 text-white text-xs font-semibold tabular-nums" data-photo-counter="${roomDbType}">${(photoIndex[roomDbType] || 0) + 1} / ${photos.length}</span>` : ''}`
                        : `<span class="flex flex-col items-center justify-center gap-2 h-full text-gray-400">
                               <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg>
                               <span class="text-sm tracking-wider">FOTO</span>
                           </span>`;

                    return `
                    <div class="bg-white border-2 rounded-2xl p-4 sm:p-5 transition ${selectedCount > 0 ? 'border-gray-900 shadow-lg shadow-black/5' : 'border-transparent shadow-sm'} ${soldOut ? 'opacity-60' : ''}">
                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-5">
                            <div class="relative w-full sm:w-72 h-52 sm:h-48 shrink-0 bg-gray-100 rounded-xl overflow-hidden">${photo}</div>
                            <div class="flex-1 min-w-0">
                                <div>
                                    <p class="font-display text-xl font-semibold text-gray-900">${room.name}</p>
                                    <p class="text-gray-600">${description}</p>
                                    <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                                        <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        Maks. ${room.capacity || 2} tamu per kamar
                                    </p>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    ${room.facilities.map((f) => `<span class="px-2.5 py-1 bg-sand rounded-full text-xs text-gray-700">${f}</span>`).join('')}
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-gray-400">Harga / ${unit}</p>
                                    <p class="text-xl font-bold text-gray-900">${rupiah(room.prices[priceKey()])}</p>
                                </div>
                            </div>
                        </div>
                        ${!soldOut && availableRooms.length > 0 ? `
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-3">Jumlah kamar (tersedia: ${availableRooms.length})</p>
                            <div class="flex items-center gap-3">
                                <button type="button" data-room-type="${room.type}" data-action="decrease" class="quantity-btn flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition" ${state.quantities[room.type] === 0 ? 'disabled' : ''}>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14"/></svg>
                                </button>
                                <span class="quantity-display text-xl font-semibold text-gray-900 w-12 text-center">${state.quantities[room.type] || 0}</span>
                                <button type="button" data-room-type="${room.type}" data-action="increase" class="quantity-btn flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition" ${state.quantities[room.type] >= availableRooms.length ? 'disabled' : ''}>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14m7-7H5"/></svg>
                                </button>
                                <span class="text-sm text-gray-500 ml-2">kamar</span>
                            </div>
                        </div>
                        ` : ''}
                    </div>`;
                }).join('');
            }

            function getMissingStep() {
                if (state.loading) return 'Memeriksa ketersediaan kamar...';
                if (!state.selectedDate) return 'Pilih tanggal terlebih dahulu.';
                if (state.bookingType === 'short' && state.hour === null) return 'Pilih jam check-in.';
                const totalQuantity = Object.values(state.quantities).reduce((sum, q) => sum + q, 0);
                if (totalQuantity === 0) return 'Pilih minimal satu kamar.';
                if (state.availabilityError) return state.availabilityError;
                // Capacity check: each room max 2 guests, total capacity = num rooms * 2
                if (state.guests > totalQuantity * 2) return 'Jumlah tamu melebihi kapasitas kamar.';
                return null;
            }

            function renderTotal() {
                const totalQuantity = Object.values(state.quantities).reduce((sum, q) => sum + q, 0);
                const totalPriceValue = ROOM_TYPES.reduce((sum, roomType) => {
                    const quantity = state.quantities[roomType.type] || 0;
                    const price = roomType.prices[priceKey()] || 0;
                    return sum + price * quantity;
                }, 0) * (state.bookingType === 'full' ? state.nights : 1);

                const roomsDetail = ROOM_TYPES
                    .filter(rt => (state.quantities[rt.type] || 0) > 0)
                    .map(rt => `${state.quantities[rt.type]} ${rt.type}`)
                    .join(', ');

                const newTotal = rupiah(totalPriceValue);
                if (el.totalPrice.textContent !== newTotal && totalQuantity > 0) {
                    el.totalPrice.classList.remove('kz-pulse');
                    void el.totalPrice.offsetWidth; // ulang animasi
                    el.totalPrice.classList.add('kz-pulse');
                }
                el.totalPrice.textContent = newTotal;
                // Di HP, ringkasan total baru menempel di bawah layar setelah ada kamar dipilih
                el.totalBar.classList.toggle('sticky', totalQuantity > 0);
                el.totalDetail.textContent = totalQuantity
                    ? `${totalQuantity} kamar (${roomsDetail}) · ${durationText()}`
                    : 'Belum ada kamar dipilih';

                const missing = getMissingStep();
                el.submitButton.disabled = !!missing;
                el.submitButton.className = missing
                    ? 'mt-6 w-full py-4 rounded-full text-base font-semibold uppercase tracking-wide transition bg-gray-200 text-gray-400 cursor-not-allowed'
                    : 'btn-gold mt-6 w-full py-4 text-base';
                el.submitHint.textContent = state.availabilityError || missing || '';
            }

            function render() {
                renderCalendar();
                renderDateSummary();
                renderTypeCards();
                renderTimePanel();
                renderPrices();
                renderGuests();
                renderRooms();
                restorePhotoSliders();
                renderTotal();
            }

            function restorePhotoSliders() {
                el.roomCards.querySelectorAll('[data-photo-track]').forEach((track) => {
                    const index = photoIndex[track.dataset.photoTrack] || 0;
                    if (index) track.scrollLeft = index * track.clientWidth;
                });
            }

            // =====================================================
            // AKSI
            // =====================================================
            function selectDate(date) {
                state.selectedDate = date;
                state.hour = null;
                ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                refreshAvailability();
            }

            async function refreshAvailability() {
                if (!state.selectedDate) return;

                const requestId = ++state.availabilityRequestId;
                const params = new URLSearchParams({
                    date: dateKey(state.selectedDate),
                    booking_type: state.bookingType === 'full' ? 'Full Day' : 'Short Time',
                    duration_hours: state.bookingType === 'full' ? '24' : String(state.duration),
                });
                const hour = state.bookingType === 'full' ? 14 : state.hour;
                if (hour !== null) params.set('check_in_time', `${pad(hour)}:00`);

                state.availabilityError = null;
                state.loading = true;
                try {
                    const response = await fetch(`${AVAILABILITY_URL}?${params}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const result = await response.json();
                    if (requestId !== state.availabilityRequestId) return;

                    if (!response.ok) {
                        throw new Error(result.message || 'Ketersediaan kamar gagal dimuat.');
                    }

                    state.availability = result;
                } catch (error) {
                    if (requestId !== state.availabilityRequestId) return;
                    state.availabilityError = error.message || 'Ketersediaan kamar gagal dimuat.';
                }

                state.loading = false;
                render();
            }

            // Gulir halus ke langkah berikutnya bila belum terlihat di layar
            const guided = new Set(); // cukup sekali per langkah, supaya tidak meloncat terus saat ganti tanggal/jam
            function guideTo(target) {
                if (!target || guided.has(target.id)) return;
                guided.add(target.id);
                const top = target.getBoundingClientRect().top;
                if (top > window.innerHeight * 0.75) {
                    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
                }
            }

            el.prevMonth.addEventListener('click', () => {
                const d = new Date(state.viewYear, state.viewMonth - 1, 1);
                state.viewYear = d.getFullYear();
                state.viewMonth = d.getMonth();
                render();
            });

            el.nextMonth.addEventListener('click', () => {
                const d = new Date(state.viewYear, state.viewMonth + 1, 1);
                state.viewYear = d.getFullYear();
                state.viewMonth = d.getMonth();
                render();
            });

            el.monthSelect.addEventListener('change', (e) => {
                const [y, m] = e.target.value.split('-').map(Number);
                state.viewYear = y;
                state.viewMonth = m;
                render();
            });

            el.calendarGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-date]');
                if (!btn) return;
                const [y, m, d] = btn.dataset.date.split('-').map(Number);
                selectDate(new Date(y, m - 1, d));
                render();
                guideTo(el.typeHeading);
            });

            document.querySelectorAll('.type-card').forEach((card) => {
                card.addEventListener('click', () => {
                    state.bookingType = card.dataset.bookingType;
                    state.hour = null;
                    ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                    refreshAvailability();
                    render();
                });
            });

            function changeNights(delta) {
                const next = Math.min(MAX_NIGHTS, Math.max(1, state.nights + delta));
                if (next === state.nights) return;
                state.nights = next;
                // Kamar perlu dicek ulang untuk rentang malam yang baru
                ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                refreshAvailability();
                render();
            }
            el.nightsMinus.addEventListener('click', () => changeNights(-1));
            el.nightsPlus.addEventListener('click', () => changeNights(1));

            document.querySelectorAll('.duration-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    state.duration = Number(btn.dataset.duration);
                    state.hour = null;
                    ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                    refreshAvailability();
                    render();
                });
            });

            el.hourGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-hour]');
                if (!btn) return;
                state.hour = Number(btn.dataset.hour);
                ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                refreshAvailability();
                render();
                guideTo(el.roomsHeading);
            });

            el.guestMinus.addEventListener('click', () => { state.guests = Math.max(1, state.guests - 1); render(); });
            el.guestPlus.addEventListener('click', () => { state.guests = Math.min(10, state.guests + 1); render(); });

            el.roomCards.addEventListener('scroll', (e) => {
                const track = e.target.closest?.('[data-photo-track]');
                if (!track) return;
                const type = track.dataset.photoTrack;
                const total = track.children.length;
                photoIndex[type] = Math.min(total - 1, Math.round(track.scrollLeft / track.clientWidth));
                const counter = el.roomCards.querySelector(`[data-photo-counter="${type}"]`);
                if (counter) counter.textContent = `${photoIndex[type] + 1} / ${total}`;
            }, true);

            el.roomCards.addEventListener('click', (e) => {
                const nav = e.target.closest('[data-photo-nav]');
                if (nav) {
                    const track = el.roomCards.querySelector(`[data-photo-track="${nav.dataset.photoType}"]`);
                    track?.scrollBy({ left: Number(nav.dataset.photoNav) * track.clientWidth, behavior: 'smooth' });
                    return;
                }

                // Handle quantity buttons
                const quantityBtn = e.target.closest('.quantity-btn');
                if (quantityBtn) {
                    const roomType = quantityBtn.dataset.roomType;
                    const action = quantityBtn.dataset.action;
                    const availableRooms = state.availability?.data.filter(r => r.room_type === roomType && r.is_available) || [];
                    const maxQuantity = availableRooms.length;
                    
                    if (action === 'increase') {
                        state.quantities[roomType] = Math.min(maxQuantity, (state.quantities[roomType] || 0) + 1);
                    } else if (action === 'decrease') {
                        state.quantities[roomType] = Math.max(0, (state.quantities[roomType] || 0) - 1);
                    }
                    render();
                    return;
                }
            });

            // Lanjut ke halaman Isi Data Diri.
            el.submitButton.addEventListener('click', () => {
                if (getMissingStep()) return;

                const params = new URLSearchParams();
                params.set('check_in_date', dateKey(state.selectedDate));
                params.set('check_in_time', `${pad(checkInHour())}:00`);
                params.set('booking_type', state.bookingType === 'full' ? 'Full Day' : `Short Time ${state.duration} Jam`);
                params.set('duration_hours', String(durationHours()));
                params.set('guests', String(state.guests));
                
                // Pass quantities per room type
                const quantitiesArray = ROOM_TYPES
                    .filter(rt => (state.quantities[rt.type] || 0) > 0)
                    .map(rt => `${rt.type}:${state.quantities[rt.type]}`)
                    .join('|');
                params.set('room_quantities', quantitiesArray);

                window.location.href = `${CHECKOUT_URL}?${params.toString()}`;
            });

            // =====================================================
            // MULAI: pilih otomatis tanggal terdekat yang tidak penuh
            // =====================================================
            renderMonthSelect();

            // Jika kembali dari halaman Isi Data Diri, pulihkan pilihan sebelumnya
            // Nama baru (dari tombol kembali) dengan cadangan nama lama
            const prev = new URLSearchParams(window.location.search);
            const prevParam = (baru, lama) => prev.get(baru) ?? prev.get(lama);
            const prevDate = prevParam('tanggal', 'check_in_date');
            if (prevDate && /^\d{4}-\d{2}-\d{2}$/.test(prevDate)) {
                const [py, pm, pd] = prevDate.split('-').map(Number);
                const d = new Date(py, pm - 1, pd);
                if (getDayInfo(d).status !== 'past' && getDayInfo(d).status !== 'full') {
                    const type = prevParam('tipe_pemesanan', 'booking_type') || '';
                    state.bookingType = type === 'Full Day' ? 'full' : 'short';
                    if (type === 'Short Time 8 Jam') state.duration = 8;
                    if (state.bookingType === 'full') {
                        state.nights = Math.min(MAX_NIGHTS, Math.max(1, Math.round((parseInt(prevParam('durasi_jam', 'duration_hours') || '24', 10)) / 24)));
                    }
                    state.guests = Math.max(1, parseInt(prev.get('guests') || '1', 10));
                    const hour = parseInt((prevParam('jam_checkin', 'check_in_time') || '').split(':')[0], 10);
                    if (state.bookingType === 'short' && !isNaN(hour) && isHourAvailable(d, hour, state.duration)) {
                        state.hour = hour;
                    }
                    state.selectedDate = d;
                    
                    // Restore quantities from room_quantities parameter
                    const quantitiesParam = prev.get('room_quantities');
                    if (quantitiesParam) {
                        quantitiesParam.split('|').forEach(item => {
                            const [roomType, quantity] = item.split(':');
                            if (roomType && quantity) {
                                state.quantities[roomType] = parseInt(quantity, 10);
                            }
                        });
                    }
                    
                    refreshAvailability();
                    state.viewYear = d.getFullYear();
                    state.viewMonth = d.getMonth();
                }
            }

            for (let i = 0; !state.selectedDate && i < 60; i++) {
                const d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
                if (getDayInfo(d).status !== 'full') {
                    selectDate(d);
                    state.viewYear = d.getFullYear();
                    state.viewMonth = d.getMonth();
                    break;
                }
            }
            render();
        })();
    </script>
@endpush