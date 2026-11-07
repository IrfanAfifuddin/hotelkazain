@extends('layouts.app')

@section('title', 'Pembayaran Booking')

@section('content')
    <div class="mx-auto w-full max-w-2xl min-w-0">
        <h1 class="text-2xl font-bold text-kazain-text">Pembayaran Booking</h1>
        <p class="mt-1 text-sm text-kazain-muted">Booking {{ $booking->booking_code }}</p>

        <div class="mt-6 space-y-4 rounded-2xl border border-kazain-border bg-kazain-surface p-4 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-4 border-b border-kazain-border/50 py-3">
                <span class="text-sm text-kazain-muted">Customer</span>
                <strong class="text-kazain-text">{{ $booking->customer?->customer_name }}</strong>
            </div>
            <div class="flex items-center justify-between gap-4 border-b border-kazain-border/50 py-3">
                <span class="text-sm text-kazain-muted">Kamar</span>
                <strong class="text-kazain-text">{{ $booking->room?->room_number }}</strong>
            </div>
            <div class="flex items-center justify-between gap-4 border-b border-kazain-border/50 py-3">
                <span class="text-sm text-kazain-muted">Total</span>
                <strong class="text-lg text-kazain-text">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="text-sm text-kazain-muted">Status pembayaran</span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $booking->payment_status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : ($booking->payment_status === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ $booking->payment_status }}
                </span>
            </div>

            @if ($booking->payment_status === 'Pending')
                <form method="POST" action="{{ $paymentCreateRoute }}" class="mt-6 space-y-3">
                    @csrf
                    <button class="w-full rounded-xl bg-kazain-primary hover:bg-kazain-primary-hover px-5 py-3 text-sm font-semibold text-white transition shadow-sm">Buat Pembayaran Midtrans</button>
                </form>
            @else
                <p class="rounded-xl bg-gray-50 border border-kazain-border/30 p-4 text-sm text-kazain-muted">Booking ini tidak menunggu pembayaran Midtrans.</p>
            @endif

            @isset($snapToken)
                <button type="button" id="payButton" class="w-full rounded-xl bg-kazain-primary hover:bg-kazain-primary-hover px-5 py-3 text-sm font-semibold text-white transition shadow-sm">Bayar dengan Snap</button>
            @endisset
        </div>
    </div>
@endsection

@isset($snapToken)
    @push('scripts')
        <script src="{{ $midtransIsProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            document.getElementById('payButton').addEventListener('click', () => {
                window.snap.pay(@json($snapToken));
            });
        </script>
    @endpush
@endisset
