@extends('layouts.app')

@section('title', 'Data Pemesanan')

@section('content')

    {{-- Header Halaman --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Pemesanan</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola data reservasi kamar hotel (Online & Offline).</p>
    </div>

    {{-- Alert Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- 1. KOMPONEN KALENDER PEMESANAN --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-6 mb-8">
        {{-- Kalender Header & Legenda --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-bold text-gray-900 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span>Kalender Pemesanan Kamar</span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau okupansi kamar dan jadwal reservasi per tanggal.</p>
            </div>

            {{-- Legenda Indikator Titik Pemesanan --}}
            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 bg-gray-50 px-3.5 py-2 rounded-xl border border-gray-200">
                <span class="font-semibold text-gray-700 mr-1">Legenda:</span>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block shadow-xs"></span>
                    <span>1 Pemesanan</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block shadow-xs"></span>
                    <span>2 Pemesanan</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block shadow-xs"></span>
                    <span>&ge; 3 Pemesanan</span>
                </div>
            </div>
        </div>

        {{-- Kalender Grid & Detail Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-5">

            {{-- SISI KIRI: Kalender Bulanan (7 Kolom) --}}
            <div class="lg:col-span-7 flex flex-col justify-between">
                <div>
                    {{-- Navigasi Bulan & Tahun --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <h3 id="currentMonthYearLabel" class="text-base font-bold text-gray-900 tracking-tight">
                                Bulan Tahun
                            </h3>
                            <button
                                type="button"
                                onclick="goToToday()"
                                class="text-xs font-semibold px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition"
                            >
                                Hari Ini
                            </button>
                        </div>
                        <div class="flex items-center space-x-1">
                            <button
                                type="button"
                                onclick="changeMonth(-1)"
                                title="Bulan Sebelumnya"
                                class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-600 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                onclick="changeMonth(1)"
                                title="Bulan Berikutnya"
                                class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-600 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Nama Hari (Min - Sab) --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-500 mb-1.5">
                        <div class="py-1 text-red-500">Min</div>
                        <div class="py-1">Sen</div>
                        <div class="py-1">Sel</div>
                        <div class="py-1">Rab</div>
                        <div class="py-1">Kam</div>
                        <div class="py-1">Jum</div>
                        <div class="py-1 text-blue-600">Sab</div>
                    </div>

                    {{-- Kotak Tanggal-Tanggal Kalender --}}
                    <div id="calendarDaysGrid" class="grid grid-cols-7 gap-1.5">
                        {{-- Diisi secara dinamis via JavaScript --}}
                    </div>
                </div>

                {{-- Catatan Okupansi --}}
                <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-400 flex items-center justify-between">
                    <span>* Klik tanggal untuk melihat rincian pemesanan dan kamar terisi.</span>
                    <span id="calendarTotalMonthBookings" class="font-medium text-gray-600">0 pemesanan bulan ini</span>
                </div>
            </div>

            {{-- SISI KANAN: Detail Pemesanan pada Tanggal Terpilih (5 Kolom) --}}
            <div class="lg:col-span-5 bg-gray-50/70 border border-gray-200 rounded-xl p-4 flex flex-col h-full min-h-[380px]">
                {{-- Header Detail Tanggal --}}
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Detail Tanggal</p>
                        <h4 id="selectedDateTitle" class="text-sm font-bold text-gray-900 mt-0.5">
                            Pilih Tanggal
                        </h4>
                    </div>
                    <div>
                        <span id="selectedOccupiedBadge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                            0 Kamar Terisi
                        </span>
                    </div>
                </div>

                {{-- List Kartu Pemesanan pada Tanggal Terpilih --}}
                <div id="selectedDateBookingsList" class="flex-1 overflow-y-auto space-y-3 pr-1 max-h-[370px]">
                    {{-- Diisi secara dinamis via JavaScript --}}
                </div>
            </div>

        </div>
    </div>

    {{-- 2. TABEL DATA PEMESANAN --}}
    {{-- Search & Filter Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari kode booking, nama customer, atau nomor kamar..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                Cari
            </button>
            @if($search)
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="text-xs text-red-600 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Pemesanan --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Daftar Seluruh Pemesanan</h3>
            <span class="text-xs text-gray-500 font-medium">Total: {{ $bookings->total() }} Pemesanan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Booking Code</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Kamar</th>
                        <th class="px-6 py-3">Durasi</th>
                        <th class="px-6 py-3">Check-in</th>
                        <th class="px-6 py-3">Check-out</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Pembayaran</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $b->booking_code }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $b->customer->customer_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $b->customer->phone_number ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-medium">
                                Kamar {{ $b->room->room_number ?? '-' }}
                                <span class="text-xs text-gray-400 block">({{ $b->room->room_type ?? '-' }})</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $b->booking_type }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($b->check_in_time)->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($b->check_out_time)->format('d M Y H:i') }}</td>

                            {{-- Status Pemesanan --}}
                            <td class="px-6 py-4">
                                @php
                                    $badge = match($b->booking_status) {
                                        'Confirmed' => 'bg-green-100 text-green-800',
                                        'Check-in' => 'bg-blue-100 text-blue-800',
                                        'Check-out' => 'bg-gray-100 text-gray-800',
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $b->booking_status }}
                                </span>
                            </td>

                            {{-- Kolom Pembayaran (Metode & Status Terpisah) --}}
                            <td class="px-6 py-4">
                                @php
                                    $methodBadge = match($b->payment_method) {
                                        'Cash' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'QRIS' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Virtual Account', 'Transfer' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                                    };

                                    $paymentStatusBadge = match($b->payment_status) {
                                        'Paid' => 'bg-green-100 text-green-800',
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Failed', 'Cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $methodBadge }}">
                                        {{ $b->payment_method ?? 'Belum ada data' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $paymentStatusBadge }}">
                                        {{ $b->payment_status ?? 'Pending' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if ($b->booking_status === 'Check-in')
                                    <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.extend.create', $b) : route('receptionist.pemesanan.extend.create', $b) }}" class="inline-flex items-center text-xs font-medium text-emerald-700 hover:text-emerald-900">Extend</a>
                                @endif
                                @if (auth()->user()->role === 'Receptionist' && $b->payment_method === 'Midtrans' && $b->payment_status === 'Pending')
                                    <a href="{{ route('receptionist.pemesanan.payment.show', $b) }}" class="inline-flex items-center text-xs font-medium text-purple-700 hover:text-purple-900">Bayar</a>
                                @endif
                                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.edit', $b->id) : route('receptionist.pemesanan.edit', $b->id) }}" class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-900">
                                    Edit
                                </a>
                                <button type="button" onclick="confirmDelete({{ $b->id }}, '{{ $b->booking_code }}')" class="inline-flex items-center text-xs font-medium text-red-600 hover:text-red-900">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-gray-400">
                                Tidak ada data pemesanan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    {{-- Confirmation Modal Hapus --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-xs" onclick="closeDeleteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 relative border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Pemesanan</h3>
                <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus pemesanan <span id="deleteCodeLabel" class="font-semibold text-gray-900">--</span>?</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700">
                            Konfirmasi Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Data Pemesanan Aktif dari Backend (sudah mengecualikan Cancelled)
    const rawBookings = @json($calendarBookings ?? []);

    // State Kalender (Bulan & Tahun saat ini)
    let currentCalendarDate = new Date();
    let selectedDateString = formatToDateString(new Date());

    const monthNamesID = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const dayNamesID = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];

    // Helper: format Date object menjadi string 'YYYY-MM-DD'
    function formatToDateString(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Helper: Parse 'YYYY-MM-DD' ke Date
    function parseDateString(dateStr) {
        const [year, month, day] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    }

    /**
     * Hitung pemetaan tanggal ke daftar pemesanan aktif.
     * Aturan Bisnis: Pemesanan yang berlangsung melewati tengah malam tercakup pada seluruh tanggal interval [check_in_date, check_out_date].
     */
    function getBookingsByDateMap() {
        const map = {};

        rawBookings.forEach(booking => {
            if (!booking.check_in_date || !booking.check_out_date) return;

            let cur = parseDateString(booking.check_in_date);
            const end = parseDateString(booking.check_out_date);

            // Loop setiap hari dari check_in_date sampai check_out_date
            while (cur <= end) {
                const dateKey = formatToDateString(cur);
                if (!map[dateKey]) {
                    map[dateKey] = [];
                }

                // Hindari duplikasi dalam tanggal yang sama
                if (!map[dateKey].some(b => b.id === booking.id)) {
                    map[dateKey].push(booking);
                }

                cur.setDate(cur.getDate() + 1);
            }
        });

        return map;
    }

    // Render Kalender Grid
    function renderCalendar() {
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();

        // 1. Label Bulan & Tahun
        document.getElementById('currentMonthYearLabel').textContent = `${monthNamesID[month]} ${year}`;

        // 2. Data Pemesanan
        const bookingsMap = getBookingsByDateMap();

        // 3. Perhitungan Hari
        const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0 = Minggu
        const totalDaysInMonth = new Date(year, month + 1, 0).getDate();
        const totalDaysInPrevMonth = new Date(year, month, 0).getDate();

        const gridContainer = document.getElementById('calendarDaysGrid');
        gridContainer.innerHTML = '';

        let monthBookingsCount = 0;
        const countedBookingIds = new Set();

        const todayString = formatToDateString(new Date());

        // A. Hari dari Bulan Sebelumnya (Leading empty/faded cells)
        for (let i = firstDayOfMonth - 1; i >= 0; i--) {
            const dayNum = totalDaysInPrevMonth - i;
            const prevMonthDate = new Date(year, month - 1, dayNum);
            const dateStr = formatToDateString(prevMonthDate);
            const bookingsOnDate = bookingsMap[dateStr] || [];

            gridContainer.appendChild(createDayCell(dayNum, dateStr, bookingsOnDate, false, todayString));
        }

        // B. Hari di Bulan Aktif
        for (let dayNum = 1; dayNum <= totalDaysInMonth; dayNum++) {
            const curDate = new Date(year, month, dayNum);
            const dateStr = formatToDateString(curDate);
            const bookingsOnDate = bookingsMap[dateStr] || [];

            bookingsOnDate.forEach(b => countedBookingIds.add(b.id));

            gridContainer.appendChild(createDayCell(dayNum, dateStr, bookingsOnDate, true, todayString));
        }

        // C. Hari dari Bulan Berikutnya (Trailing cells untuk mengisi grid 35 atau 42 cell)
        const totalCellsSoFar = firstDayOfMonth + totalDaysInMonth;
        const remainingCells = totalCellsSoFar % 7 === 0 ? 0 : 7 - (totalCellsSoFar % 7);
        for (let dayNum = 1; dayNum <= remainingCells; dayNum++) {
            const nextMonthDate = new Date(year, month + 1, dayNum);
            const dateStr = formatToDateString(nextMonthDate);
            const bookingsOnDate = bookingsMap[dateStr] || [];

            gridContainer.appendChild(createDayCell(dayNum, dateStr, bookingsOnDate, false, todayString));
        }

        // Total pemesanan bulan ini
        document.getElementById('calendarTotalMonthBookings').textContent = `${countedBookingIds.size} pemesanan pada bulan ini`;

        // Render detail pemesanan pada tanggal terpilih
        renderSelectedDateDetails();
    }

    // Buat Elemen Cell Hari
    function createDayCell(dayNum, dateStr, bookingsOnDate, isCurrentMonth, todayString) {
        const cell = document.createElement('button');
        cell.type = 'button';
        cell.dataset.date = dateStr;

        const isSelected = (dateStr === selectedDateString);
        const isToday = (dateStr === todayString);
        const count = bookingsOnDate.length;

        // Base Classes
        let cellClasses = 'relative h-14 p-1.5 rounded-xl border flex flex-col justify-between items-center transition text-left focus:outline-none';

        if (isSelected) {
            // Selected Day
            cellClasses += ' bg-gray-900 text-white border-gray-900 shadow-md ring-2 ring-gray-900/30';
        } else if (isToday) {
            // Today
            cellClasses += ' bg-blue-50/70 border-blue-300 text-blue-900 font-bold hover:bg-blue-100/70';
        } else if (isCurrentMonth) {
            // Current Month
            cellClasses += ' bg-white border-gray-150 text-gray-800 hover:bg-gray-100/70 hover:border-gray-300';
        } else {
            // Other Month
            cellClasses += ' bg-gray-50/60 border-gray-100 text-gray-300 hover:bg-gray-100/50';
        }

        cell.className = cellClasses;

        // Nomor Hari & Badge Hari Ini
        const topRow = document.createElement('div');
        topRow.className = 'w-full flex items-center justify-between';

        const dayNumSpan = document.createElement('span');
        dayNumSpan.className = `text-xs font-semibold ${isSelected ? 'text-white' : (isToday ? 'text-blue-700 font-bold' : (isCurrentMonth ? 'text-gray-800' : 'text-gray-300'))}`;
        dayNumSpan.textContent = dayNum;
        topRow.appendChild(dayNumSpan);

        if (isToday && !isSelected) {
            const todayBadge = document.createElement('span');
            todayBadge.className = 'w-1.5 h-1.5 rounded-full bg-blue-600';
            topRow.appendChild(todayBadge);
        }

        cell.appendChild(topRow);

        // Indikator Titik Pemesanan
        const bottomRow = document.createElement('div');
        bottomRow.className = 'w-full flex items-center justify-center space-x-1';

        if (count > 0) {
            // Tentukan Warna Sesuai Aturan:
            // 1 Pemesanan = Emerald, 2 Pemesanan = Amber, >=3 Pemesanan = Rose
            let dotColor = 'bg-emerald-500';
            let dotTitle = '1 Pemesanan';
            if (count === 2) {
                dotColor = 'bg-amber-500';
                dotTitle = '2 Pemesanan';
            } else if (count >= 3) {
                dotColor = 'bg-rose-500';
                dotTitle = `${count} Pemesanan`;
            }

            // Jika selected, gunakan ring kontras
            const dot = document.createElement('span');
            dot.className = `w-2 h-2 rounded-full ${dotColor} ${isSelected ? 'ring-1 ring-white' : ''}`;
            dot.title = dotTitle;
            bottomRow.appendChild(dot);

            // Jika lebih dari 1 kamar, tampilkan counter mini
            if (count > 1) {
                const countBadge = document.createElement('span');
                countBadge.className = `text-[10px] font-bold leading-none ${isSelected ? 'text-gray-200' : 'text-gray-600'}`;
                countBadge.textContent = count;
                bottomRow.appendChild(countBadge);
            }
        }

        cell.appendChild(bottomRow);

        // Click Handler
        cell.addEventListener('click', () => {
            selectDate(dateStr);
        });

        return cell;
    }

    // Pilih Tanggal
    function selectDate(dateStr) {
        selectedDateString = dateStr;
        renderCalendar();
    }

    // Navigasi Bulan (-1 / +1)
    function changeMonth(delta) {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + delta);
        renderCalendar();
    }

    // Kembali ke Hari Ini
    function goToToday() {
        currentCalendarDate = new Date();
        selectedDateString = formatToDateString(new Date());
        renderCalendar();
    }

    // Render Panel Detail Pemesanan pada Tanggal Terpilih
    function renderSelectedDateDetails() {
        const bookingsMap = getBookingsByDateMap();
        const bookings = bookingsMap[selectedDateString] || [];

        const selectedDate = parseDateString(selectedDateString);
        const dayName = dayNamesID[selectedDate.getDay()];
        const dayNum = selectedDate.getDate();
        const monthName = monthNamesID[selectedDate.getMonth()];
        const year = selectedDate.getFullYear();

        // 1. Judul Tanggal & Badge Kamar Terisi
        document.getElementById('selectedDateTitle').textContent = `${dayName}, ${dayNum} ${monthName} ${year}`;

        const occupiedBadge = document.getElementById('selectedOccupiedBadge');
        const occupiedCount = bookings.length;
        occupiedBadge.textContent = `${occupiedCount} Kamar Terisi`;
        if (occupiedCount > 0) {
            occupiedBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800';
        } else {
            occupiedBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700';
        }

        // 2. Daftar Kartu Pemesanan
        const container = document.getElementById('selectedDateBookingsList');
        container.innerHTML = '';

        if (bookings.length === 0) {
            // Empty State
            container.innerHTML = `
                <div class="h-full min-h-[220px] flex flex-col items-center justify-center text-center p-6 bg-white/70 rounded-xl border border-dashed border-gray-300">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">Tidak ada pemesanan pada tanggal ini</p>
                    <p class="text-xs text-gray-500 mt-1 max-w-xs">Seluruh kamar berstatus kosong atau tidak ada jadwal reservasi aktif.</p>
                </div>
            `;
            return;
        }

        // Kartu untuk setiap pemesanan
        bookings.forEach(b => {
            let statusColor = 'bg-gray-100 text-gray-800';
            if (b.booking_status === 'Confirmed') statusColor = 'bg-green-100 text-green-800';
            else if (b.booking_status === 'Check-in') statusColor = 'bg-blue-100 text-blue-800';
            else if (b.booking_status === 'Pending') statusColor = 'bg-yellow-100 text-yellow-800';
            else if (b.booking_status === 'Check-out') statusColor = 'bg-gray-100 text-gray-800';

            let paymentBadgeColor = (b.payment_status === 'Paid')
                ? 'bg-green-100 text-green-800'
                : 'bg-yellow-100 text-yellow-800';

            const card = document.createElement('div');
            card.className = 'bg-white border border-gray-200 rounded-xl p-3.5 shadow-xs hover:border-gray-300 transition text-xs space-y-2';

            card.innerHTML = `
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-gray-900 text-sm tracking-wide">${b.booking_code}</span>
                        <span class="text-[11px] text-gray-500 font-medium">(${b.booking_type})</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ${statusColor}">
                        ${b.booking_status}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-gray-600">
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Kamar</span>
                        <span class="font-bold text-gray-900 text-xs">Kamar ${b.room_number}</span>
                        <span class="text-gray-500 text-[11px] block">Tipe: ${b.room_type} Bed</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Customer</span>
                        <span class="font-bold text-gray-900 text-xs">${b.customer_name}</span>
                        <span class="text-gray-500 text-[11px] block">${b.phone_number}</span>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-2 grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-gray-400 block text-[10px]">Check-in</span>
                        <span class="font-medium text-gray-800">${b.check_in_formatted}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Check-out</span>
                        <span class="font-medium text-gray-800">${b.check_out_formatted}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 text-[11px]">
                    <div class="flex items-center space-x-1.5">
                        <span class="text-gray-400">Bayar:</span>
                        <span class="font-medium text-gray-700">${b.payment_method}</span>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold ${paymentBadgeColor}">
                            ${b.payment_status}
                        </span>
                    </div>
                    <span class="font-bold text-gray-900 text-xs">${b.total_price_formatted}</span>
                </div>
            `;

            container.appendChild(card);
        });
    }

    // Modal Konfirmasi Hapus Pemesanan
    function confirmDelete(id, code) {
        document.getElementById('deleteCodeLabel').innerText = code;
        const prefix = "{{ auth()->user()->role === 'Admin' ? 'admin' : 'receptionist' }}";
        document.getElementById('deleteForm').action = `/${prefix}/pemesanan/${id}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Inisialisasi Kalender saat Halaman Selesai Dimuat
    document.addEventListener('DOMContentLoaded', () => {
        renderCalendar();
    });
</script>
@endpush
