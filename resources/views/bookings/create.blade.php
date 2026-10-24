@extends('layouts.app')

@section('title', 'Tambah Pemesanan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Pemesanan</h1>
        <p class="text-sm text-gray-500 mt-1">Buat reservasi kamar baru.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm max-w-3xl">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.store') : route('receptionist.pemesanan.store') }}" method="POST" class="space-y-6" onsubmit="return validateCheckInTime(document.getElementById('check_in_time'))">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Customer *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kamar *</label>
                    <select name="room_id" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                Kamar {{ $room->room_number }} ({{ $room->room_type }}) — Status: {{ $room->room_status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Pemesanan *</label>
                    <select name="booking_type" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                        <option value="Full Day" {{ old('booking_type') == 'Full Day' ? 'selected' : '' }}>Full Day</option>
                        <option value="Short Time 5 Jam" {{ old('booking_type') == 'Short Time 5 Jam' ? 'selected' : '' }}>Short Time (5 Jam)</option>
                        <option value="Short Time 8 Jam" {{ old('booking_type') == 'Short Time 8 Jam' ? 'selected' : '' }}>Short Time (8 Jam)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Jam Check-in *</label>
                    <input type="datetime-local" name="check_in_time" id="check_in_time" min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" value="{{ old('check_in_time', now('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" required onchange="validateCheckInTime(this)" oninput="validateCheckInTime(this)" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                    <p id="checkInError" class="hidden mt-1 text-xs text-red-600"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Metode Pembayaran *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                        <option value="Cash">Cash / Tunai</option>
                        <option value="Transfer">Transfer Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status Pemesanan *</label>
                    <select name="booking_status" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm">
                        <option value="Confirmed" {{ old('booking_status', 'Confirmed') == 'Confirmed' ? 'selected' : '' }}>Confirmed (Reservasi)</option>
                        <option value="Check-in" {{ old('booking_status') == 'Check-in' ? 'selected' : '' }}>Check-in (Tamu Masuk)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Simpan Pemesanan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function validateCheckInTime(input) {
        const error = document.getElementById('checkInError');
        const value = input.value;
        if (!value) {
            return true;
        }

        const selected = new Date(value);
        const now = new Date();
        const valid = !Number.isNaN(selected.getTime()) && selected >= now;
        input.setCustomValidity(valid ? '' : 'Waktu check-in tidak boleh sudah berlalu.');
        error.textContent = valid ? '' : 'Waktu check-in tidak boleh sudah berlalu. Pilih waktu sekarang atau setelahnya.';
        error.classList.toggle('hidden', valid);
        return valid;
    }
</script>
@endpush
