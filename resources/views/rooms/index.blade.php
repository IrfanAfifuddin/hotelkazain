@extends('layouts.app')

@section('title', 'Status Kamar')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Status Kamar</h1>
            <p class="text-sm text-gray-500 mt-1">Pemantauan dan pengelolaan status ketersediaan kamar secara real-time.</p>
        </div>
        <div class="flex items-center space-x-2 text-xs">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-medium bg-green-100 text-green-800">Tersedia</span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-medium bg-red-100 text-red-800">Terisi</span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-medium bg-yellow-100 text-yellow-800">Cleaning</span>
        </div>
    </div>

    {{-- Alert Success / Error --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('extend'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ $errors->first('extend') }}
        </div>
    @endif

    {{-- Grid Kamar --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($rooms as $room)
            @php
                $activeBooking = $room->bookings->first();
                $statusClass = match($room->room_status) {
                    'Available' => 'border-green-300 bg-green-50/30',
                    'Occupied' => 'border-red-300 bg-red-50/30',
                    'Cleaning' => 'border-yellow-300 bg-yellow-50/30',
                    default => 'border-gray-200 bg-white',
                };
                $badgeClass = match($room->room_status) {
                    'Available' => 'bg-green-100 text-green-800',
                    'Occupied' => 'bg-red-100 text-red-800',
                    'Cleaning' => 'bg-yellow-100 text-yellow-800',
                    default => 'bg-gray-100 text-gray-800',
                };
                $statusLabel = match($room->room_status) {
                    'Available' => 'Tersedia',
                    'Occupied' => 'Terisi',
                    'Cleaning' => 'Cleaning',
                    default => $room->room_status,
                };
            @endphp

            <div class="bg-white border-2 {{ $statusClass }} rounded-xl p-5 flex flex-col justify-between shadow-sm">
                <div>
                    {{-- Header Kamar --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xl font-bold text-gray-900">Kamar {{ $room->room_number }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-500 mb-4">Tipe: <span class="font-medium text-gray-700">{{ $room->room_type }}</span></p>

                    {{-- Detail jika Terisi --}}
                    @if($room->room_status === 'Occupied' && $activeBooking)
                        <div class="bg-white/80 border border-red-200 rounded-lg p-3 text-xs space-y-1 mb-4">
                            <p class="font-semibold text-gray-900">{{ $activeBooking->customer->customer_name ?? 'Tamu' }}</p>
                            <p class="text-gray-500">In: {{ \Carbon\Carbon::parse($activeBooking->check_in_time)->format('d/m/Y H:i') }}</p>
                            <p class="text-gray-500">Out: {{ \Carbon\Carbon::parse($activeBooking->check_out_time)->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2 pt-2 border-t border-gray-100">
                    {{-- Form Ubah Status --}}
                    <form action="{{ route(auth()->user()->role === 'Admin' ? 'admin.status-kamar.update' : 'receptionist.status-kamar.update', $room) }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        @method('PATCH')
                        <select name="room_status" onchange="this.form.submit()" class="w-full text-xs bg-gray-50 border border-gray-300 rounded-md py-1.5 px-2 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            <option value="Available" {{ $room->room_status === 'Available' ? 'selected' : '' }}>Set Tersedia</option>
                            <option value="Occupied" {{ $room->room_status === 'Occupied' ? 'selected' : '' }}>Set Terisi</option>
                            <option value="Cleaning" {{ $room->room_status === 'Cleaning' ? 'selected' : '' }}>Set Cleaning</option>
                        </select>
                    </form>

                    {{-- Tombol Extend if Occupied --}}
                    @if($room->room_status === 'Occupied' && $activeBooking)
                        <a href="{{ route(auth()->user()->role === 'Admin' ? 'admin.pemesanan.extend.create' : 'receptionist.pemesanan.extend.create', $activeBooking) }}" class="block w-full rounded-md bg-gray-900 py-1.5 text-center text-xs font-medium text-white transition hover:bg-gray-800">
                            Extend
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>


@endsection
