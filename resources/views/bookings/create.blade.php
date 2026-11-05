@extends('layouts.app')

@section('title', 'Tambah Pemesanan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-kazain-text">Tambah Pemesanan</h1>
        <p class="text-sm text-kazain-muted mt-1">Buat reservasi kamar baru untuk tamu.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-kazain-surface border border-kazain-border rounded-2xl p-6 shadow-sm max-w-3xl">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.store') : route('receptionist.pemesanan.store') }}" method="POST" class="space-y-6" id="bookingForm">
            @csrf
            
            {{-- Hidden field untuk booking_type --}}
            <input type="hidden" name="booking_type" id="booking_type" value="{{ old('booking_type', 'Full Day') }}">

            {{-- Data Customer --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Data Customer</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Nama Customer *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition placeholder:text-kazain-muted/70 focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Nomor Telepon *</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition placeholder:text-kazain-muted/70 focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Email <span class="font-normal text-kazain-muted/70">(opsional)</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition placeholder:text-kazain-muted/70 focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    </div>
                </div>
            </div>

            {{-- Harga Kamar - Duration Cards --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Harga Kamar</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Short Time Card --}}
                    <button type="button" onclick="selectDurationType('shorttime')" id="shorttime-card" class="duration-card relative flex flex-col items-start justify-between rounded-2xl border-2 border-kazain-border bg-kazain-surface p-5 text-left transition hover:border-kazain-primary/40 hover:bg-gray-50 focus:outline-none">
                        <div class="w-full">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-base font-semibold text-kazain-text">Short Time</h4>
                                <div class="h-5 w-5 rounded-full border-2 border-kazain-border bg-white transition-colors duration-200" id="shorttime-radio"></div>
                            </div>
                            <p class="text-xs text-kazain-muted">5 jam / 8 jam</p>
                        </div>
                    </button>

                    {{-- Full Day Card --}}
                    <button type="button" onclick="selectDurationType('fullday')" id="fullday-card" class="duration-card relative flex flex-col items-start justify-between rounded-2xl border-2 border-kazain-primary bg-kazain-surface p-5 text-left transition hover:border-kazain-primary/40 hover:bg-gray-50 focus:outline-none">
                        <div class="w-full">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-base font-semibold text-kazain-text">Full Day</h4>
                                <div class="h-5 w-5 rounded-full border-2 border-kazain-primary bg-kazain-primary flex items-center justify-center transition-colors duration-200" id="fullday-radio">
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                            </div>
                            <p class="text-xs text-kazain-muted" id="fullday-price">Rp 700.000 / hari</p>
                        </div>
                    </button>
                </div>

                {{-- Hour Selection (for Short Time only) --}}
                <div id="hour-selection" class="hidden grid grid-cols-2 gap-3">
                    <button type="button" onclick="selectHourDuration('5')" id="hour-5-btn" class="hour-btn rounded-xl border-2 border-kazain-border bg-kazain-surface px-4 py-3 text-left transition hover:border-kazain-primary/40 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-kazain-text">5 Jam</p>
                                <p class="text-xs text-kazain-muted" id="hour-5-price">Rp 350.000</p>
                            </div>
                        </div>
                    </button>
                    <button type="button" onclick="selectHourDuration('8')" id="hour-8-btn" class="hour-btn rounded-xl border-2 border-kazain-border bg-kazain-surface px-4 py-3 text-left transition hover:border-kazain-primary/40 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-kazain-text">8 Jam</p>
                                <p class="text-xs text-kazain-muted" id="hour-8-price">Rp 600.000</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Jam Check-In / Check-Out --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Jam Check-In / Check-Out</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Check-In</label>
                        <input type="datetime-local" name="check_in_time" id="check_in_time" min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" value="{{ old('check_in_time', now('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" required onchange="calculateCheckOut()" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                        <p id="checkInError" class="hidden mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Check-Out</label>
                        <input type="text" id="check_out_preview" readonly value="-" class="min-h-11 w-full cursor-not-allowed rounded-xl border border-kazain-border bg-gray-50 px-3.5 py-2.5 text-sm font-medium text-kazain-muted">
                        <p class="mt-1.5 text-xs text-kazain-muted/70">Isi jam check-in untuk melihat kamar yang tersedia.</p>
                    </div>
                </div>
            </div>

            {{-- No. & Tipe Kamar --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">No. & Tipe Kamar</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Pilih kamar tersedia *</label>
                        <select name="room_id" id="room_id" required onchange="updateRoomPrice()" class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                            <option value="">-- Pilih Kamar (0 tersedia) --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" 
                                    data-room-type="{{ $room->room_type }}"
                                    data-price-5="{{ $room->price_5_hours }}"
                                    data-price-8="{{ $room->price_8_hours }}"
                                    data-price-full="{{ $room->full_day_price }}"
                                    {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    Kamar {{ $room->room_number }} — {{ $room->room_type }} Bed (Tersedia)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-kazain-muted mb-1.5">Tipe kamar</label>
                        <input type="text" id="room_type_display" readonly value="Pilih kamar terlebih dahulu" class="min-h-11 w-full cursor-not-allowed rounded-xl border border-kazain-border bg-gray-50 px-3.5 py-2.5 text-sm font-medium text-kazain-muted">
                    </div>
                </div>
            </div>

            {{-- Jumlah Orang & Kamar --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Jumlah Orang & Kamar</h3>
                <p class="text-xs text-kazain-muted">1 kamar maks. 2 orang. Harga × jumlah kamar.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-kazain-border bg-kazain-surface p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-kazain-muted">Jumlah Orang</p>
                        <div class="flex items-center justify-between">
                            <button type="button" onclick="changeQuantity('guest', -1)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-kazain-border bg-gray-50 text-kazain-text transition hover:bg-gray-100">−</button>
                            <span class="text-3xl font-semibold text-kazain-text" id="guest_count">1</span>
                            <button type="button" onclick="changeQuantity('guest', 1)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-kazain-border bg-gray-50 text-kazain-text transition hover:bg-gray-100">+</button>
                        </div>
                        <p class="mt-2 text-center text-xs text-kazain-muted">orang</p>
                    </div>
                    <div class="rounded-xl border border-kazain-border bg-kazain-surface p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-kazain-muted">Jumlah Kamar</p>
                        <div class="flex items-center justify-between">
                            <button type="button" onclick="changeQuantity('room', -1)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-kazain-border bg-gray-50 text-kazain-text transition hover:bg-gray-100">−</button>
                            <span class="text-3xl font-semibold text-kazain-text" id="room_count">1</span>
                            <button type="button" onclick="changeQuantity('room', 1)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-kazain-border bg-gray-50 text-kazain-text transition hover:bg-gray-100">+</button>
                        </div>
                        <p class="mt-2 text-center text-xs text-kazain-muted">kamar</p>
                    </div>
                </div>
            </div>

            {{-- Metode Pembayaran --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Metode Pembayaran</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Cash Card --}}
                    <button type="button" onclick="selectPaymentMethod('Cash')" id="payment-cash" class="payment-card relative flex items-center gap-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50 p-4 text-left transition hover:bg-emerald-100 focus:outline-none">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v8.25m0-8.25a60.07 60.07 0 0115.797-2.101c.727-.198 1.453.342 1.453 1.096V6m-18 8.25h16.5m0 0a2.25 2.25 0 002.25-2.25V6m0 8.25a2.25 2.25 0 01-2.25 2.25m-16.5 0a2.25 2.25 0 002.25-2.25V6" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-emerald-900">Cash</h4>
                            <p class="text-xs text-emerald-700">Bayar tunai</p>
                        </div>
                        <div class="h-5 w-5 rounded-full border-2 border-emerald-500 bg-emerald-500 flex items-center justify-center" id="cash-radio">
                            <div class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                    </button>

                    {{-- Transfer Card --}}
                    <button type="button" onclick="selectPaymentMethod('Transfer')" id="payment-transfer" class="payment-card relative flex items-center gap-4 rounded-2xl border-2 border-kazain-border bg-kazain-surface p-4 text-left transition hover:border-kazain-primary/40 hover:bg-gray-50 focus:outline-none">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100">
                            <svg class="h-6 w-6 text-kazain-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-kazain-text">Transfer</h4>
                            <p class="text-xs text-kazain-muted">Bank transfer</p>
                        </div>
                        <div class="h-5 w-5 rounded-full border-2 border-kazain-border bg-white" id="transfer-radio"></div>
                    </button>
                </div>
                <input type="hidden" name="payment_method" id="payment_method" value="Cash" required>
            </div>

            {{-- Status Pemesanan --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-kazain-text">Status Pemesanan</h3>
                <select name="booking_status" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    <option value="Confirmed" {{ old('booking_status', 'Confirmed') == 'Confirmed' ? 'selected' : '' }}>Confirmed (Reservasi)</option>
                    <option value="Check-in" {{ old('booking_status') == 'Check-in' ? 'selected' : '' }}>Check-in (Tamu Masuk)</option>
                </select>
            </div>

            {{-- Total Price Display --}}
            <div class="rounded-2xl bg-kazain-primary p-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-white/80">Total biaya pemesanan</p>
                        <p class="mt-0.5 text-xs text-white/70">× <span id="total_room_multiplier">1</span> kamar</p>
                    </div>
                    <p class="text-2xl font-bold text-white" id="total_price_display">Rp 0</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-kazain-border">
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="px-5 py-2.5 border border-kazain-border rounded-xl text-sm font-medium text-kazain-muted hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-kazain-primary hover:bg-kazain-primary-hover text-white text-sm font-semibold rounded-xl transition shadow-sm">Simpan Pemesanan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    let currentDurationType = 'fullday'; // fullday or shorttime
    let currentHourDuration = null; // 5 or 8 (for shorttime)
    let guestCount = 1;
    let roomCount = 1;
    let currentRoomPrice = { price5: 0, price8: 0, priceFull: 0 };

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateRoomPrice();
        calculateCheckOut();
        updateTotalPrice();
    });

    function selectDurationType(type) {
        currentDurationType = type;
        
        const shorttimeCard = document.getElementById('shorttime-card');
        const fulldayCard = document.getElementById('fullday-card');
        const shorttimeRadio = document.getElementById('shorttime-radio');
        const fulldayRadio = document.getElementById('fullday-radio');
        const hourSelection = document.getElementById('hour-selection');
        
        if (type === 'shorttime') {
            // Activate Short Time
            shorttimeCard.classList.add('border-kazain-primary', 'bg-gray-50');
            shorttimeCard.classList.remove('border-kazain-border');
            shorttimeRadio.classList.add('border-kazain-primary', 'bg-kazain-primary');
            shorttimeRadio.classList.remove('border-kazain-border', 'bg-white');
            shorttimeRadio.innerHTML = '<div class="h-2 w-2 rounded-full bg-white"></div>';
            
            // Deactivate Full Day
            fulldayCard.classList.remove('border-kazain-primary', 'bg-gray-50');
            fulldayCard.classList.add('border-kazain-border');
            fulldayRadio.classList.remove('border-kazain-primary', 'bg-kazain-primary');
            fulldayRadio.classList.add('border-kazain-border', 'bg-white');
            fulldayRadio.innerHTML = '';
            
            // Show hour selection
            hourSelection.classList.remove('hidden');
            
            // Auto-select 5 hours if not selected
            if (!currentHourDuration) {
                selectHourDuration('5');
            }
        } else {
            // Activate Full Day
            fulldayCard.classList.add('border-kazain-primary', 'bg-gray-50');
            fulldayCard.classList.remove('border-kazain-border');
            fulldayRadio.classList.add('border-kazain-primary', 'bg-kazain-primary');
            fulldayRadio.classList.remove('border-kazain-border', 'bg-white');
            fulldayRadio.innerHTML = '<div class="h-2 w-2 rounded-full bg-white"></div>';
            
            // Deactivate Short Time
            shorttimeCard.classList.remove('border-kazain-primary', 'bg-gray-50');
            shorttimeCard.classList.add('border-kazain-border');
            shorttimeRadio.classList.remove('border-kazain-primary', 'bg-kazain-primary');
            shorttimeRadio.classList.add('border-kazain-border', 'bg-white');
            shorttimeRadio.innerHTML = '';
            
            // Hide hour selection
            hourSelection.classList.add('hidden');
            currentHourDuration = null;
            
            document.getElementById('booking_type').value = 'Full Day';
        }
        
        calculateCheckOut();
        updateTotalPrice();
    }

    function selectHourDuration(hours) {
        currentHourDuration = hours;
        
        const hour5Btn = document.getElementById('hour-5-btn');
        const hour8Btn = document.getElementById('hour-8-btn');
        
        // Reset both
        hour5Btn.classList.remove('border-kazain-primary', 'bg-gray-50');
        hour5Btn.classList.add('border-kazain-border');
        hour8Btn.classList.remove('border-kazain-primary', 'bg-gray-50');
        hour8Btn.classList.add('border-kazain-border');
        
        if (hours === '5') {
            hour5Btn.classList.add('border-kazain-primary', 'bg-gray-50');
            hour5Btn.classList.remove('border-kazain-border');
            document.getElementById('booking_type').value = 'Short Time 5 Jam';
        } else {
            hour8Btn.classList.add('border-kazain-primary', 'bg-gray-50');
            hour8Btn.classList.remove('border-kazain-border');
            document.getElementById('booking_type').value = 'Short Time 8 Jam';
        }
        
        calculateCheckOut();
        updateTotalPrice();
    }

    function selectPaymentMethod(method) {
        const cashCard = document.getElementById('payment-cash');
        const transferCard = document.getElementById('payment-transfer');
        const cashRadio = document.getElementById('cash-radio');
        const transferRadio = document.getElementById('transfer-radio');
        
        if (method === 'Cash') {
            // Activate Cash
            cashCard.classList.add('border-emerald-500', 'bg-emerald-50');
            cashCard.classList.remove('border-kazain-border', 'bg-kazain-surface');
            cashRadio.classList.add('border-emerald-500', 'bg-emerald-500');
            cashRadio.classList.remove('border-kazain-border', 'bg-white');
            cashRadio.innerHTML = '<div class="h-2 w-2 rounded-full bg-white"></div>';
            
            // Deactivate Transfer
            transferCard.classList.remove('border-blue-500', 'bg-blue-50');
            transferCard.classList.add('border-kazain-border', 'bg-kazain-surface');
            transferRadio.classList.remove('border-blue-500', 'bg-blue-500');
            transferRadio.classList.add('border-kazain-border', 'bg-white');
            transferRadio.innerHTML = '';
        } else {
            // Activate Transfer
            transferCard.classList.add('border-blue-500', 'bg-blue-50');
            transferCard.classList.remove('border-kazain-border', 'bg-kazain-surface');
            transferRadio.classList.add('border-blue-500', 'bg-blue-500');
            transferRadio.classList.remove('border-kazain-border', 'bg-white');
            transferRadio.innerHTML = '<div class="h-2 w-2 rounded-full bg-white"></div>';
            
            // Deactivate Cash
            cashCard.classList.remove('border-emerald-500', 'bg-emerald-50');
            cashCard.classList.add('border-kazain-border', 'bg-kazain-surface');
            cashRadio.classList.remove('border-emerald-500', 'bg-emerald-500');
            cashRadio.classList.add('border-kazain-border', 'bg-white');
            cashRadio.innerHTML = '';
        }
        
        document.getElementById('payment_method').value = method;
    }

    function changeQuantity(type, delta) {
        if (type === 'guest') {
            guestCount = Math.max(1, Math.min(10, guestCount + delta));
            document.getElementById('guest_count').textContent = guestCount;
        } else {
            roomCount = Math.max(1, Math.min(5, roomCount + delta));
            document.getElementById('room_count').textContent = roomCount;
            document.getElementById('total_room_multiplier').textContent = roomCount;
        }
        updateTotalPrice();
    }

    function updateRoomPrice() {
        const roomSelect = document.getElementById('room_id');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const roomTypeDisplay = document.getElementById('room_type_display');
        
        if (selectedOption && selectedOption.value) {
            const roomType = selectedOption.getAttribute('data-room-type') || 'Standard';
            roomTypeDisplay.value = roomType + ' Bed';
            
            currentRoomPrice.price5 = parseFloat(selectedOption.getAttribute('data-price-5')) || 0;
            currentRoomPrice.price8 = parseFloat(selectedOption.getAttribute('data-price-8')) || 0;
            currentRoomPrice.priceFull = parseFloat(selectedOption.getAttribute('data-price-full')) || 0;
            
            // Update price displays
            document.getElementById('hour-5-price').textContent = 'Rp ' + currentRoomPrice.price5.toLocaleString('id-ID');
            document.getElementById('hour-8-price').textContent = 'Rp ' + currentRoomPrice.price8.toLocaleString('id-ID');
            document.getElementById('fullday-price').textContent = 'Rp ' + currentRoomPrice.priceFull.toLocaleString('id-ID') + ' / hari';
        } else {
            roomTypeDisplay.value = 'Pilih kamar terlebih dahulu';
            currentRoomPrice = { price5: 0, price8: 0, priceFull: 0 };
        }
        
        updateTotalPrice();
    }

    function updateTotalPrice() {
        let price = 0;
        
        if (currentDurationType === 'fullday') {
            price = currentRoomPrice.priceFull;
        } else if (currentDurationType === 'shorttime') {
            if (currentHourDuration === '5') {
                price = currentRoomPrice.price5;
            } else if (currentHourDuration === '8') {
                price = currentRoomPrice.price8;
            }
        }
        
        const totalPrice = price * roomCount;
        document.getElementById('total_price_display').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
    }

    function calculateCheckOut() {
        const checkInInput = document.getElementById('check_in_time');
        const checkOutPreview = document.getElementById('check_out_preview');
        
        if (!checkInInput.value) {
            checkOutPreview.value = '-';
            return;
        }
        
        const checkInDate = new Date(checkInInput.value);
        if (isNaN(checkInDate.getTime())) {
            checkOutPreview.value = '-';
            return;
        }
        
        let checkOutDate = new Date(checkInDate.getTime());
        
        if (currentDurationType === 'shorttime' && currentHourDuration) {
            const hours = parseInt(currentHourDuration);
            checkOutDate.setHours(checkOutDate.getHours() + hours);
        } else if (currentDurationType === 'fullday') {
            checkOutDate.setDate(checkOutDate.getDate() + 1);
            checkOutDate.setHours(12, 0, 0, 0);
        }
        
        // Format output
        const day = String(checkOutDate.getDate()).padStart(2, '0');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const month = months[checkOutDate.getMonth()];
        const year = checkOutDate.getFullYear();
        const hours = String(checkOutDate.getHours()).padStart(2, '0');
        const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');
        
        checkOutPreview.value = `${day} ${month} ${year} ${hours}:${minutes}`;
        
        // Validate check-in time
        const now = new Date();
        const valid = checkInDate >= now;
        const error = document.getElementById('checkInError');
        checkInInput.setCustomValidity(valid ? '' : 'Waktu check-in tidak boleh sudah berlalu.');
        error.textContent = valid ? '' : 'Waktu check-in tidak boleh sudah berlalu. Pilih waktu sekarang atau setelahnya.';
        error.classList.toggle('hidden', valid);
    }

    // Add check-in validation on input
    document.getElementById('check_in_time').addEventListener('change', calculateCheckOut);
    document.getElementById('check_in_time').addEventListener('input', calculateCheckOut);
</script>
@endpush
