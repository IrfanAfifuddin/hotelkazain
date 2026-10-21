@extends('layouts.booking')

@section('title', 'Pembayaran')
@section('step', '3')

@section('content')
    <main class="mx-auto w-full max-w-2xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold text-gray-900">Selesaikan Pembayaran</h1>

        <section class="mt-8 rounded-lg border border-gray-200 p-6">
            <dl class="space-y-3">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Kamar</dt>
                    <dd class="text-right text-gray-900">{{ $booking->room->room_number }} ({{ $booking->room->room_type }})</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Metode</dt>
                    <dd class="text-gray-900">{{ $booking->payment_method }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Total</dt>
                    <dd class="font-semibold text-gray-900">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($booking->payment_status === 'Paid')
                <p class="mt-6 rounded-lg bg-green-50 p-4 text-green-800">Pembayaran sudah diterima.</p>
                <a href="{{ route('customer.success', ['order_id' => $booking->midtrans_order_id]) }}"
                   class="mt-5 inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white hover:bg-gray-800">
                    Lihat kode booking
                </a>
            @elseif ($booking->payment_status === 'Pending' && $booking->midtrans_redirect_url)
                <p class="mt-6 text-sm text-gray-600">Kamar ditahan sampai {{ $booking->payment_expires_at?->format('d M Y, H:i') }}.</p>
                <a href="{{ $booking->midtrans_redirect_url }}"
                   class="mt-5 inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white hover:bg-gray-800">
                    Lanjutkan pembayaran di Midtrans
                </a>
            @else
                <p class="mt-6 rounded-lg bg-red-50 p-4 text-red-700">Transaksi ini tidak dapat dibayar. Silakan mulai pemesanan baru.</p>
                <a href="{{ route('customer.availability') }}"
                   class="mt-5 inline-flex w-full justify-center rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-900 hover:bg-gray-50">
                    Pesan kamar kembali
                </a>
            @endif
        </section>
    </main>
@endsection
