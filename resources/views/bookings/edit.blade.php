@extends('layouts.app')

@section('title', 'Edit Pemesanan')

@section('content')

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-kazain-text">Edit Pemesanan</h1>
        <p class="text-sm text-kazain-muted mt-1">Perbarui status dan detail pemesanan <span class="font-semibold text-kazain-text">{{ $booking->booking_code }}</span>.</p>
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

    <div class="bg-kazain-surface border border-kazain-border rounded-2xl p-6 shadow-sm max-w-2xl">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.update', $booking->id) : route('receptionist.pemesanan.update', $booking->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Ringkasan Info --}}
            <div class="bg-gray-50 border border-kazain-border rounded-xl p-4 text-xs space-y-2 text-kazain-text">
                <p><strong class="text-kazain-text">Customer:</strong> {{ $booking->customer->customer_name ?? '-' }} ({{ $booking->customer->phone_number ?? '-' }})</p>
                <p><strong class="text-kazain-text">Tipe Booking:</strong> {{ $booking->booking_type }}</p>
                <p><strong class="text-kazain-text">Jadwal:</strong> {{ \Carbon\Carbon::parse($booking->check_in_time)->format('d M Y H:i') }} s/d {{ \Carbon\Carbon::parse($booking->check_out_time)->format('d M Y H:i') }}</p>
                <p><strong class="text-kazain-text">Total Harga:</strong> Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
            </div>

            {{-- Room Selection --}}
            <div>
                <label class="block text-xs font-medium text-kazain-muted mb-1.5">Pilih Kamar *</label>
                <select name="room_id" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ $booking->room_id == $room->id ? 'selected' : '' }}>
                            Kamar {{ $room->room_number }} ({{ $room->room_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Booking Status --}}
            <div>
                <label class="block text-xs font-medium text-kazain-muted mb-1.5">Status Pemesanan *</label>
                <select name="booking_status" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    <option value="Pending" {{ $booking->booking_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Confirmed" {{ $booking->booking_status == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="Check-in" {{ $booking->booking_status == 'Check-in' ? 'selected' : '' }}>Check-in</option>
                    <option value="Check-out" {{ $booking->booking_status == 'Check-out' ? 'selected' : '' }}>Check-out</option>
                    <option value="Cancelled" {{ $booking->booking_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div>
                <label class="block text-xs font-medium text-kazain-muted mb-1.5">Status Pembayaran *</label>
                <select name="payment_status" required class="min-h-11 w-full rounded-xl border border-kazain-border bg-kazain-surface px-3.5 py-2.5 text-sm text-kazain-text outline-none transition focus:border-kazain-primary focus:bg-gray-50 focus:ring-4 focus:ring-kazain-primary/10">
                    <option value="Pending" {{ $booking->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Paid" {{ $booking->payment_status == 'Paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                    <option value="Failed" {{ $booking->payment_status == 'Failed' ? 'selected' : '' }}>Failed</option>
                    <option value="Cancelled" {{ $booking->payment_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-kazain-border">
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="px-5 py-2.5 border border-kazain-border rounded-xl text-sm font-medium text-kazain-muted hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-kazain-primary hover:bg-kazain-primary-hover text-white text-sm font-semibold rounded-xl transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

@endsection
