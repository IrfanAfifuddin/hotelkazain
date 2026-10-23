@extends('layouts.booking')

@section('title', 'Isi Data Diri')
@section('step', '2')
@section('back_label', 'Ketersediaan')
@section('back_url', route('customer.availability'))

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Isi Data Diri</h1>

        {{-- Jika data pemesanan tidak ada / tidak valid --}}
        <div id="invalidBooking" class="hidden mt-8 border border-dashed border-gray-300 rounded-lg p-8 text-center">
            <p class="text-gray-700">Data pemesanan tidak ditemukan. Silakan pilih tanggal dan kamar terlebih dahulu.</p>
            <a href="{{ route('customer.availability') }}" class="mt-4 inline-flex px-5 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800">Cek Ketersediaan</a>
        </div>

        <div id="checkoutContent" class="hidden mt-8 grid gap-8 lg:grid-cols-[1fr_380px] items-start">

            {{-- ===================== FORM ===================== --}}
            <form id="checkoutForm" novalidate class="space-y-6">
                <p id="serverError" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></p>

                <div class="border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 rounded-t-lg">
                        <h2 class="font-semibold text-gray-900">Informasi Pemesan</h2>
                    </div>

                    <div class="p-6 space-y-5">
                        @php
                            $fields = [
                                ['name' => 'customer_name', 'label' => 'Nama Lengkap', 'type' => 'text', 'placeholder' => 'Nama lengkap sesuai identitas', 'autocomplete' => 'name',
                                 'icon' => '<circle cx="12" cy="8" r="5" /><path d="M20 21a8 8 0 0 0-16 0" />'],
                                ['name' => 'phone_number', 'label' => 'Nomor Telepon', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'autocomplete' => 'tel',
                                 'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />'],
                                ['name' => 'email', 'label' => 'Alamat Email', 'type' => 'email', 'placeholder' => 'nama@email.com', 'autocomplete' => 'email',
                                 'icon' => '<rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />'],
                            ];
                        @endphp

                        @foreach ($fields as $field)
                            <div>
                                <label for="{{ $field['name'] }}" class="block font-medium text-gray-800">
                                    {{ $field['label'] }} <span class="text-gray-400">*</span>
                                </label>
                                <div class="mt-2 relative">
                                    <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $field['icon'] !!}</svg>
                                    <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] }}"
                                           placeholder="{{ $field['placeholder'] }}" autocomplete="{{ $field['autocomplete'] }}" required
                                           @if ($field['type'] === 'tel') inputmode="tel" maxlength="16" @endif
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                                </div>
                                <p data-error-for="{{ $field['name'] }}" class="hidden mt-1.5 text-sm text-red-600"></p>
                            </div>
                        @endforeach

                        <label class="flex items-start gap-3 pt-1 cursor-pointer">
                            <input type="checkbox" id="agree_terms" name="agree_terms" value="1" class="mt-1 w-4 h-4 accent-gray-900">
                            <span class="text-gray-700">
                                Saya menyetujui <a href="#" class="underline font-medium text-gray-900">Syarat dan Ketentuan</a>
                                serta <a href="#" class="underline font-medium text-gray-900">Kebijakan Privasi</a> Kazain Hotel.
                            </span>
                        </label>

                        <div class="flex items-start gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                            <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></svg>
                            <span>Kode booking akan dikirimkan ke alamat email yang Anda isi di atas.</span>
                        </div>
                    </div>
                </div>

                {{-- Metode pembayaran --}}
                <div>
                    <p class="font-medium text-gray-800">Metode Pembayaran <span class="text-gray-400">*</span></p>
                    <label class="mt-3 flex items-center gap-4 border-2 border-gray-200 rounded-lg p-5 cursor-pointer transition has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                        <input type="radio" name="payment_method" value="Virtual Account" class="w-4 h-4 accent-gray-900">
                        <span class="w-11 h-11 shrink-0 border border-gray-300 rounded-md bg-white flex items-center justify-center text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 22h18" /><path d="M6 18v-7" /><path d="M10 18v-7" /><path d="M14 18v-7" /><path d="M18 18v-7" /><path d="m12 2 8 5H4z" /></svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-900">Transfer / Virtual Account</span>
                            <span class="block text-sm text-gray-500">Transfer via m-banking, internet banking, atau ATM</span>
                        </span>
            
                </div>

                <button type="submit" id="submitButton" disabled
                        class="w-full py-4 rounded-lg text-lg font-semibold transition bg-gray-100 text-gray-400 cursor-not-allowed">
                    Lanjut ke Pembayaran &rarr;
                </button>
            </form>

            {{-- ===================== RINGKASAN ===================== --}}
            <aside class="border border-gray-200 rounded-lg overflow-hidden lg:sticky lg:top-24">
                <div id="summaryPhoto" class="h-44 bg-gray-100 border-b border-gray-200 flex flex-col items-center justify-center gap-2 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" /><circle cx="9" cy="9" r="2" /><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21" /></svg>
                    <span class="text-sm tracking-wider">FOTO KAMAR</span>
                </div>
                <div class="p-6">
                    <p id="summaryRooms" class="text-lg font-semibold text-gray-900">-</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Check-in</dt><dd id="summaryCheckIn" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Check-out</dt><dd id="summaryCheckOut" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Durasi</dt><dd id="summaryDuration" class="text-right text-gray-900">-</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Tamu</dt><dd id="summaryGuests" class="text-right text-gray-900">-</dd></div>
                    </dl>
                    <div class="mt-5 pt-5 border-t border-gray-200 flex items-end justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">Total Harga</p>
                            <p class="text-xs text-gray-400">Bayar Penuh (Lunas)</p>
                        </div>
                        <p id="summaryTotal" class="text-2xl font-bold text-gray-900">-</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    @include('customer.partials.booking-data')
    <script>
        (function () {
            const KB = window.KazainBooking;
            const AVAILABILITY_URL = @json(route('customer.availability'));
            const BOOKING_URL = @json(route('customer.bookings.store'));

            const booking = KB.parse();
            if (!booking) {
                document.getElementById('invalidBooking').classList.remove('hidden');
                return;
            }
            document.getElementById('checkoutContent').classList.remove('hidden');

            // Tombol kembali membawa pilihan sebelumnya
            const query = KB.bookingQuery(booking);
            document.getElementById('backLink').href = `${AVAILABILITY_URL}?${query}`;

            // ---------- Ringkasan ----------
            document.getElementById('summaryRooms').textContent = booking.roomsText;
            document.getElementById('summaryCheckIn').textContent = KB.formatDateTime(booking.start);
            document.getElementById('summaryCheckOut').textContent = KB.formatDateTime(booking.end);
            document.getElementById('summaryDuration').textContent = booking.durationText;
            document.getElementById('summaryGuests').textContent = `${booking.guests} orang`;
            document.getElementById('summaryTotal').textContent = KB.rupiah(booking.total);
            if (booking.image) {
                document.getElementById('summaryPhoto').innerHTML = `<img src="${booking.image}" alt="Foto kamar" class="w-full h-full object-cover">`;
            }

            // ---------- Validasi form ----------
            const form = document.getElementById('checkoutForm');
            const submitButton = document.getElementById('submitButton');
            const touched = {};

            const rules = {
                customer_name: (v) => v.trim().length >= 3 ? '' : 'Nama lengkap minimal 3 karakter.',
                phone_number: (v) => /^(\+62|62|0)8\d{7,12}$/.test(v.replace(/[\s-]/g, '')) ? '' : 'Nomor telepon tidak valid. Contoh: 08123456789',
                email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Format email tidak valid. Contoh: nama@email.com',
            };

            function validate(showAll = false) {
                let valid = true;
                Object.entries(rules).forEach(([name, rule]) => {
                    const input = form.elements[name];
                    const message = rule(input.value);
                    const errorEl = form.querySelector(`[data-error-for="${name}"]`);
                    const show = message && (touched[name] || showAll);
                    errorEl.textContent = message;
                    errorEl.classList.toggle('hidden', !show);
                    input.classList.toggle('border-red-400', !!show);
                    if (message) valid = false;
                });
                if (!form.elements.agree_terms.checked) valid = false;
                if (!form.querySelector('input[name="payment_method"]:checked')) valid = false;

                submitButton.disabled = !valid;
                submitButton.className = 'w-full py-4 rounded-lg text-lg font-semibold transition ' +
                    (valid ? 'bg-gray-900 hover:bg-gray-800 text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed');
                return valid;
            }

            Object.keys(rules).forEach((name) => {
                form.elements[name].addEventListener('blur', () => { touched[name] = true; validate(); });
                form.elements[name].addEventListener('input', () => validate());
            });
            form.addEventListener('change', () => validate());
            validate();

            // ---------- Submit ----------
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!validate(true)) return;

                const serverError = document.getElementById('serverError');
                serverError.classList.add('hidden');
                submitButton.disabled = true;
                submitButton.textContent = 'Menyiapkan pembayaran...';

                try {
                    const response = await fetch(BOOKING_URL, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            room_id: booking.roomId,
                            customer_name: form.elements.customer_name.value.trim(),
                            phone_number: form.elements.phone_number.value.replace(/[\s-]/g, ''),
                            email: form.elements.email.value.trim(),
                            booking_type: booking.isFull ? 'Full Day' : 'Short Time',
                            check_in_date: booking.date,
                            check_in_time: booking.time,
                            duration_hours: booking.duration,
                            payment_method: form.querySelector('input[name="payment_method"]:checked').value,
                            agree_terms: form.elements.agree_terms.checked,
                        }),
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        const validationMessages = Object.values(result.errors || {}).flat();
                        throw new Error(validationMessages.join(' ') || result.message || 'Booking gagal dibuat.');
                    }

                    window.location.assign(result.data.payment.redirect_url);
                } catch (error) {
                    serverError.textContent = error.message || 'Booking gagal dibuat. Silakan coba kembali.';
                    serverError.classList.remove('hidden');
                    serverError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    submitButton.disabled = false;
                    submitButton.textContent = 'Lanjut ke Pembayaran →';
                }
            });
        })();
    </script>
@endpush