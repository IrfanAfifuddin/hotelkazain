@extends('layouts.app')

@section('title', 'Extend Pemesanan')

@php
    $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';
    $checkout = $booking->check_out_time->copy()->timezone('Asia/Jakarta');
@endphp

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route($prefix . '.pemesanan.index') }}" class="mb-4 inline-flex min-h-10 items-center gap-2 rounded-xl px-3 text-sm font-semibold text-kazain-primary transition hover:bg-[#edf1ec] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">
            <span aria-hidden="true">←</span>
            Data pemesanan
        </a>

        <section class="flex max-h-[calc(100vh-7rem)] min-h-[32rem] flex-col overflow-hidden rounded-2xl border border-kazain-border bg-kazain-surface shadow-[0_24px_70px_-30px_rgba(29,48,39,0.35)]">
            <header class="flex shrink-0 items-center gap-3 border-b border-kazain-border bg-white px-5 py-4 sm:px-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kazain-primary text-[#e8dec3]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base font-semibold tracking-tight text-kazain-text">Extend Pemesanan Kamar</h1>
                    <p class="mt-0.5 text-xs text-kazain-muted">Perpanjangan untuk {{ $booking->booking_code }} · Kamar {{ $booking->room->room_number }}</p>
                </div>
            </header>

            @if ($errors->any())
                <div role="alert" class="mx-5 mt-4 shrink-0 rounded-2xl border border-[#e8c9c2] bg-[#fbf3f1] p-4 text-sm text-[#844a40] sm:mx-6">
                    <p class="font-semibold">Perpanjangan tidak dapat diproses.</p>
                    <p class="mt-1">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route($prefix . '.pemesanan.extend.store', $booking) }}" id="extendForm" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <div class="min-h-0 flex-1 space-y-6 overflow-y-auto p-5 sm:p-6">
                    <section>
                        <h2 class="mb-3 flex items-center gap-1.5 border-b border-kazain-border pb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">
                            <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>Detail Pemesanan</span>
                        </h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p class="mb-1.5 block text-xs font-semibold text-kazain-muted">Nama tamu</p>
                                <div class="flex min-h-10 items-center rounded-xl border border-kazain-border bg-[#f4f5f1] px-3 py-2 text-sm font-medium text-kazain-text">{{ $booking->customer->customer_name ?? '-' }}</div>
                            </div>
                            <div>
                                <p class="mb-1.5 block text-xs font-semibold text-kazain-muted">Waktu check-out saat ini</p>
                                <div class="flex min-h-10 items-center rounded-xl border border-kazain-border bg-[#f4f5f1] px-3 py-2 text-sm font-medium text-kazain-text">{{ $checkout->translatedFormat('d M Y, H:i') }} WIB</div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-3 flex items-center gap-1.5 border-b border-kazain-border pb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">
                            <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <span>Tipe dan Durasi Extend</span>
                        </h2>
                        <div class="space-y-4">
                            <fieldset>
                                <legend class="mb-1.5 block text-xs font-semibold text-kazain-muted">Tipe perpanjangan <span class="text-[#975b50]">*</span></legend>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="extension_type" value="Short Time" checked>
                                        <span class="block text-sm font-semibold text-kazain-text">Short Time</span>
                                        <span class="mt-0.5 block text-xs text-kazain-muted">Pilih tambahan 5 atau 8 jam.</span>
                                    </label>
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="extension_type" value="Full Day">
                                        <span class="block text-sm font-semibold text-kazain-text">Full Day</span>
                                        <span class="mt-0.5 block text-xs text-kazain-muted">Sampai pukul 12.00 hari berikutnya.</span>
                                    </label>
                                </div>
                            </fieldset>
                            <fieldset id="durationSection">
                                <legend class="mb-1.5 block text-xs font-semibold text-kazain-muted">Durasi Short Time <span class="text-[#975b50]">*</span></legend>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="duration_hours" value="5" checked>
                                        <span class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-semibold text-kazain-text">5 Jam</span>
                                            <span class="text-xs font-semibold text-kazain-primary">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</span>
                                        </span>
                                    </label>
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="duration_hours" value="8">
                                        <span class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-semibold text-kazain-text">8 Jam</span>
                                            <span class="text-xs font-semibold text-kazain-primary">Rp {{ number_format($booking->room->price_8_hours, 0, ',', '.') }}</span>
                                        </span>
                                    </label>
                                </div>
                            </fieldset>
                            <input id="fullDayDurationInput" type="hidden" name="duration_hours" value="24" disabled>
                        </div>
                        <p id="fullDayNote" class="mt-3 hidden rounded-xl border border-kazain-border bg-[#f4f5f1] px-3 py-2.5 text-sm text-kazain-muted">Full Day: check-out diperpanjang sampai pukul 12.00 hari berikutnya.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 flex items-center gap-1.5 border-b border-kazain-border pb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">
                            <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75h19.5m-18-12h16.5A2.25 2.25 0 0 1 22.5 9v9.75H1.5V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5h16.5v2.25" />
                            </svg>
                            <span>Pembayaran</span>
                        </h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <fieldset>
                                <legend class="mb-1.5 block text-xs font-semibold text-kazain-muted">Metode pembayaran <span class="text-[#975b50]">*</span></legend>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 text-center transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="payment_method" value="Cash" checked>
                                        <span class="text-sm font-semibold text-kazain-text">Cash</span>
                                    </label>
                                    <label class="cursor-pointer rounded-xl border border-kazain-border bg-white p-3 text-center transition hover:border-kazain-primary/50 has-[:checked]:border-kazain-primary has-[:checked]:bg-[#edf1ec] has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-kazain-primary/15">
                                        <input class="sr-only" type="radio" name="payment_method" value="Transfer">
                                        <span class="text-sm font-semibold text-kazain-text">Transfer</span>
                                    </label>
                                </div>
                            </fieldset>
                            <div>
                                <label for="summaryCheckout" class="mb-1.5 block text-xs font-semibold text-kazain-muted">Perkiraan check-out baru</label>
                                <input id="summaryCheckout" type="text" readonly value="" class="min-h-10 w-full rounded-xl border border-kazain-border bg-[#f4f5f1] px-3 py-2 text-sm font-medium text-kazain-text">
                            </div>
                        </div>
                    </section>
                </div>

                <footer class="flex shrink-0 flex-col gap-4 border-t border-kazain-border bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center justify-between gap-4 sm:justify-start sm:gap-6">
                        <div>
                            <p class="text-xs text-kazain-muted">Durasi</p>
                            <p id="summaryDuration" class="mt-0.5 text-sm font-semibold text-kazain-text">5 Jam</p>
                        </div>
                        <div class="h-9 border-l border-kazain-border"></div>
                        <div>
                            <p class="text-xs text-kazain-muted">Total biaya extend</p>
                            <p id="summaryTotal" class="mt-0.5 text-lg font-semibold tracking-tight text-kazain-primary">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</p>
                            <p id="summaryAmount" class="sr-only">Rp {{ number_format($booking->room->price_5_hours, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <a href="{{ route($prefix . '.pemesanan.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-kazain-border bg-white px-5 text-sm font-semibold text-kazain-text transition hover:bg-[#f4f5f1] focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15">Batal</a>
                        <button id="submitButton" type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-kazain-primary px-5 text-sm font-semibold text-white transition hover:bg-kazain-primary-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20 disabled:cursor-not-allowed disabled:opacity-60">Konfirmasi Extend</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    @push('scripts')
        <script>
            const prices = { five: {{ $booking->room->price_5_hours }}, eight: {{ $booking->room->price_8_hours }}, full: {{ $booking->room->full_day_price }} };
            const currentCheckOut = new Date('{{ $checkout->format('c') }}');
            const formatRupiah = amount => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
            const formatDate = date => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(date) + ' WIB';
            function updateSummary() {
                const type = document.querySelector('input[name="extension_type"]:checked').value;
                const durationInputs = document.querySelectorAll('input[name="duration_hours"][type="radio"]');
                const selectedDuration = document.querySelector('input[name="duration_hours"][type="radio"]:checked');
                const duration = type === 'Full Day' ? 24 : Number(selectedDuration.value);
                const amount = type === 'Full Day' ? prices.full : duration === 5 ? prices.five : prices.eight;
                const checkout = new Date(currentCheckOut);
                if (type === 'Full Day') { checkout.setDate(checkout.getDate() + 1); checkout.setHours(12, 0, 0, 0); } else { checkout.setHours(checkout.getHours() + duration); }
                durationInputs.forEach(input => { input.disabled = type === 'Full Day'; });
                document.getElementById('fullDayDurationInput').disabled = type !== 'Full Day';
                document.getElementById('durationSection').classList.toggle('hidden', type === 'Full Day');
                document.getElementById('fullDayNote').classList.toggle('hidden', type !== 'Full Day');
                document.getElementById('summaryDuration').textContent = type === 'Full Day' ? 'Full Day' : `${duration} Jam`;
                document.getElementById('summaryAmount').textContent = formatRupiah(amount);
                document.getElementById('summaryTotal').textContent = formatRupiah(amount);
                document.getElementById('summaryCheckout').textContent = formatDate(checkout);
            }
            document.querySelectorAll('input[name="extension_type"], input[name="duration_hours"][type="radio"]').forEach(input => input.addEventListener('change', updateSummary));
            document.getElementById('extendForm').addEventListener('submit', event => { const button = document.getElementById('submitButton'); if (button.disabled) { event.preventDefault(); return; } button.disabled = true; button.textContent = 'Menyimpan...'; });
            updateSummary();
        </script>
    @endpush
@endsection
