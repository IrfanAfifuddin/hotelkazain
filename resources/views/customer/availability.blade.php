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
        <div class="flex items-start gap-3 border border-dashed border-gray-300 bg-gray-50 rounded-lg p-5 text-gray-700 leading-relaxed">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
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
            <p class="text-sm font-medium tracking-[0.3em] text-gray-500">LANGKAH 1</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900">Cek Ketersediaan Kamar</h1>
            <p class="mt-2 text-gray-600">Pilih tanggal, tipe pemesanan, dan jam check-in yang tersedia.</p>
        </div>

        {{-- ===================== KALENDER ===================== --}}
        <div class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 bg-gray-50 border-b border-gray-200">
                <button type="button" id="prevMonth" class="w-10 h-10 border border-gray-300 rounded-md bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Bulan sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" /></svg>
                </button>

                <label class="relative flex items-center gap-2 font-semibold text-gray-900 text-lg cursor-pointer">
                    <span id="monthLabel">-</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" /></svg>
                    <select id="monthSelect" class="absolute inset-0 opacity-0 cursor-pointer" aria-label="Pilih bulan"></select>
                </label>

                <button type="button" id="nextMonth" class="w-10 h-10 border border-gray-300 rounded-md bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Bulan berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" /></svg>
                </button>
            </div>

            <div class="grid grid-cols-7 border-b border-gray-200 text-center text-xs sm:text-sm font-medium text-gray-400">
                @foreach (['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'] as $hari)
                    <div class="py-3">{{ $hari }}</div>
                @endforeach
            </div>

            <div id="calendarGrid" class="grid grid-cols-7 gap-px bg-gray-200"></div>
        </div>

        {{-- Legenda kalender --}}
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
            <span class="flex items-center gap-2"><span class="w-5 h-5 rounded border border-gray-300 bg-white"></span>Tersedia</span>
            <span class="flex items-center gap-2"><span class="w-5 h-5 rounded border border-gray-300 bg-gray-100"></span>Hampir Penuh</span>
            <span class="flex items-center gap-2"><span class="w-5 h-5 rounded border border-gray-300 day-full"></span>Penuh</span>
            <span class="flex items-center gap-2"><span class="w-5 h-5 rounded border border-gray-200 bg-gray-50"></span>Sudah Lewat</span>
            <span class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-gray-900"></span>Dipilih</span>
        </div>

        {{-- Belum pilih tanggal --}}
        <p id="noDateHint" class="mt-8 text-center text-gray-500">Pilih tanggal pada kalender untuk melanjutkan.</p>

        {{-- ===================== SETELAH PILIH TANGGAL ===================== --}}
        <div id="afterDate" class="hidden">

            {{-- Ringkasan tanggal --}}
            <div class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect width="18" height="18" x="3" y="4" rx="2" /><path d="M16 2v4" /><path d="M8 2v4" /><path d="M3 10h18" />
                    </svg>
                    <p class="font-semibold text-gray-900">Tanggal dipilih: <span id="selectedDateLabel">-</span></p>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-gray-700">Short Time</span>
                        <span id="shortStatus" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-semibold">-</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-gray-700">Full Day</span>
                        <span id="fullStatus" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-semibold">-</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4 bg-gray-50">
                        <span class="text-gray-600">Sisa kamar tersedia</span>
                        <span class="font-bold text-gray-900"><span id="totalRemaining">0</span> kamar</span>
                    </div>
                </div>
            </div>

            {{-- Pilih tipe pemesanan --}}
            <h2 class="mt-10 text-lg font-semibold text-gray-900">Pilih Tipe Pemesanan</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <button type="button" data-booking-type="short" class="type-card text-left border-2 rounded-lg p-5 transition">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center gap-3 font-semibold text-gray-900 text-lg">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                            Short Time
                        </span>
                        <svg class="type-check w-5 h-5 text-gray-900 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                    </span>
                    <span class="mt-2 block text-gray-600">Sewa per sesi · 5 jam atau 8 jam</span>
                </button>

                <button type="button" data-booking-type="full" class="type-card text-left border-2 rounded-lg p-5 transition">
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
            <div class="mt-6 border border-gray-200 rounded-lg bg-gray-50 divide-y divide-gray-200">

                {{-- Durasi (khusus Short Time) --}}
                <div id="durationSection" class="p-6">
                    <p class="text-sm font-medium tracking-wider text-gray-400 uppercase">Pilih Durasi</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button type="button" data-duration="5" class="duration-btn py-3 rounded-md border text-lg font-medium transition">5 Jam</button>
                        <button type="button" data-duration="8" class="duration-btn py-3 rounded-md border text-lg font-medium transition">8 Jam</button>
                    </div>
                </div>

                {{-- Jam check-in --}}
                <div class="p-6">
                    <p class="text-sm font-medium tracking-wider text-gray-400 uppercase">Pilih Jam Check-in · <span id="timeDateLabel">-</span></p>

                    <div id="hourGrid" class="mt-3 grid grid-cols-4 sm:grid-cols-6 gap-2"></div>

                    <div id="fullDayTimeInfo" class="hidden">
                        <p class="mt-3 text-gray-600">
                            Untuk Full Day, check-in mulai pukul <strong class="text-gray-900">14:00 WIB</strong> dan check-out pukul <strong class="text-gray-900">12:00 WIB</strong> setelah malam terakhir.
                        </p>

                        {{-- Jumlah malam --}}
                        <p class="mt-5 text-sm font-medium tracking-wider text-gray-400 uppercase">Jumlah Malam</p>
                        <div class="mt-3 flex items-center gap-4">
                            <button type="button" id="nightsMinus" class="w-12 h-12 bg-white border border-gray-300 rounded-md text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Kurangi malam">&minus;</button>
                            <span id="nightsCount" class="w-8 text-center text-2xl font-semibold">1</span>
                            <button type="button" id="nightsPlus" class="w-12 h-12 bg-white border border-gray-300 rounded-md text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Tambah malam">+</button>
                            <span class="text-gray-600">malam</span>
                        </div>
                    </div>

                    <div id="hourLegend" class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border border-gray-300 bg-white"></span>Tersedia</span>
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border border-gray-200 bg-gray-100"></span>Penuh</span>
                        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-gray-900"></span>Dipilih</span>
                    </div>

                    <div id="timeSummary" class="hidden mt-4 flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-md text-gray-700">
                        <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                        <span>Check-in <strong id="checkInLabel" class="text-gray-900">-</strong> · Check-out <strong id="checkOutLabel" class="text-gray-900">-</strong></span>
                    </div>
                </div>

                {{-- Harga kamar --}}
                <div class="p-6">
                    <p class="text-sm font-medium tracking-wider text-gray-400 uppercase">Harga Kamar</p>
                    <div id="priceCards" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            {{-- Jumlah tamu --}}
            <div class="mt-6 border border-gray-200 rounded-lg p-6">
                <p class="text-sm font-medium tracking-wider text-gray-400 uppercase">Jumlah Tamu</p>
                <div class="mt-3 flex items-center gap-4">
                    <button type="button" id="guestMinus" class="w-12 h-12 border border-gray-300 rounded-md text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Kurangi tamu">&minus;</button>
                    <span id="guestCount" class="w-8 text-center text-2xl font-semibold">1</span>
                    <button type="button" id="guestPlus" class="w-12 h-12 border border-gray-300 rounded-md text-xl text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Tambah tamu">+</button>
                    <span class="text-gray-600">orang</span>
                </div>
                <p id="guestHint" class="mt-3 text-sm text-gray-500">Pilih kamar di bawah untuk melihat kapasitas</p>
            </div>

            {{-- Pilih kamar --}}
            <h2 class="mt-10 text-lg font-semibold text-gray-900">Pilih Kamar <span class="font-normal text-gray-500">(satu kamar per pemesanan)</span></h2>
            <div id="roomCards" class="mt-4 space-y-4"></div>

            {{-- Total --}}
            <div class="mt-6 border border-gray-200 rounded-lg bg-gray-50 px-6 py-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-lg text-gray-700">Total estimasi</p>
                    <p id="totalDetail" class="mt-1 text-sm text-gray-500">Belum ada kamar dipilih</p>
                </div>
                <p id="totalPrice" class="text-2xl font-bold text-gray-900">Rp 0</p>
            </div>

            <button type="button" id="submitButton" disabled
                    class="mt-6 w-full py-4 rounded-lg text-lg font-semibold transition bg-gray-100 text-gray-400 cursor-not-allowed">
                Lanjut ke Isi Data Diri &rarr;
            </button>
            <p id="submitHint" class="mt-3 text-center text-sm text-gray-500"></p>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .day-full {
            background-image: repeating-linear-gradient(135deg, #f3f4f6 0 6px, #ffffff 6px 12px);
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
                selectedRoomId: null,
                availabilityError: null,
                availabilityRequestId: 0,
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
                for (let i = 0; i < firstDay; i++) {
                    html += '<div class="bg-gray-50 h-20 sm:h-24"></div>';
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(viewYear, viewMonth, day);
                    const info = getDayInfo(date);
                    const isSelected = state.selectedDate && dateKey(state.selectedDate) === dateKey(date);
                    const base = 'h-20 sm:h-24 p-2 sm:p-3 text-left flex flex-col justify-between';

                    if (info.status === 'past') {
                        html += `<div class="${base} bg-gray-50 text-gray-300"><span class="text-sm sm:text-base">${day}</span></div>`;
                        continue;
                    }
                    if (info.status === 'full') {
                        html += `<div class="${base} day-full text-gray-400 cursor-not-allowed" aria-label="${day} penuh">
                                    <span class="text-sm sm:text-base font-semibold">${day}</span>
                                    <span class="text-[10px] sm:text-xs line-through">Penuh</span>
                                 </div>`;
                        continue;
                    }

                    const label = info.status === 'almost'
                        ? 'Hampir Penuh'
                        : info.status === 'unknown' ? 'Cek ketersediaan' : 'Tersedia';
                    let color = info.status === 'almost'
                        ? 'bg-gray-100 hover:bg-gray-200 text-gray-900'
                        : 'bg-white hover:bg-gray-50 text-gray-900';
                    if (isSelected) color = 'bg-gray-900 text-white';

                    html += `<button type="button" data-date="${dateKey(date)}" class="${base} ${color} transition" aria-pressed="${isSelected}">
                                <span class="text-sm sm:text-base font-semibold">${day}</span>
                                <span class="text-[10px] sm:text-xs ${isSelected ? 'text-gray-200' : 'text-gray-500'} leading-tight">${label}</span>
                             </button>`;
                }

                const trailing = (7 - ((firstDay + daysInMonth) % 7)) % 7;
                for (let i = 0; i < trailing; i++) {
                    html += '<div class="bg-gray-50 h-20 sm:h-24"></div>';
                }

                el.calendarGrid.innerHTML = html;
            }

            function renderDateSummary() {
                const hasDate = !!state.selectedDate;
                el.afterDate.classList.toggle('hidden', !hasDate);
                el.noDateHint.classList.toggle('hidden', hasDate);
                if (!hasDate) return;

                const d = state.selectedDate;
                const info = getDayInfo(d);
                const label = `${DAYS[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;

                el.selectedDateLabel.textContent = label;
                el.timeDateLabel.textContent = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
                el.shortStatus.textContent = statusLabel(info.status);
                el.fullStatus.textContent = statusLabel(info.status);
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
                    btn.className = 'duration-btn py-3 rounded-md border text-lg font-medium transition ' +
                        (active ? 'bg-gray-900 border-gray-900 text-white' : 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50');
                });

                if (!isFull && state.selectedDate) {
                    let html = '';
                    for (let h = 0; h < 24; h++) {
                        const available = isHourAvailable(state.selectedDate, h, state.duration);
                        const selected = state.hour === h;
                        const label = `${pad(h)}:00`;
                        if (!available) {
                            html += `<button type="button" disabled class="py-2.5 rounded-md border border-gray-100 bg-gray-100 text-gray-300 line-through cursor-not-allowed">${label}</button>`;
                        } else {
                            html += `<button type="button" data-hour="${h}" class="py-2.5 rounded-md border transition ${selected ? 'bg-gray-900 border-gray-900 text-white' : 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50'}">${label}</button>`;
                        }
                    }
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
                    <div class="bg-white border border-gray-200 rounded-md p-5">
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

                const capacity = totalCapacity();
                if (totalRoomsSelected() === 0) {
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
                const selectedRoom = state.availability?.data.find((room) => room.room_id === state.selectedRoomId);

                el.roomCards.innerHTML = ROOM_TYPES.map((room) => {
                    const remaining = info.remaining[room.type] ?? 0;
                    const qty = state.quantities[room.type];
                    const selected = qty > 0;
                    const availableRooms = state.availability?.date === dateKey(state.selectedDate)
                        ? state.availability.data.filter((availableRoom) => availableRoom.room_type === room.type && availableRoom.is_available)
                        : [];
                    const soldOut = !availableRooms.length;

                    let description = room.description;
                    if (soldOut) description = 'Tidak tersedia pada tanggal ini';
                    else if (remaining <= 2) description = `${room.description.split('·')[0].trim()} · hanya ${remaining} kamar tersedia`;

                    const photos = ROOM_PHOTOS[room.type]?.length ? ROOM_PHOTOS[room.type] : (room.image ? [room.image] : []);
                    const photo = photos.length
                        ? `<div class="flex h-full overflow-x-auto snap-x snap-mandatory no-scrollbar" data-photo-track="${room.type}">
                               ${photos.map((src, i) => `<img src="${src}" alt="${room.name} – foto ${i + 1}" class="w-full h-full shrink-0 snap-center object-cover" loading="lazy">`).join('')}
                           </div>
                           ${photos.length > 1 ? `
                           <button type="button" data-photo-nav="-1" data-photo-type="${room.type}" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white flex items-center justify-center" aria-label="Foto sebelumnya">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                           </button>
                           <button type="button" data-photo-nav="1" data-photo-type="${room.type}" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white flex items-center justify-center" aria-label="Foto berikutnya">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                           </button>
                           <span class="absolute left-2 bottom-2 px-2 py-0.5 rounded bg-black/55 text-white text-xs font-semibold tabular-nums" data-photo-counter="${room.type}">${(photoIndex[room.type] || 0) + 1} / ${photos.length}</span>` : ''}`
                        : `<span class="flex flex-col items-center justify-center gap-2 h-full text-gray-400">
                               <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg>
                               <span class="text-sm tracking-wider">FOTO</span>
                           </span>`;

                    return `
                    <div class="border-2 rounded-lg p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:gap-5 transition ${selected ? 'border-gray-900' : 'border-gray-200'} ${soldOut ? 'opacity-60' : ''}">
                        <div class="relative w-full sm:w-72 h-52 sm:h-48 shrink-0 bg-gray-100 border border-gray-200 rounded-md overflow-hidden">${photo}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">${room.name}</p>
                                    <p class="text-gray-600">${description}</p>
                                </div>
                                ${selected ? `<span class="shrink-0 px-2.5 py-1 bg-gray-900 text-white text-xs font-semibold rounded">${qty} dipilih</span>` : ''}
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                ${room.facilities.map((f) => `<span class="px-2.5 py-1 bg-gray-50 border border-gray-200 rounded text-xs text-gray-600">${f}</span>`).join('')}
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <p class="text-gray-400">Harga / ${unit}</p>
                                    <p class="text-xl font-bold text-gray-900">${rupiah(room.prices[priceKey()])}</p>
                                    ${selected && selectedRoom ? `<p class="mt-1 text-sm font-medium text-gray-700">Kamar ${selectedRoom.room_number}</p>` : ''}
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400">Sisa: <strong class="text-gray-600">${remaining}</strong></span>
                                    <button type="button" data-room="${room.type}" data-action="minus" ${qty <= 0 ? 'disabled' : ''}
                                        class="w-10 h-10 border border-gray-300 rounded-md text-lg hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Kurangi ${room.name}">&minus;</button>
                                    <span class="w-6 text-center text-lg font-semibold">${qty}</span>
                                    <button type="button" data-room="${room.type}" data-action="plus" ${soldOut || (totalRoomsSelected() > 0 && !selected) || (state.bookingType === 'short' && state.hour === null) ? 'disabled' : ''}
                                        class="w-10 h-10 border border-gray-300 rounded-md text-lg hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Tambah ${room.name}">+</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            }

            function getMissingStep() {
                if (!state.selectedDate) return 'Pilih tanggal terlebih dahulu.';
                if (state.bookingType === 'short' && state.hour === null) return 'Pilih jam check-in.';
                if (totalRoomsSelected() === 0) return 'Pilih minimal satu kamar.';
                if (!state.selectedRoomId) return 'Pilih kamar yang tersedia.';
                if (state.availabilityError) return state.availabilityError;
                if (state.guests > totalCapacity()) return 'Jumlah tamu melebihi kapasitas kamar.';
                return null;
            }

            function renderTotal() {
                const rooms = selectedRooms();
                el.totalPrice.textContent = rupiah(totalPrice());
                el.totalDetail.textContent = rooms.length
                    ? `${rooms[0].short_name}${state.selectedRoomId ? ` (Kamar ${state.availability.data.find((room) => room.room_id === state.selectedRoomId)?.room_number ?? ''})` : ''} · ${durationText()}`
                    : 'Belum ada kamar dipilih';

                const missing = getMissingStep();
                el.submitButton.disabled = !!missing;
                el.submitButton.className = 'mt-6 w-full py-4 rounded-lg text-lg font-semibold transition ' +
                    (missing ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-900 hover:bg-gray-800 text-white');
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
                state.selectedRoomId = null;
                ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                refreshAvailability();
            }

            async function refreshAvailability() {
                if (!state.selectedDate) return;

                const requestId = ++state.availabilityRequestId;
                const params = new URLSearchParams({
                    date: dateKey(state.selectedDate),
                    booking_type: state.bookingType === 'full' ? 'Full Day' : 'Short Time',
                    // Endpoint ketersediaan backend hanya menerima 5, 8, atau 24 jam.
                    // Untuk Full Day lebih dari 1 malam, cek malam pertama di sini;
                    // seluruh malam dicek ulang oleh backend saat pesanan disimpan.
                    duration_hours: state.bookingType === 'full' ? '24' : String(state.duration),
                });
                const hour = state.bookingType === 'full' ? 14 : state.hour;
                if (hour !== null) params.set('check_in_time', `${pad(hour)}:00`);

                state.availabilityError = null;
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
                    if (state.selectedRoomId && !result.data.some((room) => room.room_id === state.selectedRoomId && room.is_available)) {
                        state.selectedRoomId = null;
                        ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                    }
                } catch (error) {
                    if (requestId !== state.availabilityRequestId) return;
                    state.availabilityError = error.message || 'Ketersediaan kamar gagal dimuat.';
                }

                render();
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
            });

            document.querySelectorAll('.type-card').forEach((card) => {
                card.addEventListener('click', () => {
                    state.bookingType = card.dataset.bookingType;
                    state.hour = null;
                    state.selectedRoomId = null;
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
                state.selectedRoomId = null;
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
                    state.selectedRoomId = null;
                    ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                    refreshAvailability();
                    render();
                });
            });

            el.hourGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-hour]');
                if (!btn) return;
                state.hour = Number(btn.dataset.hour);
                state.selectedRoomId = null;
                ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                refreshAvailability();
                render();
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

                const btn = e.target.closest('[data-room]');
                if (!btn || btn.disabled) return;
                const type = btn.dataset.room;
                if (btn.dataset.action === 'plus') {
                    ROOM_TYPES.forEach((room) => { state.quantities[room.type] = 0; });
                    state.quantities[type] = 1;
                    state.selectedRoomId = state.availability.data.find((room) => room.room_type === type && room.is_available)?.room_id ?? null;
                } else {
                    state.quantities[type] = 0;
                    state.selectedRoomId = null;
                }
                render();
            });

            // Lanjut ke halaman Isi Data Diri.
            // Nama parameter mengikuti kontrak di tests/Feature/CustomerBookingTest.php
            el.submitButton.addEventListener('click', () => {
                if (getMissingStep()) return;

                const params = new URLSearchParams();
                params.set('check_in_date', dateKey(state.selectedDate));
                params.set('check_in_time', `${pad(checkInHour())}:00`);
                params.set('booking_type', state.bookingType === 'full' ? 'Full Day' : `Short Time ${state.duration} Jam`);
                params.set('duration_hours', String(durationHours()));
                params.set('guests', String(state.guests));
                params.set('room_id', String(state.selectedRoomId));
                selectedRooms().forEach((r) => params.set(`rooms[${r.type}]`, String(state.quantities[r.type])));

                window.location.href = `${CHECKOUT_URL}?${params.toString()}`;
            });

            // =====================================================
            // MULAI: pilih otomatis tanggal terdekat yang tidak penuh
            // =====================================================
            renderMonthSelect();

            // Jika kembali dari halaman Isi Data Diri, pulihkan pilihan sebelumnya
            const prev = new URLSearchParams(window.location.search);
            const prevDate = prev.get('check_in_date');
            if (prevDate && /^\d{4}-\d{2}-\d{2}$/.test(prevDate)) {
                const [py, pm, pd] = prevDate.split('-').map(Number);
                const d = new Date(py, pm - 1, pd);
                if (getDayInfo(d).status !== 'past' && getDayInfo(d).status !== 'full') {
                    const type = prev.get('booking_type') || '';
                    state.bookingType = type === 'Full Day' ? 'full' : 'short';
                    if (type === 'Short Time 8 Jam') state.duration = 8;
                    if (state.bookingType === 'full') {
                        state.nights = Math.min(MAX_NIGHTS, Math.max(1, Math.round((parseInt(prev.get('duration_hours') || '24', 10)) / 24)));
                    }
                    state.guests = Math.max(1, parseInt(prev.get('guests') || '1', 10));
                    const hour = parseInt((prev.get('check_in_time') || '').split(':')[0], 10);
                    if (state.bookingType === 'short' && !isNaN(hour) && isHourAvailable(d, hour, state.duration)) {
                        state.hour = hour;
                    }
                    state.selectedDate = d;
                    state.selectedRoomId = Number(prev.get('room_id')) || null;
                    const selectedType = ROOM_TYPES.find((room) => Number(prev.get(`rooms[${room.type}]`)) > 0);
                    if (selectedType && state.selectedRoomId) state.quantities[selectedType.type] = 1;
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