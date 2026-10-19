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
                        <button type="button" onclick="openExtendModal({{ $room->id }}, '{{ $room->room_number }}')" class="w-full bg-gray-900 hover:bg-gray-800 text-white text-xs font-medium py-1.5 rounded-md transition">
                            Extend
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Extend Modal --}}
    <div id="extendModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeExtendModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Extend Masa Menginap</h3>
                <p class="text-xs text-gray-500 mb-4" id="extendRoomLabel">Kamar --</p>

                <form id="extendForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tambah Durasi (Jam)</label>
                        <select name="hours" class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <option value="1">1 Jam</option>
                            <option value="2">2 Jam</option>
                            <option value="3">3 Jam</option>
                            <option value="5">5 Jam</option>
                            <option value="8">8 Jam</option>
                            <option value="24">24 Jam (1 Hari)</option>
                        </select>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeExtendModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 rounded-lg text-sm font-medium text-white hover:bg-gray-800">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openExtendModal(roomId, roomNumber) {
                const extendUrlTemplate = "{{ route(auth()->user()->role === 'Admin' ? 'admin.status-kamar.extend' : 'receptionist.status-kamar.extend', ['room' => '__ROOM__']) }}";

                document.getElementById('extendRoomLabel').innerText = 'Kamar ' + roomNumber;
                document.getElementById('extendForm').action = extendUrlTemplate.replace('__ROOM__', roomId);
                document.getElementById('extendModal').classList.remove('hidden');
            }

            function closeExtendModal() {
                document.getElementById('extendModal').classList.add('hidden');
            }
        </script>
    @endpush

@endsection
