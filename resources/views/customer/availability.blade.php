@extends('layouts.booking')

@section('title', 'Cek Ketersediaan')
@section('step', '1')

@php
    // ================= DATA TIPE KAMAR (SEMENTARA STATIS) =================
    // Nanti diganti data dari database (tabel rooms) oleh tim backend.
    // Harga sesuai PRD (BR-04).
    $fasilitasKamar = ['Kamar Mandi Dalam', 'Air Panas', 'AC', 'Wi-Fi Gratis', 'Smart TV'];

    $roomTypes = [
        [
            'type' => 'King',
            'name' => 'Kamar King Bed',
            'short_name' => 'King Bed',
            'description' => 'Tempat tidur King ukuran besar · cocok untuk 1–2 orang',
            'capacity' => 2,
            'total_rooms' => 5,
            'prices' => ['5' => 150000, '8' => 200000, 'full' => 350000],
            'facilities' => $fasilitasKamar,
            'image' => file_exists(public_path('images/kamar-king.jpg')) ? asset('images/kamar-king.jpg') : null,
        ],
        [
            'type' => 'Twin',
            'name' => 'Kamar Twin Bed',
            'short_name' => 'Twin Bed',
            'description' => 'Dua tempat tidur terpisah · cocok untuk 1–2 orang',
            'capacity' => 2,
            'total_rooms' => 3,
            'prices' => ['5' => 130000, '8' => 175000, 'full' => 300000],
            'facilities' => $fasilitasKamar,
            'image' => file_exists(public_path('images/kamar-twin.jpg')) ? asset('images/kamar-twin.jpg') : null,
        ],
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

                    <p id="fullDayTimeInfo" class="hidden mt-3 text-gray-600">
                        Untuk Full Day, check-in mulai pukul <strong class="text-gray-900">14:00 WIB</strong> dan check-out pukul <strong class="text-gray-900">12:00 WIB</strong> keesokan harinya.
                    </p>

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
            <h2 class="mt-10 text-lg font-semibold text-gray-900">Pilih Kamar <span class="font-normal text-gray-500">(bisa lebih dari satu)</span></h2>
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
            const CHECKOUT_URL = @json(route('customer.checkout'));

            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const MONTHS_AHEAD = 5; // bisa pesan sampai 5 bulan ke depan

            const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
            const pad = (n) => String(n).padStart(2, '0');
            const dateKey = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            // =====================================================
            // DATA DUMMY KETERSEDIAAN
            // TODO (backend): ganti dengan data dari endpoint ketersediaan,
            // misalnya GET /customer/availability?date=YYYY-MM-DD
            // =====================================================
            function hash(text) {
                let h = 2166136261;
                for (let i = 0; i < text.length; i++) {
                    h ^= text.charCodeAt(i);
                    h = Math.imul(h, 16777619);
                }
                return h >>> 0;
            }

            const totalOf = (type) => ROOM_TYPES.find((r) => r.type === type).total_rooms;

            function getDayInfo(date) {
                if (date < today) return { status: 'past', remaining: {} };

                const r = hash(dateKey(date)) % 100;
                if (r < 12) {
                    return { status: 'full', remaining: { King: 0, Twin: 0 } };
                }
                if (r < 35) {
                    let king = r % 3 === 0 ? 0 : 1;
                    const twin = r % 2 === 0 ? 1 : 0;
                    if (king + twin === 0) king = 1;
                    return { status: 'almost', remaining: { King: king, Twin: twin } };
                }
                return {
                    status: 'available',
                    remaining: {
                        King: Math.min(totalOf('King'), 3 + (r % 3)),
                        Twin: Math.min(totalOf('Twin'), 1 + ((r >> 1) % 3)),
                    },
                };
            }

            function isHourAvailable(date, hour, duration) {
                const sameDay = dateKey(date) === dateKey(today);
                if (sameDay && hour <= now.getHours()) return false;
                return hash(`${dateKey(date)}-${hour}-${duration}`) % 100 >= 18;
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
                hour: null,
                guests: 1,
                quantities: Object.fromEntries(ROOM_TYPES.map((r) => [r.type, 0])),
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
            const durationText = () => (state.bookingType === 'full' ? '1 malam' : `${state.duration} jam`);
            const checkInHour = () => (state.bookingType === 'full' ? 14 : state.hour);

            function getCheckOut() {
                const hour = checkInHour();
                if (!state.selectedDate || hour === null) return null;
                const start = new Date(state.selectedDate);
                start.setHours(hour, 0, 0, 0);
                const end = new Date(start);
                if (state.bookingType === 'full') {
                    end.setDate(end.getDate() + 1);
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
            const totalPrice = () => ROOM_TYPES.reduce((sum, r) => sum + r.prices[priceKey()] * state.quantities[r.type], 0);

            function statusLabel(status) {
                return { available: 'Tersedia', almost: 'Terbatas', full: 'Penuh' }[status] || '-';
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

                    const label = info.status === 'almost' ? 'Hampir Penuh' : 'Tersedia';
                    let color = info.status === 'almost' ? 'bg-gray-100 hover:bg-gray-200 text-gray-900' : 'bg-white hover:bg-gray-50 text-gray-900';
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

                el.roomCards.innerHTML = ROOM_TYPES.map((room) => {
                    const remaining = info.remaining[room.type] ?? 0;
                    const qty = state.quantities[room.type];
                    const selected = qty > 0;
                    const soldOut = remaining === 0;

                    let description = room.description;
                    if (soldOut) description = 'Tidak tersedia pada tanggal ini';
                    else if (remaining <= 2) description = `${room.description.split('·')[0].trim()} · hanya ${remaining} kamar tersedia`;

                    const photo = room.image
                        ? `<img src="${room.image}" alt="${room.name}" class="w-full h-full object-cover">`
                        : `<span class="flex flex-col items-center justify-center gap-2 h-full text-gray-400">
                               <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg>
                               <span class="text-sm tracking-wider">FOTO</span>
                           </span>`;

                    return `
                    <div class="border-2 rounded-lg p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:gap-5 transition ${selected ? 'border-gray-900' : 'border-gray-200'} ${soldOut ? 'opacity-60' : ''}">
                        <div class="w-full sm:w-36 h-32 sm:h-28 shrink-0 bg-gray-100 border border-gray-200 rounded-md overflow-hidden">${photo}</div>
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
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400">Sisa: <strong class="text-gray-600">${remaining}</strong></span>
                                    <button type="button" data-room="${room.type}" data-action="minus" ${qty <= 0 ? 'disabled' : ''}
                                        class="w-10 h-10 border border-gray-300 rounded-md text-lg hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Kurangi ${room.name}">&minus;</button>
                                    <span class="w-6 text-center text-lg font-semibold">${qty}</span>
                                    <button type="button" data-room="${room.type}" data-action="plus" ${qty >= remaining ? 'disabled' : ''}
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
                if (state.guests > totalCapacity()) return 'Jumlah tamu melebihi kapasitas kamar.';
                return null;
            }

            function renderTotal() {
                const rooms = selectedRooms();
                el.totalPrice.textContent = rupiah(totalPrice());
                el.totalDetail.textContent = rooms.length
                    ? rooms.map((r) => `${state.quantities[r.type]}× ${r.short_name}`).join(', ') + ` · ${durationText()}`
                    : 'Belum ada kamar dipilih';

                const missing = getMissingStep();
                el.submitButton.disabled = !!missing;
                el.submitButton.className = 'mt-6 w-full py-4 rounded-lg text-lg font-semibold transition ' +
                    (missing ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-900 hover:bg-gray-800 text-white');
                el.submitHint.textContent = missing || '';
            }

            function render() {
                renderCalendar();
                renderDateSummary();
                renderTypeCards();
                renderTimePanel();
                renderPrices();
                renderGuests();
                renderRooms();
                renderTotal();
            }

            // =====================================================
            // AKSI
            // =====================================================
            function selectDate(date) {
                state.selectedDate = date;
                state.hour = null;
                // Sesuaikan jumlah kamar dengan sisa kamar di tanggal baru
                const info = getDayInfo(date);
                ROOM_TYPES.forEach((r) => {
                    state.quantities[r.type] = Math.min(state.quantities[r.type], info.remaining[r.type] ?? 0);
                });
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
                    render();
                });
            });

            document.querySelectorAll('.duration-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    state.duration = Number(btn.dataset.duration);
                    state.hour = null;
                    render();
                });
            });

            el.hourGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-hour]');
                if (!btn) return;
                state.hour = Number(btn.dataset.hour);
                render();
            });

            el.guestMinus.addEventListener('click', () => { state.guests = Math.max(1, state.guests - 1); render(); });
            el.guestPlus.addEventListener('click', () => { state.guests = Math.min(10, state.guests + 1); render(); });

            el.roomCards.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-room]');
                if (!btn || btn.disabled) return;
                const type = btn.dataset.room;
                state.quantities[type] += btn.dataset.action === 'plus' ? 1 : -1;
                state.quantities[type] = Math.max(0, state.quantities[type]);
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
                params.set('duration_hours', state.bookingType === 'full' ? '24' : String(state.duration));
                params.set('guests', String(state.guests));
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
                    state.guests = Math.max(1, parseInt(prev.get('guests') || '1', 10));
                    selectDate(d);
                    const info = getDayInfo(d);
                    ROOM_TYPES.forEach((r) => {
                        const q = parseInt(prev.get(`rooms[${r.type}]`) || '0', 10) || 0;
                        state.quantities[r.type] = Math.min(q, info.remaining[r.type] ?? 0);
                    });
                    const hour = parseInt((prev.get('check_in_time') || '').split(':')[0], 10);
                    if (state.bookingType === 'short' && !isNaN(hour) && isHourAvailable(d, hour, state.duration)) state.hour = hour;
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