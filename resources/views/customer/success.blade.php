@extends('layouts.customer')

@php
    // Support both flows:
    // 1. Legacy Snap flow: $bookingCode (string), $booking may be null
    // 2. Redirect flow: $booking (Booking model) with payment_status
    $isRedirectFlow = isset($booking) && $booking instanceof \App\Models\Booking;
    $paymentPending = $isRedirectFlow && $booking->payment_status === 'Pending';
    $paymentStatusUnavailable = $paymentStatusUnavailable ?? false;

    if ($isRedirectFlow && $booking) {
        $checkIn = $booking->check_in_time?->copy()->locale('id');
        $checkOut = $booking->check_out_time?->copy()->locale('id');

        if ($booking->booking_type === 'Full Day' && $checkIn && $checkOut) {
            $nights = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24));
            $durasi = $nights . ' Malam';
        } else {
            $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
        }
    }
@endphp

@section('title', $paymentPending ? 'Menunggu Pembayaran' : 'Status Pemesanan')

@section('content')
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-12 sm:py-16">

        @if ($isRedirectFlow && $booking && $booking->payment_status === 'Paid')
            {{-- ===================== BERHASIL ===================== --}}
            <div class="text-center">
                <span class="mx-auto w-16 h-16 rounded-full border-2 border-gray-800 flex items-center justify-center text-gray-800" aria-hidden="true">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="m8.5 12 2.5 2.5 4.5-5" /></svg>
                </span>
                <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-gray-900">Pembayaran Berhasil</h1>
                <p class="mt-1 text-gray-500">Pemesanan Anda telah dikonfirmasi.</p>
            </div>

            <section class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-900 px-6 py-5 text-white">
                    <p class="text-xs tracking-[0.2em] text-gray-400 uppercase">Kode Booking</p>
                    <div class="mt-1 flex items-center gap-3">
                        <p id="bookingCode" class="font-mono text-2xl sm:text-3xl font-bold tracking-[0.2em]">{{ $booking->booking_code }}</p>
                        <button type="button" id="copyCode" class="w-8 h-8 border border-white/30 rounded-md flex items-center justify-center hover:bg-white/10 transition" aria-label="Salin kode booking">
                            <svg id="copyIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="14" height="14" x="8" y="8" rx="2" /><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" /></svg>
                            <svg id="copiedIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                        </button>
                    </div>
                </div>

                <dl class="divide-y divide-gray-100 text-sm">
                    @foreach ([
                        'Nama Pemesan' => $booking->customer->customer_name,
                        'Email' => $booking->customer->email,
                        'Kamar' => $booking->room->room_type . ' Bed · No. ' . $booking->room->room_number,
                        'Check-in' => $checkIn ? $checkIn->translatedFormat('l, d F Y, H:i') . ' WIB' : '-',
                        'Check-out' => $checkOut ? $checkOut->translatedFormat('l, d F Y, H:i') . ' WIB' : '-',
                        'Durasi' => $durasi,
                    ] as $label => $value)
                        <div class="grid grid-cols-[110px_1fr] sm:grid-cols-[130px_1fr] gap-4 px-6 py-3.5">
                            <dt class="text-gray-400">{{ $label }}</dt>
                            <dd class="text-gray-900 break-words">{{ $value }}</dd>
                        </div>
                    @endforeach

                    <div class="flex items-end justify-between gap-4 px-6 py-4">
                        <div>
                            <dt class="text-gray-700">Total Harga</dt>
                            <p class="text-xs text-gray-400">Lunas</p>
                        </div>
                        <dd class="text-xl font-bold text-gray-900">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </section>

            <div class="mt-5 flex items-start gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" /></svg>
                <p>Kode booking juga telah dikirim ke email Anda <strong class="text-gray-900">{{ $booking->customer->email }}</strong>. Mohon periksa folder spam jika tidak menemukan emailnya.</p>
            </div>

            <a href="{{ route('customer.home') }}" class="mt-6 block w-full py-3.5 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50 transition">
                Kembali ke Beranda
            </a>

        @elseif ($paymentPending)
            {{-- ===================== MENUNGGU PEMBAYARAN ===================== --}}
            <div class="text-center">
                <span class="mx-auto w-16 h-16 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-500" aria-hidden="true">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                </span>
                <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-gray-900">Menunggu Pembayaran</h1>
                <p class="mt-2 text-gray-500">Pembayaran belum kami terima. Kode booking akan dikirim setelah pembayaran terkonfirmasi.</p>
            </div>

            @if ($paymentStatusUnavailable)
                <div class="mt-8 flex items-start gap-3 border border-dashed border-gray-300 bg-gray-50 rounded-lg px-5 py-4 text-sm text-gray-600">
                    <svg class="w-5 h-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                    <p>Status pembayaran belum dapat diperiksa. Silakan muat ulang halaman ini beberapa saat lagi.</p>
                </div>
            @endif

            @if ($isRedirectFlow && $booking)
                <a href="{{ route('customer.payment.redirect', $booking->midtrans_order_id) }}"
                   class="mt-8 block w-full py-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-center text-lg font-semibold text-white transition">
                    Kembali ke Pembayaran
                </a>
            @elseif ($bookingCode)
                <a href="{{ route('customer.payment', ['booking' => $bookingCode]) }}"
                   class="mt-8 block w-full py-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-center text-lg font-semibold text-white transition">
                    Kembali ke Pembayaran
                </a>
            @endif

            <button type="button" onclick="window.location.reload()"
                    class="mt-3 block w-full py-3.5 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50 transition">
                Cek Status Lagi
            </button>

        @else
            {{-- ===================== GAGAL / KEDALUWARSA ===================== --}}
            <div class="text-center">
                <span class="mx-auto w-16 h-16 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-500" aria-hidden="true">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="m15 9-6 6" /><path d="m9 9 6 6" /></svg>
                </span>
                <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-gray-900">Pembayaran Tidak Berhasil</h1>
                <p class="mt-2 text-gray-500">Waktu pembayaran habis atau transaksi dibatalkan. Silakan mulai pemesanan baru.</p>
            </div>

            <a href="{{ route('customer.availability') }}"
               class="mt-8 block w-full py-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-center text-lg font-semibold text-white transition">
                Cek Ketersediaan Kamar
            </a>
            <a href="{{ route('customer.home') }}"
               class="mt-3 block w-full py-3.5 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50 transition">
                Kembali ke Beranda
            </a>
        @endif
    </div>
@endsection

@if ($isRedirectFlow && $booking && $booking->payment_status === 'Paid')
    @push('scripts')
        <script>
            (function () {
                const button = document.getElementById('copyCode');
                button.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(document.getElementById('bookingCode').textContent.trim());
                        document.getElementById('copyIcon').classList.add('hidden');
                        document.getElementById('copiedIcon').classList.remove('hidden');
                        setTimeout(() => {
                            document.getElementById('copyIcon').classList.remove('hidden');
                            document.getElementById('copiedIcon').classList.add('hidden');
                        }, 1500);
                    } catch (e) { /* abaikan */ }
                });
            })();
        </script>
    @endpush
@endif