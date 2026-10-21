@extends('layouts.customer')

@section('title', $paymentPending ? 'Menunggu Pembayaran' : 'Status Pemesanan')

@section('content')
    <main class="mx-auto w-full max-w-xl px-4 py-12 sm:px-6 sm:py-16">
        @if ($booking->payment_status === 'Paid')
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-green-700 text-green-700" aria-hidden="true">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <path d="m8.5 12 2.5 2.5 4.5-5" />
                    </svg>
                </span>
                <h1 class="mt-5 text-3xl font-bold text-gray-900">Pembayaran Berhasil</h1>
                <p class="mt-2 text-gray-600">Pemesanan Anda telah dikonfirmasi.</p>
            </div>

            <section class="mt-8 overflow-hidden rounded-lg border border-gray-200">
                <div class="bg-slate-800 px-6 py-5 text-white">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-300">Kode Booking</p>
                    <p class="mt-2 font-mono text-3xl font-bold tracking-[0.2em]">{{ $booking->booking_code }}</p>
                </div>
                <dl class="divide-y divide-gray-100 px-6">
                    <div class="flex justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-gray-500">Nama pemesan</dt>
                        <dd class="text-right text-gray-900">{{ $booking->customer->customer_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="break-all text-right text-gray-900">{{ $booking->customer->email }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-gray-500">Kamar</dt>
                        <dd class="text-right text-gray-900">{{ $booking->room->room_number }} ({{ $booking->room->room_type }})</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-gray-500">Check-in</dt>
                        <dd class="text-right text-gray-900">{{ $booking->check_in_time->format('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-gray-500">Check-out</dt>
                        <dd class="text-right text-gray-900">{{ $booking->check_out_time->format('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-4 font-semibold">
                        <dt class="text-gray-700">Total dibayar</dt>
                        <dd class="text-gray-900">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </section>

            <p class="mt-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                Kode booking dikirim ke <strong class="text-gray-900">{{ $booking->customer->email }}</strong>.
                Periksa folder spam jika email belum terlihat.
            </p>
        @elseif ($paymentPending)
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Menunggu Pembayaran</h1>
                <p class="mt-2 text-gray-600">Status pembayaran belum diterima. Kode booking akan dikirim setelah pembayaran terkonfirmasi.</p>
            </div>
            <section class="mt-8 rounded-lg border border-gray-200 p-6">
                @if ($paymentStatusUnavailable)
                    <p class="text-sm text-amber-800">Status pembayaran belum dapat diperiksa. Silakan coba kembali beberapa saat lagi.</p>
                @endif
                <a href="{{ route('customer.payment', ['booking' => $booking->midtrans_order_id]) }}"
                   class="mt-5 inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white hover:bg-gray-800">
                    Kembali ke pembayaran
                </a>
            </section>
        @else
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Pembayaran Tidak Berhasil</h1>
                <p class="mt-2 text-gray-600">Pemesanan ini tidak dikonfirmasi. Silakan mulai pemesanan baru.</p>
                <a href="{{ route('customer.availability') }}"
                   class="mt-6 inline-flex rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white hover:bg-gray-800">
                    Cek ketersediaan kamar
                </a>
            </div>
        @endif
    </main>
@endsection
