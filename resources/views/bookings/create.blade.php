@extends('layouts.app')

@section('title', 'Tambah Pemesanan')

@section('content')

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Pemesanan</h1>
        <p class="text-sm text-gray-500 mt-1">Input data pemesanan kamar baru (Offline / WhatsApp).</p>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
            <p class="font-semibold mb-1">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm max-w-3xl">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.store') : route('receptionist.pemesanan.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Section 1: Data Customer --}}
            <div>
                <h2 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-100">Data Customer</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Customer *</label>
                        <input
                            type="text"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            placeholder="Contoh: Budi Santoso"
                            required
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Telepon / WA *</label>
                        <input
                            type="text"
                            name="phone_number"
                            value="{{ old('phone_number') }}"
                            placeholder="Contoh: 081234567890"
                            required
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email (Opsional)</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Contoh: customer@gmail.com"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900"
                        >
                    </div>
                </div>
            </div>

            {{-- Section 2: Detail Kamar & Jadwal --}}
            <div>
                <h2 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-100">Detail Kamar & Jadwal</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Kamar *</label>
                        <select name="room_id" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900">
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
                        <select name="booking_type" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <option value="Full Day" {{ old('booking_type') == 'Full Day' ? 'selected' : '' }}>Full Day</option>
                            <option value="Short Time 5 Jam" {{ old('booking_type') == 'Short Time 5 Jam' ? 'selected' : '' }}>Short Time (5 Jam)</option>
                            <option value="Short Time 8 Jam" {{ old('booking_type') == 'Short Time 8 Jam' ? 'selected' : '' }}>Short Time (8 Jam)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jam Check-in *</label>
                        <input
                            type="datetime-local"
                            name="check_in_time"
                            value="{{ old('check_in_time', now()->format('Y-m-d\TH:i')) }}"
                            required
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sumber Pemesanan *</label>
                        <select name="source" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <option value="Offline" {{ old('source') == 'Offline' ? 'selected' : '' }}>Offline (Datang Langsung)</option>
                            <option value="WhatsApp" {{ old('source') == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="Website" {{ old('source') == 'Website' ? 'selected' : '' }}>Website</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section 3: Pembayaran --}}
            <div>
                <h2 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-100">Pembayaran</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash / Tunai</option>
                            <option value="QRIS" {{ old('payment_method') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                            <option value="Virtual Account" {{ old('payment_method') == 'Virtual Account' ? 'selected' : '' }}>Virtual Account (Transfer)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status Pembayaran *</label>
                        <select name="payment_status" required class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <option value="Paid" {{ old('payment_status') == 'Paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                            <option value="Pending" {{ old('payment_status') == 'Pending' ? 'selected' : '' }}>Pending (DP / Belum Lunas)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Pemesanan
                </button>
            </div>
        </form>
    </div>

@endsection
