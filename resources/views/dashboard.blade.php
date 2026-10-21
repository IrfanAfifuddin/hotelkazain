@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @php
        $availableRooms = $availableRooms ?? \App\Models\Room::where('room_status', 'Available')->orderBy('room_number')->get();
        $availableCount = $availableCount ?? $availableRooms->count();
        $occupiedCount = $occupiedCount ?? \App\Models\Room::where('room_status', 'Occupied')->count();
        $todayCheckInCount = $todayCheckInCount ?? \App\Models\Booking::whereDate('check_in_time', today())->count();
        $todayCheckOutCount = $todayCheckOutCount ?? \App\Models\Booking::whereDate('check_out_time', today())->count();
        $recentBookings = $recentBookings ?? \App\Models\Booking::with(['customer', 'room'])->latest()->take(5)->get();
        $roomStatusRoute = auth()->user()->role === 'Admin' ? 'admin.status-kamar.index' : 'receptionist.status-kamar.index';
        $bookingIndexRoute = auth()->user()->role === 'Admin' ? 'admin.pemesanan.index' : 'receptionist.pemesanan.index';
    @endphp

    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>
            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Selamat datang, {{ auth()->user()->user_name }}
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Semua yang Anda perlukan untuk melayani tamu dengan lebih mudah.</p>
                </div>
                <button
                    type="button"
                    id="btnOpenCreateBookingModal"
                    onclick="openCreateBookingModal()"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#d8c89c] px-4 text-sm font-semibold text-[#26332c] shadow-sm transition hover:bg-[#e3d6b5] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Pemesanan</span>
                </button>
            </div>
        </header>

        {{-- Statistik Cards --}}
        <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan hotel">

            {{-- Total Kamar Tersedia --}}
            <a href="{{ route($roomStatusRoute) }}" class="group rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition hover:border-kazain-primary/30 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 sm:p-6">
                <div>
                    <p class="text-sm font-medium text-kazain-muted">Kamar tersedia</p>
                    <p id="stat-available" class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $availableCount }}</p>
                </div>
            </a>

            {{-- Total Kamar Terisi --}}
            <a href="{{ route($roomStatusRoute) }}" class="group rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition hover:border-kazain-primary/30 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 sm:p-6">
                <div>
                    <p class="text-sm font-medium text-kazain-muted">Kamar terisi</p>
                    <p id="stat-occupied" class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $occupiedCount }}</p>
                </div>
            </a>

            {{-- Check-in Hari Ini --}}
            <a href="{{ route($bookingIndexRoute) }}" class="group rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition hover:border-kazain-primary/30 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 sm:p-6">
                <div>
                    <p class="text-sm font-medium text-kazain-muted">Check-in hari ini</p>
                    <p id="stat-checkin" class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $todayCheckInCount }}</p>
                </div>
            </a>

            {{-- Check-out Hari Ini --}}
            <a href="{{ route($bookingIndexRoute) }}" class="group rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition hover:border-kazain-primary/30 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 sm:p-6">
                <div>
                    <p class="text-sm font-medium text-kazain-muted">Check-out hari ini</p>
                    <p id="stat-checkout" class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $todayCheckOutCount }}</p>
                </div>
            </a>

        </section>

        {{-- Pemesanan Terbaru --}}
        <section class="overflow-hidden rounded-2xl border border-kazain-border bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold text-kazain-text">Pemesanan terbaru</h2>
                        <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">{{ number_format($recentBookings->count(), 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-kazain-muted">Aktivitas reservasi terakhir di Kazain Hotel.</p>
                </div>
                <label class="relative block w-full sm:max-w-sm sm:flex-1 lg:max-w-lg">
                    <span class="sr-only">Cari pemesanan terbaru</span>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-kazain-muted" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7.25" />
                        <path stroke-linecap="round" d="m16.5 16.5 4.25 4.25" />
                    </svg>
                    <input
                        type="search"
                        id="recentBookingsSearch"
                        placeholder="Cari nama, nomor telepon, kamar, tipe, atau pembayaran..."
                        class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface pl-10 pr-4 text-sm text-kazain-text outline-none transition placeholder:text-kazain-muted/70 focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10"
                    >
                </label>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] table-fixed text-left text-sm">
                    <thead class="border-b border-kazain-border bg-[#f7f8f5]">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Nama customer</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">No. telepon</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">No kamar</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Tipe</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Jam check-in</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Jam check-out</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody id="recentBookingsTableBody" class="divide-y divide-kazain-border">
                        @forelse($recentBookings as $booking)
                            @php
                                $paymentMethodLabel = in_array($booking->payment_method, ['Transfer', 'Virtual Account', 'QRIS'], true)
                                    ? 'TF'
                                    : ($booking->payment_method ?? 'Belum ada data');
                                $paymentBadge = $booking->payment_method === 'Cash'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : 'border-blue-200 bg-blue-50 text-blue-700';
                            @endphp
                            <tr data-booking-row class="transition hover:bg-[#f7f8f5]">
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-kazain-text">{{ $booking->customer->customer_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $booking->customer->phone_number ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="rounded-lg bg-[#f4f5f1] px-2.5 py-1 font-mono text-xs text-kazain-muted">{{ $booking->room->room_number ?? '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-text">{{ $booking->room->room_type ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium tabular-nums text-kazain-text">{{ \Carbon\Carbon::parse($booking->check_in_time)->format('H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium tabular-nums text-kazain-text">{{ \Carbon\Carbon::parse($booking->check_out_time)->format('H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center whitespace-nowrap rounded-full border px-2 py-0.5 text-xs font-medium {{ $paymentBadge }}">{{ $paymentMethodLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRecentRow">
                                <td colspan="7" class="px-6 py-14 text-center text-sm text-kazain-muted">
                                    Belum ada data pemesanan.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noRecentBookingsMatch" class="hidden">
                            <td colspan="7" class="px-6 py-14 text-center text-sm text-kazain-muted">
                                Tidak ada pemesanan yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- MODAL TAMBAH PEMESANAN (LANGSUNG DARI DASHBOARD) --}}
    <div id="createBookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeCreateBookingModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-200">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50/70">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gray-900 text-white rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" id="modal-title">Tambah Pemesanan Kamar</h3>
                            <p class="text-xs text-gray-500">Input reservasi langsung dari dashboard admin.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick="closeCreateBookingModal()"
                        class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 hover:bg-gray-200/50 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Alert Error Box --}}
                <div id="modalErrorBox" class="hidden mx-6 mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs">
                    <div class="flex items-center space-x-2 font-semibold mb-1">
                        <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span id="modalErrorTitle">Terjadi kesalahan validasi</span>
                    </div>
                    <ul id="modalErrorList" class="list-disc list-inside space-y-0.5 text-red-700"></ul>
                </div>

                {{-- Form Tambah Pemesanan --}}
                <form id="createBookingForm" onsubmit="handleCreateBookingSubmit(event)" class="p-6 space-y-5">
                    @csrf

                    {{-- Section 1: Data Customer --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b border-gray-100 flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>1. Data Customer</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap Customer <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    id="modal_customer_name"
                                    placeholder="Contoh: Budi Santoso"
                                    required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="phone_number"
                                    id="modal_phone_number"
                                    placeholder="Contoh: 081234567890"
                                    required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email Customer (Opsional)</label>
                                <input
                                    type="email"
                                    name="email"
                                    id="modal_email"
                                    placeholder="Contoh: budi@gmail.com"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Detail Kamar & Durasi --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b border-gray-100 flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5Z" />
                            </svg>
                            <span>2. Detail Kamar & Jadwal</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Kamar Tersedia <span class="text-red-500">*</span></label>
                                <select
                                    name="room_id"
                                    id="modal_room_id"
                                    required
                                    onchange="updateRoomCalculations()"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="">-- Pilih Kamar Tersedia --</option>
                                    @forelse($availableRooms as $room)
                                        <option
                                            value="{{ $room->id }}"
                                            data-room-number="{{ $room->room_number }}"
                                            data-room-type="{{ $room->room_type }}"
                                            data-price-5="{{ $room->price_5_hours }}"
                                            data-price-8="{{ $room->price_8_hours }}"
                                            data-price-full="{{ $room->full_day_price }}"
                                        >
                                            Kamar {{ $room->room_number }} — {{ $room->room_type }} Bed (Tersedia)
                                        </option>
                                    @empty
                                        <option value="" disabled>-- Tidak ada kamar yang tersedia saat ini --</option>
                                    @endforelse
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Kamar</label>
                                <input
                                    type="text"
                                    id="modal_room_type_display"
                                    value="Pilih kamar terlebih dahulu"
                                    readonly
                                    class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600 font-semibold cursor-not-allowed"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Durasi Pemesanan <span class="text-red-500">*</span></label>
                                <select
                                    name="booking_type"
                                    id="modal_booking_type"
                                    required
                                    onchange="updateRoomCalculations()"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="Full Day" selected>Full Day (Sampai 12:00 Hari Berikutnya)</option>
                                    <option value="Short Time 5 Jam">Short Time (5 Jam)</option>
                                    <option value="Short Time 8 Jam">Short Time (8 Jam)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Sumber Pemesanan <span class="text-red-500">*</span></label>
                                <select
                                    name="source"
                                    id="modal_source"
                                    required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="Offline" selected>Offline (Datang Langsung)</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="Website">Website</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Waktu Check-in <span class="text-red-500">*</span></label>
                                <input
                                    type="datetime-local"
                                    name="check_in_time"
                                    id="modal_check_in_time"
                                    value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                                    min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                                    required
                                    onchange="validateDashboardCheckIn(this); updateRoomCalculations()"
                                    oninput="validateDashboardCheckIn(this)"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Waktu Check-out (Otomatis)</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="modal_check_out_preview"
                                        readonly
                                        value="-"
                                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-semibold text-gray-800 cursor-not-allowed"
                                    >
                                    <span class="absolute right-3 top-2.5 text-xs text-gray-400">Auto</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Pembayaran & Total Harga --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b border-gray-100 flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0-8.25a60.07 60.07 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096V6m-18 8.25h16.5m0 0a2.25 2.25 0 0 0 2.25-2.25V6m0 8.25a2.25 2.25 0 0 1-2.25 2.25m-16.5 0a2.25 2.25 0 0 0 2.25-2.25V6" />
                            </svg>
                            <span>3. Pembayaran & Status</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                                <select
                                    name="payment_method"
                                    id="modal_payment_method"
                                    required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="Cash" selected>Cash / Tunai</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="Virtual Account">Virtual Account</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status Pembayaran <span class="text-red-500">*</span></label>
                                <select
                                    name="payment_status"
                                    id="modal_payment_status"
                                    required
                                    onchange="handlePaymentStatusChange()"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="Paid" selected>Lunas (Paid)</option>
                                    <option value="Pending">Pending (DP / Belum)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status Pemesanan <span class="text-red-500">*</span></label>
                                <select
                                    name="booking_status"
                                    id="modal_booking_status"
                                    required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:bg-white transition"
                                >
                                    <option value="Check-in" selected>Check-in (Tamu Langsung Masuk)</option>
                                    <option value="Confirmed">Confirmed (Terkonfirmasi)</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                        </div>

                        {{-- Total Price Banner --}}
                        <div class="mt-4 p-4 rounded-xl bg-gray-900 text-white flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-300">Total Biaya Pemesanan</p>
                                <p class="text-xs text-gray-400 mt-0.5">Dihitung otomatis berdasarkan tipe kamar & durasi</p>
                            </div>
                            <div class="text-right">
                                <span id="modal_total_price_display" class="text-2xl font-extrabold tracking-tight text-white">
                                    Rp 0
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-200">
                        <button
                            type="button"
                            onclick="closeCreateBookingModal()"
                            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="btnSubmitBooking"
                            class="inline-flex items-center space-x-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg id="submitSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="submitBtnText">Simpan Pemesanan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI SUKSES PEMESANAN --}}
    <div id="bookingSuccessModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeBookingSuccessModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-center shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 mb-4">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Pemesanan Berhasil!</h3>
                <p class="text-sm text-gray-500 mb-4">Data pemesanan kamar telah berhasil disimpan ke sistem.</p>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5 text-left text-xs space-y-2">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                        <span class="text-gray-500 font-medium">Kode Booking:</span>
                        <span id="successBookingCode" class="font-bold text-sm text-gray-900 bg-gray-200 px-2 py-0.5 rounded">KZN-000000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Customer:</span>
                        <span id="successCustomerName" class="font-semibold text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Kamar:</span>
                        <span id="successRoomInfo" class="font-semibold text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Check-in:</span>
                        <span id="successCheckIn" class="font-semibold text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Total Harga:</span>
                        <span id="successTotalPrice" class="font-bold text-green-700">-</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button
                        type="button"
                        onclick="closeBookingSuccessModal()"
                        class="flex-1 bg-gray-900 hover:bg-gray-800 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition shadow-sm"
                    >
                        Tutup
                    </button>
                    <a
                        href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}"
                        class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg text-sm transition"
                    >
                        Lihat Data Pemesanan
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const recentBookingsSearch = document.getElementById('recentBookingsSearch');
    if (recentBookingsSearch) {
        recentBookingsSearch.addEventListener('input', filterRecentBookings);
    }

    function filterRecentBookings() {
        const tbody = document.getElementById('recentBookingsTableBody');
        const query = recentBookingsSearch.value.trim().toLocaleLowerCase();
        const bookingRows = Array.from(tbody.querySelectorAll('[data-booking-row]'));
        let visibleRows = 0;

        bookingRows.forEach(row => {
            const matches = row.textContent.toLocaleLowerCase().includes(query);
            row.classList.toggle('hidden', !matches);
            if (matches) {
                visibleRows += 1;
            }
        });

        const noMatchesRow = document.getElementById('noRecentBookingsMatch');
        noMatchesRow.classList.toggle('hidden', bookingRows.length === 0 || visibleRows > 0);
    }

    // Buka Modal Tambah Pemesanan
    function openCreateBookingModal() {
        const modal = document.getElementById('createBookingModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Reset alert error
        hideModalError();

        // Update perhitungan default
        updateRoomCalculations();

        // Focus nama customer
        setTimeout(() => {
            const input = document.getElementById('modal_customer_name');
            if (input) input.focus();
        }, 100);
    }

    // Tutup Modal Tambah Pemesanan
    function closeCreateBookingModal() {
        const modal = document.getElementById('createBookingModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Modal Konfirmasi Sukses
    function openBookingSuccessModal(bookingData) {
        document.getElementById('successBookingCode').textContent = bookingData.booking_code;
        document.getElementById('successCustomerName').textContent = bookingData.customer_name;
        document.getElementById('successRoomInfo').textContent = 'Kamar ' + bookingData.room_number + ' (' + bookingData.room_type + ')';
        document.getElementById('successCheckIn').textContent = bookingData.check_in_formatted;
        document.getElementById('successTotalPrice').textContent = bookingData.total_price_formatted;

        const modal = document.getElementById('bookingSuccessModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeBookingSuccessModal() {
        const modal = document.getElementById('bookingSuccessModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Error Alert Handler
    function showModalError(title, errors) {
        const errorBox = document.getElementById('modalErrorBox');
        const errorTitle = document.getElementById('modalErrorTitle');
        const errorList = document.getElementById('modalErrorList');

        errorTitle.textContent = title;
        errorList.innerHTML = '';

        if (Array.isArray(errors)) {
            errors.forEach(msg => {
                const li = document.createElement('li');
                li.textContent = msg;
                errorList.appendChild(li);
            });
        } else if (typeof errors === 'object') {
            for (const key in errors) {
                if (errors.hasOwnProperty(key)) {
                    errors[key].forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorList.appendChild(li);
                    });
                }
            }
        } else if (errors) {
            const li = document.createElement('li');
            li.textContent = String(errors);
            errorList.appendChild(li);
        }

        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideModalError() {
        const errorBox = document.getElementById('modalErrorBox');
        errorBox.classList.add('hidden');
    }

    function validateDashboardCheckIn(input) {
        const selected = new Date(input.value);
        const valid = input.value && !Number.isNaN(selected.getTime()) && selected >= new Date();
        input.setCustomValidity(valid ? '' : 'Waktu check-in tidak boleh sudah berlalu.');
        return valid;
    }

    // Perubahan Status Pembayaran -> Sinkronkan Status Pemesanan
    function handlePaymentStatusChange() {
        const paymentStatus = document.getElementById('modal_payment_status').value;
        const bookingStatusSelect = document.getElementById('modal_booking_status');

        if (paymentStatus === 'Paid') {
            bookingStatusSelect.value = 'Check-in';
        } else {
            bookingStatusSelect.value = 'Pending';
        }
    }

    // Perhitungan Realtime Tipe Kamar, Total Harga, dan Waktu Check-out
    function updateRoomCalculations() {
        const roomSelect = document.getElementById('modal_room_id');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const roomTypeDisplay = document.getElementById('modal_room_type_display');
        const bookingType = document.getElementById('modal_booking_type').value;
        const checkInInput = document.getElementById('modal_check_in_time');
        const checkOutPreview = document.getElementById('modal_check_out_preview');
        const totalPriceDisplay = document.getElementById('modal_total_price_display');

        // 1. Tipe Kamar & Harga
        let price = 0;
        if (selectedOption && selectedOption.value) {
            const roomType = selectedOption.getAttribute('data-room-type') || 'Standard';
            roomTypeDisplay.value = roomType + ' Bed';

            const price5 = parseFloat(selectedOption.getAttribute('data-price-5')) || 0;
            const price8 = parseFloat(selectedOption.getAttribute('data-price-8')) || 0;
            const priceFull = parseFloat(selectedOption.getAttribute('data-price-full')) || 0;

            if (bookingType === 'Short Time 5 Jam') {
                price = price5;
            } else if (bookingType === 'Short Time 8 Jam') {
                price = price8;
            } else {
                price = priceFull;
            }
        } else {
            roomTypeDisplay.value = 'Pilih kamar terlebih dahulu';
        }

        totalPriceDisplay.textContent = 'Rp ' + price.toLocaleString('id-ID');

        // 2. Waktu Check-out Otomatis
        if (checkInInput.value) {
            const checkInDate = new Date(checkInInput.value);
            if (!isNaN(checkInDate.getTime())) {
                let checkOutDate = new Date(checkInDate.getTime());

                if (bookingType === 'Short Time 5 Jam') {
                    checkOutDate.setHours(checkOutDate.getHours() + 5);
                } else if (bookingType === 'Short Time 8 Jam') {
                    checkOutDate.setHours(checkOutDate.getHours() + 8);
                } else {
                    // Full Day: check-in besok jam 12:00
                    checkOutDate.setDate(checkOutDate.getDate() + 1);
                    checkOutDate.setHours(12, 0, 0, 0);
                }

                // Format tanggal output
                const day = String(checkOutDate.getDate()).padStart(2, '0');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const month = months[checkOutDate.getMonth()];
                const year = checkOutDate.getFullYear();
                const hours = String(checkOutDate.getHours()).padStart(2, '0');
                const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');

                checkOutPreview.value = `${day} ${month} ${year} ${hours}:${minutes}`;
            } else {
                checkOutPreview.value = '-';
            }
        } else {
            checkOutPreview.value = '-';
        }
    }

    // Submit Pemesanan Via AJAX
    async function handleCreateBookingSubmit(event) {
        event.preventDefault();
        hideModalError();

        const form = document.getElementById('createBookingForm');
        const submitBtn = document.getElementById('btnSubmitBooking');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitBtnText = document.getElementById('submitBtnText');

        // Cegah double submission
        submitBtn.disabled = true;
        submitSpinner.classList.remove('hidden');
        submitBtnText.textContent = 'Menyimpan...';

        const formData = new FormData(form);
        const storeUrl = "{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.store') : route('receptionist.pemesanan.store') }}";

        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    showModalError(data.message || 'Data tidak valid', data.errors || []);
                } else {
                    showModalError('Gagal Menyimpan Pemesanan', data.message || 'Terjadi kesalahan sistem.');
                }
                return;
            }

            // SUKSES
            // 1. Reset form & tutup modal form
            const bookedRoomId = formData.get('room_id');
            const bookedBookingStatus = formData.get('booking_status');
            form.reset();
            closeCreateBookingModal();

            // 2. Tampilkan modal konfirmasi sukses
            openBookingSuccessModal(data.booking);

            // 3. Perbarui angka statistik Dashboard realtime
            if (data.stats) {
                const statAvailable = document.getElementById('stat-available');
                const statOccupied = document.getElementById('stat-occupied');
                const statCheckin = document.getElementById('stat-checkin');
                const statCheckout = document.getElementById('stat-checkout');

                if (statAvailable) statAvailable.textContent = data.stats.available;
                if (statOccupied) statOccupied.textContent = data.stats.occupied;
                if (statCheckin) statCheckin.textContent = data.stats.today_check_in;
                if (statCheckout) statCheckout.textContent = data.stats.today_check_out;
            }

            // 4. Perbarui tabel Pemesanan Terbaru di Dashboard
            if (data.booking) {
                insertNewBookingRow(data.booking);
            }

            // 5. Jika kamar menjadi Occupied, hapus kamar dari dropdown modal
            if (bookedBookingStatus === 'Check-in' && bookedRoomId) {
                const roomOption = document.querySelector(`#modal_room_id option[value="${bookedRoomId}"]`);
                if (roomOption) {
                    roomOption.remove();
                }
            }

        } catch (error) {
            console.error('Error submitting booking:', error);
            showModalError('Koneksi Error', 'Gagal menghubungi server. Periksa koneksi internet Anda.');
        } finally {
            submitBtn.disabled = false;
            submitSpinner.classList.add('hidden');
            submitBtnText.textContent = 'Simpan Pemesanan';
        }
    }

    // Sisipkan baris baru pada tabel Pemesanan Terbaru
    function insertNewBookingRow(booking) {
        const tbody = document.getElementById('recentBookingsTableBody');
        const emptyRow = document.getElementById('emptyRecentRow');
        if (emptyRow) {
            emptyRow.remove();
        }

        const tr = document.createElement('tr');
        tr.dataset.bookingRow = '';
        tr.className = 'transition bg-[#edf1ec] hover:bg-[#f7f8f5]';

        appendRecentBookingCell(tr, booking.customer_name || '-', 'whitespace-nowrap px-6 py-4 font-semibold text-kazain-text');
        appendRecentBookingCell(tr, booking.phone_number || '-', 'whitespace-nowrap px-6 py-4 text-kazain-muted');
        appendRecentBookingCell(tr, booking.room_number || '-', 'whitespace-nowrap px-6 py-4', 'rounded-lg bg-[#f4f5f1] px-2.5 py-1 font-mono text-xs text-kazain-muted');
        appendRecentBookingCell(tr, booking.room_type || '-', 'whitespace-nowrap px-6 py-4 text-kazain-text');
        appendRecentBookingCell(tr, booking.check_in_clock || '-', 'whitespace-nowrap px-6 py-4 font-medium tabular-nums text-kazain-text');
        appendRecentBookingCell(tr, booking.check_out_clock || '-', 'whitespace-nowrap px-6 py-4 font-medium tabular-nums text-kazain-text');

        const paymentMethodLabel = booking.payment_method === 'Cash'
            ? 'Cash'
            : (booking.payment_method ? 'TF' : 'Belum ada data');
        const paymentBadgeClasses = booking.payment_method === 'Cash'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-blue-200 bg-blue-50 text-blue-700';
        appendRecentBookingCell(
            tr,
            paymentMethodLabel,
            'whitespace-nowrap px-6 py-4',
            `inline-flex items-center whitespace-nowrap rounded-full border px-2 py-0.5 text-xs font-medium ${paymentBadgeClasses}`
        );

        // Sisipkan di baris teratas
        if (tbody.firstChild) {
            tbody.insertBefore(tr, tbody.firstChild);
        } else {
            tbody.appendChild(tr);
        }
        filterRecentBookings();

        // Hilangkan highlight hijau setelah 3 detik
        setTimeout(() => {
            tr.classList.remove('bg-[#edf1ec]');
        }, 3000);
    }

    function appendRecentBookingCell(row, value, cellClasses, badgeClasses = null) {
        const cell = document.createElement('td');
        cell.className = cellClasses;

        if (badgeClasses) {
            const badge = document.createElement('span');
            badge.className = badgeClasses;
            badge.textContent = value;
            cell.appendChild(badge);
        } else {
            cell.textContent = value;
        }

        row.appendChild(cell);
    }
</script>
@endpush
