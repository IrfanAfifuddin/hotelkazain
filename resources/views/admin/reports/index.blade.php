@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@php
    $salesReportRoute = 'admin.reports.index';
    $transactionCount = $bookings->count();
@endphp

@section('content')
    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>
            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div class="relative">
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Laporan penjualan</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Pantau pendapatan dan transaksi hotel yang telah lunas.</p>
                </div>
                <a
                    href="{{ route('admin.reports.export', request()->query()) }}"
                    class="relative inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#d8c89c] px-4 text-sm font-semibold text-[#26332c] shadow-sm transition hover:bg-[#e3d6b5] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Excel
                </a>
            </div>
        </header>

        <section class="grid gap-5 lg:grid-cols-[0.95fr_1.05fr] lg:items-stretch">
            <div class="overflow-hidden rounded-2xl border border-kazain-border bg-white shadow-sm" aria-label="Filter periode laporan">
                <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-kazain-text">Periode laporan</h2>
                        <p class="mt-1 text-sm text-kazain-muted">Pilih rentang tanggal transaksi yang ingin ditampilkan.</p>
                    </div>
                </div>
                <form action="{{ route($salesReportRoute) }}" method="GET" class="flex h-full flex-col justify-between gap-5 p-5 sm:px-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="start_date" class="text-xs font-semibold uppercase tracking-wide text-kazain-muted">Dari tanggal</label>
                            <input
                                id="start_date"
                                type="date"
                                name="start_date"
                                value="{{ $startDate ?? '' }}"
                                class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10"
                            >
                        </div>
                        <div class="space-y-1.5">
                            <label for="end_date" class="text-xs font-semibold uppercase tracking-wide text-kazain-muted">Sampai tanggal</label>
                            <input
                                id="end_date"
                                type="date"
                                name="end_date"
                                value="{{ $endDate ?? '' }}"
                                class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:ring-4 focus:ring-kazain-primary/10"
                            >
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="min-h-11 flex-1 rounded-xl bg-kazain-primary px-5 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20"
                        >
                            Terapkan filter
                        </button>
                        @if ($startDate || $endDate)
                            <a
                                href="{{ route($salesReportRoute) }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-kazain-border bg-white px-4 text-sm font-medium text-kazain-muted transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15"
                            >
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <article class="group rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md sm:p-6">
                <div class="flex h-full items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-kazain-muted">Total pendapatan lunas</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        <div class="mt-4 flex items-center gap-2 border-t border-kazain-border pt-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#edf1ec] text-kazain-primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5M5.25 3.75h13.5A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                </svg>
                            </span>
                            <p class="text-sm text-kazain-muted">
                                <span class="font-semibold text-kazain-text">{{ number_format($transactionCount, 0, ',', '.') }}</span>
                                transaksi lunas pada periode terpilih
                            </p>
                        </div>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#edf1ec] text-kazain-primary">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-kazain-border bg-kazain-surface shadow-sm">
            <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-semibold text-kazain-text">Rincian Transaksi Sukses</h2>
                    </div>
                    <p class="mt-1 text-sm text-kazain-muted">Daftar pembayaran yang berhasil diterima.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] text-left text-sm">
                    <thead class="bg-[#f7f8f5]">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">No.</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Kode booking</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">No kamar</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Nama customer</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Tipe kamar</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Durasi</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Metode pembayaran</th>
                            <th scope="col" class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Total harga</th>
                            <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Tanggal transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-kazain-border/70">
                        @forelse ($bookings as $index => $booking)
                            @php
                                $checkIn = $booking->check_in_time;
                                $checkOut = $booking->check_out_time;
                                $duration = str_starts_with($booking->booking_type, 'Short Time')
                                    ? $checkIn->diffInHours($checkOut) . ' Jam'
                                    : max(1, $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay())) . ' Malam';
                                $paymentDate = $booking->payment_time ?? $booking->created_at;
                                $paymentBadge = $booking->payment_method === 'Cash'
                                    ? 'bg-[#e8f5ed] text-[#287249] ring-[#bbe4ca]'
                                    : 'bg-[#eaf1ff] text-[#315eaa] ring-[#c4d8ff]';
                            @endphp
                            <tr class="transition hover:bg-[#f7f8f5]">
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-kazain-text">{{ $booking->booking_code }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex min-w-8 justify-center rounded-full bg-[#f0f1f4] px-2 py-1 text-xs font-semibold text-kazain-text">{{ $booking->room->room_number ?? '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-text">{{ $booking->customer->customer_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $booking->room->room_type ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-text">{{ $duration }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $paymentBadge }}">{{ $booking->payment_method ?? 'Cash' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right font-semibold text-kazain-text">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $paymentDate->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-14 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#edf1ec] text-kazain-primary">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5M5.25 3.75h13.5A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                        </svg>
                                    </span>
                                    <p class="mt-3 font-semibold text-kazain-text">Belum ada transaksi</p>
                                    <p class="mt-1 text-sm text-kazain-muted">Tidak ada transaksi lunas pada rentang tanggal tersebut.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($extensions->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-kazain-border bg-kazain-surface shadow-sm">
                <div class="border-b border-kazain-border px-5 py-5 sm:px-6"><h2 class="text-base font-semibold text-kazain-text">Rincian extend lunas</h2><p class="mt-1 text-sm text-kazain-muted">Pembayaran perpanjangan tercatat terpisah dari transaksi awal.</p></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead class="bg-[#f7f8f5]"><tr><th class="px-6 py-3 text-xs text-kazain-muted">Kode booking</th><th class="px-6 py-3 text-xs text-kazain-muted">Kamar</th><th class="px-6 py-3 text-xs text-kazain-muted">Durasi</th><th class="px-6 py-3 text-xs text-kazain-muted">Metode</th><th class="px-6 py-3 text-right text-xs text-kazain-muted">Nominal</th></tr></thead><tbody class="divide-y divide-kazain-border/70">@foreach ($extensions as $extension)<tr><td class="px-6 py-4 font-semibold text-kazain-text">{{ $extension->booking->booking_code }}</td><td class="px-6 py-4">{{ $extension->booking->room->room_number }}</td><td class="px-6 py-4">{{ $extension->extension_type }} · {{ $extension->duration_hours }} Jam</td><td class="px-6 py-4">{{ $extension->payment_method }}</td><td class="px-6 py-4 text-right font-semibold">Rp {{ number_format($extension->amount, 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div>
            </section>
        @endif
    </div>
@endsection
