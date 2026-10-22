@extends('layouts.booking')

@section('title', 'Pembayaran')
@section('step', '3')
@section('back_label', 'Isi Data Diri')
@section('back_url', route('customer.checkout'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Pembayaran</h1>

        @if (! $bookingCode)
            <div class="mt-8 border border-dashed border-gray-300 rounded-lg p-8 text-center">
                <p class="text-gray-700">Data pemesanan tidak ditemukan. Silakan ulangi pemesanan dari awal.</p>
                <a href="{{ route('customer.availability') }}" class="mt-4 inline-flex px-5 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800">Cek Ketersediaan</a>
            </div>
        @else
            <div class="mt-8 space-y-6">
                <section class="border border-gray-200 rounded-lg overflow-hidden">
                    <h2 class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold tracking-wider text-gray-700 uppercase">Pembayaran Booking</h2>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Kode Booking</span><strong class="text-gray-900">{{ $bookingCode }}</strong></div>
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Status</span><strong id="paymentStatus" class="text-gray-900">Menunggu pembayaran</strong></div>
                        <p class="text-sm text-gray-500">Total dan status transaksi divalidasi server. Pembayaran lunas hanya setelah notifikasi Midtrans tervalidasi.</p>
                    </div>
                </section>

                <button type="button" id="payButton" class="w-full py-4 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-semibold">Bayar dengan Midtrans</button>
                <p id="paymentError" class="hidden text-center text-sm text-red-600"></p>
            </div>
        @endif
    </div>
@endsection

@if ($bookingCode)
    @push('scripts')
        <script src="{{ $midtransIsProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            (() => {
                const bookingCode = @json($bookingCode);
                const payButton = document.getElementById('payButton');
                const errorElement = document.getElementById('paymentError');
                const statusElement = document.getElementById('paymentStatus');
                const snapUrl = @json(route('customer.payment.snap', ['booking' => $bookingCode]));
                const statusUrl = @json(route('customer.payment.status', ['booking' => $bookingCode]));
                let redirectedToSuccess = false;

                const showError = (message) => {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                };

                const refreshStatus = async () => {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const booking = (await response.json()).data;
                    statusElement.textContent = booking.payment_status === 'Paid' ? 'Pembayaran terverifikasi' : 'Menunggu pembayaran';
                    if (booking.payment_status === 'Paid' && !redirectedToSuccess) {
                        redirectedToSuccess = true;
                        try {
                            const query = new URLSearchParams(window.location.search);
                            query.delete('booking');
                            sessionStorage.setItem('kazain_last_booking', JSON.stringify({
                                booking_code: bookingCode,
                                query: query.toString(),
                                customer: JSON.parse(sessionStorage.getItem('kazain_customer') || 'null'),
                            }));
                        } catch (error) {
                            // Success page can still be reached from booking email.
                        }
                        window.location.href = @json(route('customer.success')).concat('?booking=', encodeURIComponent(bookingCode));
                    }
                };

                payButton.addEventListener('click', async () => {
                    payButton.disabled = true;
                    payButton.textContent = 'Menyiapkan pembayaran...';
                    try {
                        const response = await fetch(snapUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message || 'Transaksi tidak dapat dibuat.');
                        window.snap.pay(result.data.token, {
                            onSuccess: refreshStatus,
                            onPending: refreshStatus,
                            onError: refreshStatus,
                            onClose: refreshStatus,
                        });
                    } catch (error) {
                        showError(error.message);
                    } finally {
                        payButton.disabled = false;
                        payButton.textContent = 'Bayar dengan Midtrans';
                    }
                });

                setInterval(refreshStatus, 5000);
                refreshStatus();
            })();
        </script>
    @endpush
@endif
