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

    <div class="mx-auto w-full max-w-2xl rounded-2xl border border-kazain-border bg-kazain-surface p-4 shadow-sm sm:p-6">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.update', $booking->id) : route('receptionist.pemesanan.update', $booking->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Ringkasan Info --}}
            <div class="space-y-2 break-words rounded-xl border border-kazain-border bg-gray-50 p-4 text-xs text-kazain-text">
                <p><strong class="text-kazain-text">Customer:</strong> {{ $booking->customer->customer_name ?? '-' }} ({{ $booking->customer->phone_number ?? '-' }})</p>
                <p><strong class="text-kazain-text">Tipe Booking:</strong> {{ $booking->booking_type }}</p>
                <p><strong class="text-kazain-text">Jadwal:</strong> {{ \Carbon\Carbon::parse($booking->check_in_time)->format('d M Y H:i') }} s/d {{ \Carbon\Carbon::parse($booking->check_out_time)->format('d M Y H:i') }}</p>
                <p><strong class="text-kazain-text">Total Harga:</strong> Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
            </div>

            {{-- Kamar yang Dipesan --}}
            <div class="bg-kazain-surface border border-kazain-border rounded-xl p-4">
                <label class="block text-xs font-medium text-kazain-muted mb-2">Kamar yang Dipesan</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @forelse($booking->rooms as $room)
                        <div class="rounded-lg bg-gray-50 p-2.5 text-center">
                            <p class="font-semibold text-sm text-kazain-text">No. {{ $room->room_number }}</p>
                            <p class="text-xs text-kazain-muted">{{ $room->room_type }}</p>
                            <p class="text-xs text-kazain-primary mt-1">Rp {{ number_format($room->pivot->price, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="col-span-3 text-xs text-kazain-muted italic">-</p>
                    @endforelse
                </div>
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

@endsection
