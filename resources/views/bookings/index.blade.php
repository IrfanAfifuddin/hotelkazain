@extends('layouts.app')

@section('title', 'Data Pemesanan')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Pemesanan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data reservasi kamar hotel (Online & Offline).</p>
        </div>
        <div>
            <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.create') : route('receptionist.pemesanan.create') }}" class="inline-flex items-center space-x-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pemesanan</span>
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search & Filter Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form action="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" method="GET" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari kode booking, nama customer, atau nomor kamar..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                Cari
            </button>
            @if($search)
                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.index') : route('receptionist.pemesanan.index') }}" class="text-xs text-red-600 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Pemesanan --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Booking Code</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Kamar</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Check-in</th>
                        <th class="px-6 py-3">Check-out</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $b->booking_code }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $b->customer->customer_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $b->customer->phone_number ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-medium">Kamar {{ $b->room->room_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $b->booking_type }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($b->check_in_time)->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($b->check_out_time)->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = match($b->booking_status) {
                                        'Confirmed' => 'bg-green-100 text-green-800',
                                        'Check-in' => 'bg-blue-100 text-blue-800',
                                        'Check-out' => 'bg-gray-100 text-gray-800',
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $b->booking_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ auth()->user()->role === 'Admin' ? route('admin.pemesanan.edit', $b->id) : route('receptionist.pemesanan.edit', $b->id) }}" class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-900">
                                    Edit
                                </a>
                                <button type="button" onclick="confirmDelete({{ $b->id }}, '{{ $b->booking_code }}')" class="inline-flex items-center text-xs font-medium text-red-600 hover:text-red-900">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-400">
                                Tidak ada data pemesanan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    {{-- Confirmation Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeDeleteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Pemesanan</h3>
                <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus pemesanan <span id="deleteCodeLabel" class="font-semibold text-gray-900">--</span>?</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700">
                            Konfirmasi Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(id, code) {
                document.getElementById('deleteCodeLabel').innerText = code;
                // Menggunakan rute berdasarkan role user saat ini
                const prefix = "{{ auth()->user()->role === 'Admin' ? '/admin' : '/receptionist' }}";
                document.getElementById('deleteForm').action = prefix + '/pemesanan/' + id;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
        </script>
    @endpush

@endsection
