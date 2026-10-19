@extends('layouts.app')

@section('title', 'Extend Pemesanan')

@php
    $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';
    $checkout = $booking->check_out_time->copy()->timezone('Asia/Jakarta');
@endphp

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <a href="{{ route($prefix . '.pemesanan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-kazain-primary transition hover:text-kazain-primary-hover">
            <span aria-hidden="true">←</span>
            Kembali ke Data Pemesanan
        </a>

        <header>
            <h1 class="text-2xl font-semibold tracking-tight text-kazain-text sm:text-3xl">Extend Pemesanan</h1>
            <p class="mt-2 text-sm text-kazain-muted">Perpanjang masa menginap — {{ $booking->booking_code }} · Kamar {{ $booking->room->room_number }}.</p>
        </header>

        <section class="rounded-2xl border border-kazain-border bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-kazain-text">Pemesanan saat ini</h2>
            <dl class="mt-5 grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-kazain-muted">Nama tamu</dt><dd class="mt-1 font-semibold text-kazain-text">{{ $booking->customer->customer_name ?? '-' }}</dd></div>
                <div><dt class="text-kazain-muted">Kamar</dt><dd class="mt-1 font-semibold text-kazain-text">{{ $booking->room->room_number }} · {{ $booking->room->room_type }}</dd></div>
                <div><dt class="text-kazain-muted">Check-in</dt><dd class="mt-1 font-semibold text-kazain-text">{{ $booking->check_in_time->copy()->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd></div>
                <div><dt class="text-kazain-muted">Check-out saat ini</dt><dd class="mt-1 font-semibold text-kazain-text">{{ $checkout->translatedFormat('d M Y, H:i') }} WIB</dd></div>
            </dl>
        </section>

        <form method="POST" action="{{ route($prefix . '.pemesanan.extend.store', $booking) }}" id="extendForm" class="space-y-6">
            @csrf
            <section>
                <h2 class="text-base font-semibold text-kazain-text">Tipe extend</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <label class="extend-option cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-kazain-primary has-[:checked]:bg-kazain-primary has-[:checked]:text-white">
                        <input class="sr-only" type="radio" name="extension_type" value="Short Time" checked>
                        <span class="block font-semibold">Short Time</span><span class="mt-1 block text-sm opacity-75">Pilih durasi 5 atau 8 jam.</span>
                    </label>
                    <label class="extend-option cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-kazain-primary has-[:checked]:bg-kazain-primary has-[:checked]:text-white">
                        <input class="sr-only" type="radio" name="extension_type" value="Full Day">
                        <span class="block font-semibold">Full Day</span><span class="mt-1 block text-sm opacity-75">Sampai pukul 12.00 hari berikutnya.</span>
                    </label>
                </div>
            </section>

            <section id="durationSection">
                <h2 class="text-base font-semibold text-kazain-text">Durasi extend</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <label class="duration-option cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-kazain-primary has-[:checked]:bg-kazain-primary has-[:checked]:text-white">
                        <input class="sr-only" type="radio" name="duration_hours" value="5" checked>
                        <span class="block font-semibold">5 Jam</span><span class="mt-1 block text-sm opacity-75">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</span>
                    </label>
                    <label class="duration-option cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-kazain-primary has-[:checked]:bg-kazain-primary has-[:checked]:text-white">
                        <input class="sr-only" type="radio" name="duration_hours" value="8">
                        <span class="block font-semibold">8 Jam</span><span class="mt-1 block text-sm opacity-75">Rp {{ number_format($booking->room->price_8_hours, 0, ',', '.') }}</span>
                    </label>
                </div>
            </section>

            <section>
                <h2 class="text-base font-semibold text-kazain-text">Metode pembayaran</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-[#287249] has-[:checked]:bg-[#e8f5ed]">
                        <input class="sr-only" type="radio" name="payment_method" value="Cash" checked><span class="block font-semibold text-kazain-text">Cash</span><span class="mt-1 block text-sm text-kazain-muted">Bayar tunai.</span>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-kazain-border bg-white p-5 shadow-sm transition has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec]">
                        <input class="sr-only" type="radio" name="payment_method" value="Transfer"><span class="block font-semibold text-kazain-text">Transfer</span><span class="mt-1 block text-sm text-kazain-muted">Bank transfer. Menunggu pembayaran.</span>
                    </label>
                </div>
            </section>

            <section class="rounded-2xl bg-kazain-primary p-5 text-white shadow-sm sm:p-6">
                <h2 class="text-base font-semibold">Ringkasan biaya</h2>
                <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div><p class="text-white/65">Durasi extend</p><p id="summaryDuration" class="mt-1 font-semibold">5 Jam</p></div>
                    <div><p class="text-white/65">Tarif perpanjangan</p><p id="summaryAmount" class="mt-1 font-semibold">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</p></div>
                    <div><p class="text-white/65">Check-Out Baru</p><p id="summaryCheckout" class="mt-1 font-semibold"></p></div>
                    <div><p class="text-white/65">Total yang harus dibayar</p><p id="summaryTotal" class="mt-1 text-xl font-semibold">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</p></div>
                </div>
            </section>

            @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route($prefix . '.pemesanan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-kazain-border bg-white px-5 text-sm font-semibold text-kazain-text">Batal</a>
                <button id="submitButton" type="submit" class="min-h-11 rounded-xl bg-kazain-primary px-5 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover disabled:cursor-not-allowed disabled:opacity-60">Konfirmasi Extend</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const prices = { five: {{ $booking->room->price_5_hours }}, eight: {{ $booking->room->price_8_hours }}, full: {{ $booking->room->full_day_price }} };
            const currentCheckOut = new Date('{{ $checkout->format('c') }}');
            const formatRupiah = amount => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
            const formatDate = date => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(date) + ' WIB';
            function updateSummary() {
                const type = document.querySelector('input[name="extension_type"]:checked').value;
                const durationInput = document.querySelector('input[name="duration_hours"]:checked');
                const duration = type === 'Full Day' ? 24 : Number(durationInput.value);
                const amount = type === 'Full Day' ? prices.full : duration === 5 ? prices.five : prices.eight;
                const checkout = new Date(currentCheckOut);
                if (type === 'Full Day') { checkout.setDate(checkout.getDate() + 1); checkout.setHours(12, 0, 0, 0); } else { checkout.setHours(checkout.getHours() + duration); }
                document.getElementById('durationSection').classList.toggle('hidden', type === 'Full Day');
                document.getElementById('summaryDuration').textContent = type === 'Full Day' ? 'Full Day' : `${duration} Jam`;
                document.getElementById('summaryAmount').textContent = formatRupiah(amount);
                document.getElementById('summaryTotal').textContent = formatRupiah(amount);
                document.getElementById('summaryCheckout').textContent = formatDate(checkout);
                if (type === 'Full Day') { document.querySelector('input[name="duration_hours"][value="5"]').value = '24'; } else { document.querySelector('input[name="duration_hours"][value="24"]')?.remove(); document.querySelector('input[name="duration_hours"][value="5"]').value = '5'; }
            }
            document.querySelectorAll('input[type="radio"]').forEach(input => input.addEventListener('change', updateSummary));
            document.getElementById('extendForm').addEventListener('submit', event => { const button = document.getElementById('submitButton'); if (button.disabled) { event.preventDefault(); return; } button.disabled = true; button.textContent = 'Menyimpan...'; });
            updateSummary();
        </script>
    @endpush
@endsection
