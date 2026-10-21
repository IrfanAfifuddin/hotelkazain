@extends('layouts.customer')

@section('title', 'Pembayaran Berhasil')

@section('content')
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-12 sm:py-16">

        {{-- Jika data booking tidak ditemukan --}}
        <div id="noBooking" class="hidden text-center py-10">
            <p class="text-gray-700">Data pemesanan tidak ditemukan. Silakan cek email Anda untuk melihat kode booking.</p>
            <a href="{{ route('customer.home') }}" class="mt-6 inline-flex px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-900 hover:bg-gray-50">Kembali ke Beranda</a>
        </div>

        <div id="bookingContent" class="hidden">
            {{-- Judul --}}
            <div class="text-center">
                <span class="mx-auto w-16 h-16 rounded-full border-2 border-gray-800 flex items-center justify-center text-gray-800">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="m8.5 12 2.5 2.5 4.5-5" /></svg>
                </span>
                <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-gray-900">Pembayaran Berhasil</h1>
                <p class="mt-1 text-gray-500">Pemesanan Anda telah dikonfirmasi.</p>
            </div>

            {{-- Kartu kode booking --}}
            <div class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-[#1e293b] px-6 py-5 text-white">
                    <p class="text-xs tracking-[0.2em] text-gray-400 uppercase">Kode Booking</p>
                    <div class="mt-1 flex items-center gap-3">
                        <p id="bookingCode" class="font-mono text-2xl sm:text-3xl font-bold tracking-[0.2em]">-</p>
                        <button type="button" id="copyCode" class="w-8 h-8 border border-white/30 rounded-md flex items-center justify-center hover:bg-white/10 transition" aria-label="Salin kode booking">
                            <svg id="copyIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="14" height="14" x="8" y="8" rx="2" /><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" /></svg>
                            <svg id="copiedIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                        </button>
                    </div>
                </div>

                <dl class="divide-y divide-gray-100 text-sm">
                    @foreach ([
                        'bName' => 'Nama Pemesan',
                        'bEmail' => 'Email',
                        'bRooms' => 'Kamar',
                        'bCheckIn' => 'Check-in',
                        'bCheckOut' => 'Check-out',
                        'bDuration' => 'Durasi',
                    ] as $id => $label)
                        <div class="grid grid-cols-[110px_1fr] sm:grid-cols-[130px_1fr] gap-4 px-6 py-3.5">
                            <dt class="text-gray-400">{{ $label }}</dt>
                            <dd id="{{ $id }}" class="text-gray-900 break-words">-</dd>
                        </div>
                    @endforeach

                    <div class="flex items-end justify-between gap-4 px-6 py-4">
                        <div>
                            <dt class="text-gray-700">Total Harga</dt>
                            <p class="text-xs text-gray-400">Lunas</p>
                        </div>
                        <dd id="bTotal" class="text-xl font-bold text-gray-900">-</dd>
                    </div>
                </dl>
            </div>

            {{-- Info email --}}
            <div class="mt-5 flex items-start gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" /></svg>
                <p>Kode booking juga telah dikirim ke email Anda <strong id="bEmailNote" class="text-gray-900">-</strong>. Mohon periksa folder spam jika tidak menemukan emailnya.</p>
            </div>

            <a href="{{ route('customer.home') }}" class="mt-6 block w-full py-3.5 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    @include('customer.partials.booking-data')
    <script>
        (function () {
            const KB = window.KazainBooking;

            // TODO (backend): nanti data diambil dari database berdasarkan kode booking,
            // misalnya route('customer.success', ['code' => 'KZN-123456']).
            let saved = null;
            try { saved = JSON.parse(sessionStorage.getItem('kazain_last_booking') || 'null'); } catch (e) { /* abaikan */ }

            const booking = saved ? KB.parse('?' + saved.query) : null;
            if (!saved || !booking) {
                document.getElementById('noBooking').classList.remove('hidden');
                return;
            }
            document.getElementById('bookingContent').classList.remove('hidden');

            const customer = saved.customer || {};
            const set = (id, text) => { document.getElementById(id).textContent = text; };

            set('bookingCode', saved.booking_code);
            set('bName', customer.customer_name || '-');
            set('bEmail', customer.email || '-');
            set('bEmailNote', customer.email || '-');
            set('bRooms', booking.roomsText);
            set('bCheckIn', KB.formatDateTimeLong(booking.start));
            set('bCheckOut', KB.formatDateTimeLong(booking.end));
            set('bDuration', booking.durationText);
            set('bTotal', KB.rupiah(booking.total));

            // Salin kode booking
            const copyButton = document.getElementById('copyCode');
            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(saved.booking_code);
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