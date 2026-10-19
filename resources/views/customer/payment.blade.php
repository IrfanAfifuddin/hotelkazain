@extends('layouts.booking')

@section('title', 'Pembayaran')
@section('step', '3')
@section('back_label', 'Isi Data Diri')
@section('back_url', route('customer.checkout'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Pembayaran</h1>

        {{-- Jika data pemesanan tidak ada / tidak valid --}}
        <div id="invalidBooking" class="hidden mt-8 border border-dashed border-gray-300 rounded-lg p-8 text-center">
            <p class="text-gray-700">Data pemesanan tidak ditemukan. Silakan ulangi pemesanan dari awal.</p>
            <a href="{{ route('customer.availability') }}" class="mt-4 inline-flex px-5 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800">Cek Ketersediaan</a>
        </div>

        <div id="paymentContent" class="hidden mt-8 space-y-6">

            {{-- ===================== TIMER ===================== --}}
            <div id="timerCard" class="border-2 border-gray-300 rounded-lg px-6 py-7 text-center">
                <p id="timerTitle" class="text-sm font-medium tracking-[0.2em] text-gray-500 uppercase">Selesaikan pembayaran dalam</p>
                <p id="timerText" class="mt-2 text-5xl sm:text-6xl font-bold tracking-wider text-gray-900 tabular-nums">20:00</p>
                <div class="mt-5 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div id="timerBar" class="h-full bg-gray-800 rounded-full transition-[width] duration-1000 ease-linear" style="width: 100%"></div>
                </div>
                <p id="timerNote" class="mt-3 text-sm text-gray-400">Pesanan akan dibatalkan otomatis jika pembayaran tidak diterima dalam 20 menit.</p>
                <a id="restartLink" href="{{ route('customer.availability') }}" class="hidden mt-4 inline-flex px-5 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800">Pesan Ulang</a>
            </div>

            {{-- ===================== RINGKASAN ===================== --}}
            <section class="border border-gray-200 rounded-lg overflow-hidden">
                <h2 class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold tracking-wider text-gray-700 uppercase">Ringkasan Pesanan</h2>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Kamar</dt><dd id="sumRooms" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Check-in</dt><dd id="sumCheckIn" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Check-out</dt><dd id="sumCheckOut" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Durasi</dt><dd id="sumDuration" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Tamu</dt><dd id="sumGuests" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Pemesan</dt><dd id="sumCustomer" class="text-right text-gray-900">-</dd></div>
                    </dl>
                    <div class="mt-5 pt-5 border-t border-gray-200 flex items-end justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">Total Tagihan</p>
                            <p class="text-xs text-gray-400">Bayar Penuh (Lunas)</p>
                        </div>
                        <p id="sumTotal" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                </div>
            </section>

            {{-- ===================== METODE PEMBAYARAN ===================== --}}
            <section class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold tracking-wider text-gray-700 uppercase">Metode Pembayaran</h2>
                    <span id="methodBadge" class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-900">Virtual Account</span>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">Selesaikan pembayaran melalui Virtual Account berikut:</p>

                    {{-- TODO (backend): nomor VA dan bank dari payment gateway --}}
                    <div class="mt-4 border-2 border-gray-300 bg-gray-50 rounded-lg p-5">
                        <p class="text-sm text-gray-400">Bank BCA — Virtual Account</p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                            <p id="vaNumber" class="font-mono text-3xl sm:text-4xl font-bold tracking-wider text-gray-900">-</p>
                            <button type="button" data-copy="va" class="copy-btn px-3 py-1.5 border border-gray-300 bg-white rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Salin</button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">[Nomor VA contoh — nanti berbeda tiap transaksi]</p>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4 border border-gray-200 rounded-lg px-5 py-4">
                        <span class="text-gray-500">Nominal tepat</span>
                        <span class="flex items-center gap-3">
                            <strong id="vaAmount" class="text-lg text-gray-900">-</strong>
                            <button type="button" data-copy="amount" class="copy-btn px-3 py-1.5 border border-gray-300 bg-white rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Salin</button>
                        </span>
                    </div>
                </div>
            </section>

            {{-- ===================== CARA MEMBAYAR ===================== --}}
            <details class="group border border-gray-200 rounded-lg overflow-hidden" open>
                <summary class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <span class="text-sm font-semibold tracking-wider text-gray-700 uppercase">Cara Membayar</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m18 15-6-6-6 6" /></svg>
                </summary>
                <ol class="p-6 space-y-3 text-gray-700">
                    @foreach ([
                        'Buka aplikasi m-banking atau ATM Anda.',
                        'Pilih menu Transfer → Virtual Account.',
                        'Masukkan nomor VA di atas.',
                        'Masukkan nominal sesuai total tagihan.',
                        'Konfirmasi transaksi dan selesai.',
                    ] as $i => $langkah)
                        <li class="flex items-center gap-3">
                            <span class="w-7 h-7 shrink-0 border border-gray-300 rounded-full flex items-center justify-center text-sm text-gray-600">{{ $i + 1 }}</span>
                            <span>{{ $langkah }}</span>
                        </li>
                    @endforeach
                </ol>
            </details>

            <p class="px-6 py-4 bg-gray-50 border border-gray-200 rounded-lg text-center text-gray-600">
                Setelah pembayaran diterima, konfirmasi akan muncul otomatis dan kode booking dikirim ke email Anda.
            </p>

            {{-- Tombol simulasi: hanya untuk demo sebelum payment gateway tersambung --}}
            <div class="text-center">
                <a id="demoSuccessLink" href="{{ route('customer.success') }}" class="text-sm text-gray-400 underline hover:text-gray-600">
                    [Demo] Simulasikan pembayaran berhasil
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('customer.partials.booking-data')
    <script>
        (function () {
            const KB = window.KazainBooking;
            const CHECKOUT_URL = @json(route('customer.checkout'));
            const PAYMENT_HOLD_SECONDS = 20 * 60; // PRD: kamar ditahan 20 menit

            const booking = KB.parse();
            let customer = null;
            try { customer = JSON.parse(sessionStorage.getItem('kazain_customer') || 'null'); } catch (e) { /* abaikan */ }

            if (!booking) {
                document.getElementById('invalidBooking').classList.remove('hidden');
                return;
            }
            document.getElementById('paymentContent').classList.remove('hidden');

            const query = KB.bookingQuery(booking);
            document.getElementById('backLink').href = `${CHECKOUT_URL}?${query}`;

            // ---------- Ringkasan ----------
            const set = (id, text) => { document.getElementById(id).textContent = text; };
            set('sumRooms', booking.roomsText);
            set('sumCheckIn', KB.formatDateTime(booking.start));
            set('sumCheckOut', KB.formatDateTime(booking.end));
            set('sumDuration', booking.durationText);
            set('sumGuests', `${booking.guests} orang`);
            set('sumCustomer', customer ? customer.customer_name : '-');
            set('sumTotal', KB.rupiah(booking.total));
            set('vaAmount', KB.rupiah(booking.total));
            set('methodBadge', booking.query.get('payment_method') || 'Virtual Account');

            // ---------- Nomor VA dummy (tetap sama untuk pesanan yang sama) ----------
            let h = 7;
            const seed = query.toString() + (customer ? customer.email : '');
            for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) % 1000000000000;
            const vaDigits = String(h).padStart(12, '0');
            set('vaNumber', vaDigits.replace(/(\d{4})(?=\d)/g, '$1 '));

            // ---------- Tombol salin ----------
            document.querySelectorAll('.copy-btn').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const text = btn.dataset.copy === 'va' ? vaDigits : String(booking.total);
                    try {
                        await navigator.clipboard.writeText(text);
                        btn.textContent = 'Tersalin ✓';
                    } catch (e) {
                        btn.textContent = 'Gagal';
                    }
                    setTimeout(() => { btn.textContent = 'Salin'; }, 1500);
                });
            });

            // ---------- Countdown 20 menit ----------
            // Batas waktu disimpan supaya tidak reset saat halaman di-refresh.
            // TODO (backend): batas waktu sebaiknya dari server (created_at booking + 20 menit).
            const DEADLINE_KEY = 'kazain_deadline_' + query.toString();
            let deadline = null;
            try { deadline = parseInt(sessionStorage.getItem(DEADLINE_KEY) || '', 10) || null; } catch (e) { /* abaikan */ }
            if (!deadline) {
                deadline = Date.now() + PAYMENT_HOLD_SECONDS * 1000;
                try { sessionStorage.setItem(DEADLINE_KEY, String(deadline)); } catch (e) { /* abaikan */ }
            }

            const timerText = document.getElementById('timerText');
            const timerBar = document.getElementById('timerBar');

            function tick() {
                const remaining = Math.max(0, Math.round((deadline - Date.now()) / 1000));
                timerText.textContent = `${KB.pad(Math.floor(remaining / 60))}:${KB.pad(remaining % 60)}`;
                timerBar.style.width = `${(remaining / PAYMENT_HOLD_SECONDS) * 100}%`;

                if (remaining <= 0) {
                    clearInterval(timer);
                    expire();
                }
            }

            function expire() {
                set('timerTitle', 'Waktu pembayaran habis');
                set('timerNote', 'Pesanan dibatalkan karena pembayaran tidak diterima dalam 20 menit. Silakan pesan ulang.');
                timerText.classList.replace('text-gray-900', 'text-gray-400');
                document.getElementById('restartLink').classList.remove('hidden');
                document.getElementById('demoSuccessLink').classList.add('hidden');
            }

            const timer = setInterval(tick, 1000);
            tick();
        })();
    </script>
@endpush