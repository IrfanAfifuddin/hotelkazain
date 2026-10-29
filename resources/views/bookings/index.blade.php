@extends('layouts.app')

@section('title', 'Data Pemesanan')

@section('content')
    @php
        $isAdmin = auth()->user()->role === 'Admin';
        $bookingIndexRoute = $isAdmin ? 'admin.pemesanan.index' : 'receptionist.pemesanan.index';
    @endphp

    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>
            <div>
                <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </p>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Data pemesanan</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Kelola reservasi kamar dan pantau jadwal menginap tamu.</p>
            </div>
        </header>

    {{-- Alert Flash --}}
    @if(session('success'))
        <div role="status" class="flex items-start gap-3 rounded-2xl border border-[#c6dfcf] bg-[#f0f7f2] px-4 py-3.5 text-sm text-[#285c3d]">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <svg class="h-5 w-5 shrink-0 text-[#287249]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi" class="rounded-lg p-1 text-[#287249] transition hover:bg-[#e1f0e6]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- 1. KOMPONEN KALENDER PEMESANAN --}}
    <section class="overflow-hidden rounded-2xl border border-kazain-border bg-gray-100 shadow-sm">
        {{-- Kalender Header & Legenda --}}
        <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 md:flex-row md:items-center md:justify-between sm:px-6">
            <div>
                <h2 class="flex items-center gap-2 text-base font-semibold text-kazain-text">
                    <svg class="h-5 w-5 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span>Kalender Pemesanan Kamar</span>
                </h2>
                <p class="mt-1 text-sm text-kazain-muted">Pantau okupansi kamar dan jadwal reservasi per tanggal.</p>
            </div>

            {{-- Legenda Indikator Titik Pemesanan --}}
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2 text-xs text-kazain-muted">
                <span class="mr-1 font-semibold text-kazain-text">Legenda:</span>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm"></span>
                    <span>1 Pemesanan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500 shadow-sm"></span>
                    <span>2 Pemesanan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-rose-500 shadow-sm"></span>
                    <span>&ge; 3 Pemesanan</span>
                </div>
            </div>
        </div>

        {{-- Kalender Grid & Detail Layout --}}
        <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-12">

            {{-- SISI KIRI: Kalender Bulanan (7 Kolom) --}}
            <div class="flex flex-col justify-between lg:col-span-7">
                <div>
                    {{-- Navigasi Bulan & Tahun --}}
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 id="currentMonthYearLabel" class="text-base font-semibold tracking-tight text-kazain-text">
                                Bulan Tahun
                            </h3>
                            <button
                                type="button"
                                onclick="goToToday()"
                                class="rounded-lg bg-[#edf1ec] px-2.5 py-1 text-xs font-semibold text-kazain-primary transition hover:bg-[#e1e8e1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15"
                            >
                                Hari Ini
                            </button>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                onclick="changeMonth(-1)"
                                title="Bulan Sebelumnya"
                                class="rounded-lg border border-kazain-border p-1.5 text-kazain-muted transition hover:bg-kazain-surface focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                onclick="changeMonth(1)"
                                title="Bulan Berikutnya"
                                class="rounded-lg border border-kazain-border p-1.5 text-kazain-muted transition hover:bg-kazain-surface focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Nama Hari (Min - Sab) --}}
                    <div class="mb-1.5 grid grid-cols-7 gap-1 text-center text-xs font-semibold text-kazain-muted">
                        <div class="py-1 text-[#975b50]">Min</div>
                        <div class="py-1">Sen</div>
                        <div class="py-1">Sel</div>
                        <div class="py-1">Rab</div>
                        <div class="py-1">Kam</div>
                        <div class="py-1">Jum</div>
                        <div class="py-1 text-kazain-primary">Sab</div>
                    </div>

                    {{-- Kotak Tanggal-Tanggal Kalender --}}
                    <div id="calendarDaysGrid" class="grid grid-cols-7 gap-1.5">
                        {{-- Diisi secara dinamis via JavaScript --}}
                    </div>
                </div>

                {{-- Catatan Okupansi --}}
                <div class="mt-4 flex items-center justify-between gap-3 border-t border-kazain-border pt-3 text-xs text-kazain-muted">
                    <span>* Klik tanggal untuk melihat rincian pemesanan dan kamar terisi.</span>
                    <span id="calendarTotalMonthBookings" class="font-medium text-kazain-text">0 pemesanan bulan ini</span>
                </div>
            </div>

            {{-- SISI KANAN: Detail Pemesanan pada Tanggal Terpilih (5 Kolom) --}}
            <div class="flex h-full min-h-[380px] flex-col rounded-xl border border-kazain-border bg-kazain-surface p-4 lg:col-span-5">
                {{-- Header Detail Tanggal --}}
                <div class="mb-3 flex items-center justify-between gap-3 border-b border-kazain-border pb-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-kazain-muted">Detail tanggal</p>
                        <h4 id="selectedDateTitle" class="mt-0.5 text-sm font-semibold text-kazain-text">
                            Pilih Tanggal
                        </h4>
                    </div>
                    <div>
                        <span id="selectedOccupiedBadge" class="inline-flex items-center rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-semibold text-kazain-primary">
                            0 Kamar Terisi
                        </span>
                    </div>
                </div>

                {{-- List Kartu Pemesanan pada Tanggal Terpilih --}}
                <div id="selectedDateBookingsList" class="max-h-[370px] flex-1 space-y-3 overflow-y-auto pr-1">
                    {{-- Diisi secara dinamis via JavaScript --}}
                </div>
            </div>

        </div>
    </section>

    {{-- 2. TABEL DATA PEMESANAN --}}
    {{-- Search & Filter Bar --}}
    <div class="rounded-2xl border border-kazain-border bg-gray-100 p-4 shadow-sm sm:p-5">
        <form action="{{ route($bookingIndexRoute) }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative min-w-0 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari kode booking, nama customer, atau nomor kamar..."
                    class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface py-2 pl-10 pr-4 text-sm text-kazain-text outline-none transition placeholder:text-kazain-muted/70 focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10"
                >
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-kazain-muted">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-kazain-primary px-5 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                Cari
            </button>
            @if($search)
                <a href="{{ route($bookingIndexRoute) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-kazain-border bg-gray-50 px-4 text-sm font-medium text-kazain-muted transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Pemesanan --}}
    <section class="overflow-hidden rounded-2xl border border-kazain-border bg-gray-100 shadow-sm">
        <div class="flex flex-col gap-2 border-b border-kazain-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-base font-semibold text-kazain-text">Daftar Seluruh Pemesanan</h2>
                    <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">{{ number_format($bookings->total(), 0, ',', '.') }}</span>
                </div>
                <p class="mt-1 text-sm text-kazain-muted">Kelola reservasi dan informasi pembayaran tamu.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px] text-left text-sm">
                <thead class="border-b border-kazain-border bg-[#f7f8f5] text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">
                    <tr>
                        <th class="px-6 py-3">Kode booking</th>
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
                <tbody class="divide-y divide-kazain-border">
                    @forelse($bookings as $b)
                        <tr class="transition hover:bg-[#f7f8f5]">
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-kazain-text">{{ $b->booking_code }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="font-medium text-kazain-text">{{ $b->customer->customer_name ?? '-' }}</p>
                                <p class="text-xs text-kazain-muted">{{ $b->customer->phone_number ?? '-' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-kazain-text">
                                Kamar {{ $b->room->room_number ?? '-' }}
                                <span class="block text-xs text-kazain-muted">({{ $b->room->room_type ?? '-' }})</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-kazain-muted">{{ $b->booking_type }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-kazain-muted">{{ \Carbon\Carbon::parse($b->check_in_time)->format('d M Y H:i') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-kazain-muted">{{ \Carbon\Carbon::parse($b->check_out_time)->format('d M Y H:i') }}</td>

                            {{-- Status Pemesanan --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $badge = match($b->booking_status) {
                                        'Confirmed' => 'bg-emerald-100 text-emerald-800',
                                        'Check-in' => 'bg-[#edf1ec] text-kazain-primary',
                                        'Check-out' => 'bg-[#f4f5f1] text-kazain-muted',
                                        'Pending' => 'bg-amber-100 text-amber-800',
                                        'Cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-[#f4f5f1] text-kazain-muted',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">
                                    <span aria-hidden="true" class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70"></span>
                                    {{ $b->booking_status }}
                                </span>
                            </td>

                            {{-- Kolom Pembayaran (Metode & Status Terpisah) --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $methodBadge = match($b->payment_method) {
                                        'Cash' => 'bg-[#e8f5ed] text-[#287249] border-[#bbe4ca]',
                                        'QRIS' => 'bg-[#eaf1ff] text-[#315eaa] border-[#c4d8ff]',
                                        'Virtual Account', 'Transfer' => 'bg-[#edf1ec] text-kazain-primary border-kazain-border',
                                        default => 'bg-[#f4f5f1] text-kazain-muted border-kazain-border',
                                    };

                                    $paymentStatusBadge = match($b->payment_status) {
                                        'Paid' => 'bg-emerald-100 text-emerald-800',
                                        'Pending' => 'bg-amber-100 text-amber-800',
                                        'Failed', 'Cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-[#f4f5f1] text-kazain-muted',
                                    };
                                @endphp
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-lg border px-2 py-1 text-[10px] font-semibold {{ $methodBadge }}">
                                        {{ $b->payment_method ?? 'Belum ada data' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-[10px] font-semibold {{ $paymentStatusBadge }}">
                                        {{ $b->payment_status ?? 'Pending' }}
                                    </span>
                                </div>
                            </td>

<td class="whitespace-nowrap px-6 py-4 font-semibold text-kazain-text">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    @if ($b->booking_status === 'Check-in')
                                        <a href="{{ $isAdmin ? route('admin.pemesanan.extend.create', $b) : route('receptionist.pemesanan.extend.create', $b) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition" title="Perpanjang">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            Extend
                                        </a>
                                    @endif
                                    @if (auth()->user()->role === 'Receptionist' && $b->payment_method === 'Midtrans' && $b->payment_status === 'Pending')
                                        <a href="{{ route('receptionist.pemesanan.payment.show', $b) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 hover:bg-purple-100 transition" title="Bayar">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Bayar
                                        </a>
                                    @endif
                                    <a href="{{ $isAdmin ? route('admin.pemesanan.edit', $b->id) : route('receptionist.pemesanan.edit', $b->id) }}" aria-label="Edit pemesanan {{ $b->booking_code }}" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-kazain-border bg-gray-50 text-kazain-muted transition hover:border-[#cbd8cd] hover:bg-[#f4f5f1] hover:text-kazain-primary focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651m-1.414-4.065a2.25 2.25 0 0 1 3.182 3.182L8.25 22.286l-4.5 1.125 1.125-4.5L19.099 3.073Z" />
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmDelete({{ $b->id }}, @js($b->booking_code))" aria-label="Hapus pemesanan {{ $b->booking_code }}" title="Hapus" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#ead7d2] bg-[#fcf7f6] text-[#975b50] transition hover:border-[#d9bdb5] hover:bg-[#f7eeeb] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#975b50]/15">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.48m-12 .646c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.48m7.5 0V4.88c0-1.18-.91-2.17-2.09-2.207a51.964 51.964 0 0 0-3.82 0C8.66 2.71 7.75 3.7 7.75 4.88v.906m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-14 text-center text-sm text-kazain-muted">
                                Tidak ada data pemesanan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="border-t border-kazain-border px-5 py-4 sm:px-6">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>
    </div>

    {{-- Confirmation Modal Hapus --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-xs" onclick="closeDeleteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-sm rounded-2xl border border-kazain-border bg-kazain-surface p-6 shadow-[0_24px_70px_-30px_rgba(29,48,39,0.45)]">
                <h3 id="deleteModalTitle" class="mb-2 text-lg font-semibold text-kazain-text">Hapus pemesanan?</h3>
                <p class="mb-6 text-sm leading-6 text-kazain-muted">Apakah Anda yakin ingin menghapus pemesanan <span id="deleteCodeLabel" class="font-semibold text-kazain-text">--</span>?</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeDeleteModal()" class="min-h-11 flex-1 rounded-xl border border-kazain-border bg-gray-50 px-4 text-sm font-medium text-kazain-text transition hover:bg-[#f4f5f1]">
                            Batal
                        </button>
                        <button type="submit" class="min-h-11 flex-1 rounded-xl bg-[#975b50] px-4 text-sm font-semibold text-white transition hover:bg-[#844a40]">
                            Hapus pemesanan
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
        let cellClasses = 'relative flex h-14 flex-col items-center justify-between rounded-xl border p-1.5 text-left transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20';

        if (isSelected) {
            // Selected Day
            cellClasses += ' border-kazain-primary bg-kazain-primary text-white shadow-sm';
        } else if (isToday) {
            // Today
            cellClasses += ' border-[#c9b887] bg-[#f7f3e8] font-bold text-kazain-primary hover:bg-[#f0e8d3]';
        } else if (isCurrentMonth) {
            // Current Month
            cellClasses += ' border-kazain-border bg-gray-50 text-kazain-text hover:border-[#b9c9bc] hover:bg-[#f7f8f5]';
        } else {
            // Other Month
            cellClasses += ' border-kazain-border/60 bg-kazain-surface text-kazain-muted/50 hover:bg-[#f4f5f1]';
        }

        cell.className = cellClasses;

        // Nomor Hari & Badge Hari Ini
        const topRow = document.createElement('div');
        topRow.className = 'flex w-full items-center justify-between';

        const dayNumSpan = document.createElement('span');
        dayNumSpan.className = `text-xs font-semibold ${isSelected ? 'text-white' : (isToday ? 'font-bold text-kazain-primary' : (isCurrentMonth ? 'text-kazain-text' : 'text-kazain-muted/50'))}`;
        dayNumSpan.textContent = dayNum;
        topRow.appendChild(dayNumSpan);

        if (isToday && !isSelected) {
            const todayBadge = document.createElement('span');
            todayBadge.className = 'h-1.5 w-1.5 rounded-full bg-kazain-accent';
            topRow.appendChild(todayBadge);
        }

        cell.appendChild(topRow);

        // Indikator Titik Pemesanan
        const bottomRow = document.createElement('div');
        bottomRow.className = 'flex w-full items-center justify-center gap-1';

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
                countBadge.className = `text-[10px] font-bold leading-none ${isSelected ? 'text-white/80' : 'text-kazain-muted'}`;
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
            occupiedBadge.className = 'inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800';
        } else {
            occupiedBadge.className = 'inline-flex items-center rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-semibold text-kazain-primary';
        }

        // 2. Daftar Kartu Pemesanan
        const container = document.getElementById('selectedDateBookingsList');
        container.innerHTML = '';

        if (bookings.length === 0) {
            // Empty State
            container.innerHTML = `
                <div class="flex h-full min-h-[220px] flex-col items-center justify-center rounded-xl border border-dashed border-kazain-border bg-gray-50/70 p-6 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#edf1ec] text-kazain-muted">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-kazain-text">Tidak ada pemesanan pada tanggal ini</p>
                    <p class="mt-1 max-w-xs text-xs text-kazain-muted">Seluruh kamar berstatus kosong atau tidak ada jadwal reservasi aktif.</p>
                </div>
            `;
            return;
        }

        // Kartu untuk setiap pemesanan
        bookings.forEach(b => {
            let statusColor = 'bg-[#f4f5f1] text-kazain-muted';
            if (b.booking_status === 'Confirmed') statusColor = 'bg-emerald-100 text-emerald-800';
            else if (b.booking_status === 'Check-in') statusColor = 'bg-[#edf1ec] text-kazain-primary';
            else if (b.booking_status === 'Pending') statusColor = 'bg-amber-100 text-amber-800';
            else if (b.booking_status === 'Cancelled') statusColor = 'bg-rose-100 text-rose-800';

            let paymentBadgeColor = (b.payment_status === 'Paid')
                ? 'bg-emerald-100 text-emerald-800'
                : 'bg-amber-100 text-amber-800';

            const card = document.createElement('div');
            card.className = 'space-y-2 rounded-xl border border-kazain-border bg-gray-50 p-3.5 text-xs shadow-sm transition hover:border-[#b9c9bc]';

            card.innerHTML = `
                <div class="flex items-center justify-between gap-2 border-b border-kazain-border pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold tracking-wide text-kazain-text">${b.booking_code}</span>
                        <span class="text-[11px] font-medium text-kazain-muted">(${b.booking_type})</span>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${statusColor}">
                        ${b.booking_status}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-kazain-muted">
                    <div>
                        <span class="block text-[10px] font-semibold uppercase text-kazain-muted/70">Kamar</span>
                        <span class="text-xs font-semibold text-kazain-text">Kamar ${b.room_number}</span>
                        <span class="block text-[11px] text-kazain-muted">Tipe: ${b.room_type} Bed</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-semibold uppercase text-kazain-muted/70">Customer</span>
                        <span class="text-xs font-semibold text-kazain-text">${b.customer_name}</span>
                        <span class="block text-[11px] text-kazain-muted">${b.phone_number}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 rounded-lg bg-[#f7f8f5] p-2 text-[11px]">
                    <div>
                        <span class="block text-[10px] text-kazain-muted/70">Check-in</span>
                        <span class="font-medium text-kazain-text">${b.check_in_formatted}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-kazain-muted/70">Check-out</span>
                        <span class="font-medium text-kazain-text">${b.check_out_formatted}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-1 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="text-kazain-muted/70">Bayar:</span>
                        <span class="font-medium text-kazain-muted">${b.payment_method}</span>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold ${paymentBadgeColor}">
                            ${b.payment_status}
                        </span>
                    </div>
                    <span class="text-xs font-semibold text-kazain-text">${b.total_price_formatted}</span>
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
