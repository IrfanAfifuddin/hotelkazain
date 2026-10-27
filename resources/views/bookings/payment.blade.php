@extends('layouts.app')

@section('title', 'Pembayaran Booking')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">Pembayaran Booking</h1>
        <p class="mt-1 text-sm text-gray-500">Booking {{ $booking->booking_code }}</p>

        <div class="mt-6 rounded-xl border border-gray-200 bg-gray-100 p-6 shadow-sm space-y-4">
            <div class="flex justify-between"><span class="text-gray-500">Customer</span><strong>{{ $booking->customer?->customer_name }}</strong></div>
            <div class="flex justify-between"><span class="text-gray-500">Kamar</span><strong>{{ $booking->room?->room_number }}</strong></div>
            <div class="flex justify-between"><span class="text-gray-500">Total</span><strong>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong></div>
            <div class="flex justify-between"><span class="text-gray-500">Status pembayaran</span><strong>{{ $booking->payment_status }}</strong></div>

            @if ($booking->payment_status === 'Pending')
                <form method="POST" action="{{ $paymentCreateRoute }}">
                    @csrf
                    <button class="w-full rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Buat Pembayaran Midtrans</button>
                </form>
            @else
                <p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">Booking ini tidak menunggu pembayaran Midtrans.</p>
            @endif

            @isset($snapToken)
                <button type="button" id="payButton" class="w-full rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Bayar dengan Snap</button>
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
