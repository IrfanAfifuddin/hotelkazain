@extends('layouts.app')

@section('title', 'Edit Pemesanan')

@section('content')

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-kazain-text">Edit Pemesanan</h1>
        <p class="text-sm text-kazain-muted mt-1">Perbarui detail pemesanan <span class="font-semibold text-kazain-text">{{ $booking->booking_code }}</span>.</p>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <p class="font-semibold mb-1">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mx-auto w-full max-w-3xl rounded-2xl border border-kazain-border bg-kazain-surface p-4 shadow-sm sm:p-6">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.update', $booking->id) : route('receptionist.pemesanan.update', $booking->id) }}" method="POST" class="space-y-6" id="editBookingForm">
            @csrf
            @method('PUT')

            {{-- Data Customer --}}
            <div class="space-y-4 rounded-xl border border-kazain-border bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-kazain-text flex items-center gap-2">
                    <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    Data Customer
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $booking->customer->customer_name ?? '') }}" required pattern="[A-Za-z\s]+" title="Hanya boleh berisi huruf dan spasi" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone_number" value="{{ old('phone_number', $booking->customer->phone_number ?? '') }}" required minlength="10" maxlength="15" pattern="[0-9]{10,15}" title="Nomor telepon harus berupa angka antara 10 sampai 15 digit" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $booking->customer->email ?? '') }}" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                </div>
            </div>

            {{-- Tipe & Durasi --}}
            <div class="space-y-4 rounded-xl border border-kazain-border bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-kazain-text flex items-center gap-2">
                    <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5Z"/></svg>
                    Tipe & Durasi Pemesanan
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Tipe Durasi <span class="text-red-500">*</span></label>
                        <select name="booking_type" id="edit_booking_type" required onchange="updateEditCalculations()" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                            <option value="Short Time 5 Jam" {{ $booking->booking_type == 'Short Time 5 Jam' ? 'selected' : '' }}>Short Time (5 Jam)</option>
                            <option value="Short Time 8 Jam" {{ $booking->booking_type == 'Short Time 8 Jam' ? 'selected' : '' }}>Short Time (8 Jam)</option>
                            <option value="Full Day" {{ $booking->booking_type == 'Full Day' ? 'selected' : '' }}>Full Day (Sampai 12:00 Hari Berikutnya)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Jumlah Tamu <span class="text-red-500">*</span></label>
                        <input type="number" name="number_of_guests" id="edit_number_of_guests" min="1" max="{{ max(2, $booking->rooms->count() * 2) }}" value="{{ old('number_of_guests', $booking->number_of_guests ?? 1) }}" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Waktu Check-in <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="check_in_time" id="edit_check_in_time" value="{{ old('check_in_time', $booking->check_in_time->format('Y-m-d\TH:i') ?? '') }}" required onchange="updateEditCalculations()" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Waktu Check-out <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="check_out_time" id="edit_check_out_time" value="{{ old('check_out_time', $booking->check_out_time->format('Y-m-d\TH:i') ?? '') }}" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                </div>
            </div>

            {{-- Pilih Kamar --}}
            <div class="space-y-4 rounded-xl border border-kazain-border bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-kazain-text flex items-center gap-2">
                    <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5Z"/></svg>
                    Pilih Kamar (minimal 1)
                </h3>
                <p class="text-xs text-kazain-muted">Kamar yang sedang dipesan ditandai. Pilih kamar lain jika ingin mengganti.</p>
                <div id="edit-rooms-container" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($rooms as $room)
                        @php
                            $isCurrentlyBooked = $booking->rooms->contains('id', $room->id);
                            $roomPrice = match(true) {
                                $booking->booking_type === 'Short Time 5 Jam' => $room->price_5_hours,
                                $booking->booking_type === 'Short Time 8 Jam' => $room->price_8_hours,
                                default => $room->full_day_price,
                            };
                        @endphp
                        <label class="relative cursor-pointer rounded-xl border-2 transition hover:border-kazain-primary/40 hover:bg-gray-50 {{ $isCurrentlyBooked ? 'border-kazain-primary bg-gray-50' : 'border-kazain-border bg-kazain-surface' }}" data-room-id="{{ $room->id }}" data-price-5="{{ $room->price_5_hours }}" data-price-8="{{ $room->price_8_hours }}" data-price-full="{{ $room->full_day_price }}">
                            <input type="checkbox" name="room_ids[]" value="{{ $room->id }}" {{ $isCurrentlyBooked ? 'checked' : '' }} class="sr-only" onchange="updateEditRoomSelection()">
                            <div class="p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-kazain-text">No. {{ $room->room_number }}</span>
                                    <div class="h-4 w-4 rounded border-2 transition-colors {{ $isCurrentlyBooked ? 'bg-kazain-primary border-kazain-primary' : 'bg-white border-kazain-border' }}">
                                        @if($isCurrentlyBooked)
                                            <svg class="h-3 w-3 text-white mx-auto mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-kazain-muted">{{ $room->room_type }} Bed</p>
                                <p class="text-xs text-kazain-primary mt-1" id="edit-price-{{ $room->id }}">Rp {{ number_format($roomPrice, 0, ',', '.') }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p id="edit-selected-rooms-summary" class="mt-2 text-xs text-kazain-muted">
                    @if($booking->rooms->count() > 0)
                        Kamar dipilih: {{ $booking->rooms->pluck('room_number')->map(fn($n) => 'No. '.$n)->join(', ') }}
                    @else
                        Pilih minimal 1 kamar
                    @endif
                </p>
            </div>

            {{-- Status & Pembayaran --}}
            <div class="space-y-4 rounded-xl border border-kazain-border bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-kazain-text flex items-center gap-2">
                    <svg class="h-4 w-4 text-kazain-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0-8.25a60.07 60.07 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096V6m-18 8.25h16.5m0 0a2.25 2.25 0 0 0 2.25-2.25V6m0 8.25a2.25 2.25 0 0 1-2.25 2.25m-16.5 0a2.25 2.25 0 0 0 2.25-2.25V6"/></svg>
                    Status & Pembayaran
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Status Pemesanan <span class="text-red-500">*</span></label>
                        <select name="booking_status" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                            <option value="Pending" {{ $booking->booking_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Confirmed" {{ $booking->booking_status == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="Check-in" {{ $booking->booking_status == 'Check-in' ? 'selected' : '' }}>Check-in</option>
                            <option value="Check-out" {{ $booking->booking_status == 'Check-out' ? 'selected' : '' }}>Check-out</option>
                            <option value="Cancelled" {{ $booking->booking_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Status Pembayaran <span class="text-red-500">*</span></label>
                        <select name="payment_status" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                            <option value="Pending" {{ $booking->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Paid" {{ $booking->payment_status == 'Paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                            <option value="Failed" {{ $booking->payment_status == 'Failed' ? 'selected' : '' }}>Failed</option>
                            <option value="Cancelled" {{ $booking->payment_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
                        <select name="payment_method" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                            <option value="Cash" {{ $booking->payment_method == 'Cash' ? 'selected' : '' }}>Cash / Tunai</option>
                            <option value="Transfer" {{ $booking->payment_method == 'Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Harga --}}
            <div class="rounded-xl bg-kazain-primary p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-white/80">Total Biaya Pemesanan</p>
                        <p class="text-xs text-white/65">Dihitung otomatis berdasarkan kamar & durasi</p>
                    </div>
                    <div class="text-right">
                        <span id="edit_total_price_display" class="text-xl font-semibold tracking-tight text-white">
                            Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-kazain-border pt-4 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-kazain-border px-5 py-2.5 text-sm font-medium text-kazain-muted transition hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="min-h-11 rounded-xl bg-kazain-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-kazain-primary-hover">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- JavaScript for realtime calculations --}}
    <script>
        // Room prices map
        const editRoomPricesMap = {};
        document.querySelectorAll('[id^="edit-price-"]').forEach(el => {
            const roomId = el.id.replace('edit-price-', '');
            const card = document.querySelector(`[data-room-id="${roomId}"]`);
            if (card) {
                editRoomPricesMap[roomId] = {
                    price5: parseFloat(card.getAttribute('data-price-5')) || 0,
                    price8: parseFloat(card.getAttribute('data-price-8')) || 0,
                    priceFull: parseFloat(card.getAttribute('data-price-full')) || 0,
                };
            }
        });

        let editSelectedRoomIds = Array.from(document.querySelectorAll('input[name="room_ids[]"]:checked')).map(el => el.value);

        // Update room selection UI
        function updateEditRoomSelection() {
            editSelectedRoomIds = Array.from(document.querySelectorAll('input[name="room_ids[]"]:checked')).map(el => el.value);

            document.querySelectorAll('[id^="edit-price-"]').forEach(el => {
                const roomId = el.id.replace('edit-price-', '');
                const card = document.querySelector(`[data-room-id="${roomId}"]`);
                const checkbox = card?.querySelector('input[name="room_ids[]"]');
                const isChecked = checkbox?.checked;
                const check = card?.querySelector('.rounded.border-2');

                if (isChecked) {
                    card.classList.add('border-kazain-primary', 'bg-gray-50');
                    card.classList.remove('border-kazain-border', 'bg-kazain-surface');
                    check?.classList.add('bg-kazain-primary', 'border-kazain-primary');
                    check?.classList.remove('bg-white', 'border-kazain-border');
                    check?.querySelector('svg')?.classList.remove('hidden');
                } else {
                    card.classList.remove('border-kazain-primary', 'bg-gray-50');
                    card.classList.add('border-kazain-border', 'bg-kazain-surface');
                    check?.classList.remove('bg-kazain-primary', 'border-kazain-primary');
                    check?.classList.add('bg-white', 'border-kazain-border');
                    check?.querySelector('svg')?.classList.add('hidden');
                }
            });

            // Update summary
            const summaryEl = document.getElementById('edit-selected-rooms-summary');
            if (editSelectedRoomIds.length > 0) {
                const roomNumbers = editSelectedRoomIds
                    .map(id => {
                        const card = document.querySelector(`[data-room-id="${id}"]`);
                        return 'No. ' + card?.getAttribute('data-room-number');
                    })
                    .filter(Boolean)
                    .join(', ');
                summaryEl.textContent = `Kamar dipilih: ${roomNumbers}`;
            } else {
                summaryEl.textContent = 'Pilih minimal 1 kamar';
            }

            updateEditCalculations();
        }

        // Recalculate total price and check-out time
        function updateEditCalculations() {
            const bookingType = document.getElementById('edit_booking_type').value;
            const checkInInput = document.getElementById('edit_check_in_time');
            const checkOutInput = document.getElementById('edit_check_out_time');
            const totalPriceDisplay = document.getElementById('edit_total_price_display');

            // 1. Calculate total price from all selected rooms
            let totalPrice = 0;
            editSelectedRoomIds.forEach(roomId => {
                const prices = editRoomPricesMap[roomId];
                if (!prices) return;

                let roomPrice = 0;
                if (bookingType === 'Short Time 5 Jam') {
                    roomPrice = prices.price5;
                } else if (bookingType === 'Short Time 8 Jam') {
                    roomPrice = prices.price8;
                } else {
                    roomPrice = prices.priceFull;
                }
                totalPrice += roomPrice;

                // Update individual room price display
                const priceEl = document.getElementById('edit-price-' + roomId);
                if (priceEl) {
                    priceEl.textContent = 'Rp ' + roomPrice.toLocaleString('id-ID');
                }
            });

            totalPriceDisplay.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');

            // 2. Auto-update check-out time based on check-in + duration
            if (checkInInput.value) {
                const checkInDate = new Date(checkInInput.value);
                if (!isNaN(checkInDate.getTime())) {
                    let checkOutDate = new Date(checkInDate.getTime());

                    if (bookingType === 'Short Time 5 Jam') {
                        checkOutDate.setHours(checkOutDate.getHours() + 5);
                    } else if (bookingType === 'Short Time 8 Jam') {
                        checkOutDate.setHours(checkOutDate.getHours() + 8);
                    } else {
                        // Full Day: check-out pukul 12:00 — hari yang sama bila check-in sebelum pukul 12:00,
                        // keesokan hari bila check-in pada/di atas pukul 12:00
                        if (checkInDate.getHours() >= 12) {
                            checkOutDate.setDate(checkOutDate.getDate() + 1);
                        }
                        checkOutDate.setHours(12, 0, 0, 0);
                    }

                    // Format for datetime-local input
                    const year = checkOutDate.getFullYear();
                    const month = String(checkOutDate.getMonth() + 1).padStart(2, '0');
                    const day = String(checkOutDate.getDate()).padStart(2, '0');
                    const hours = String(checkOutDate.getHours()).padStart(2, '0');
                    const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');

                    checkOutInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                }
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            updateEditRoomSelection();
        });
    </script>

@endsection
