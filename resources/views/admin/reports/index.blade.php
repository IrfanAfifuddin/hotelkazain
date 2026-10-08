@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi pendapatan dan transaksi hotel.</p>
        </div>
        <div>
            <button onclick="window.print()" class="inline-flex items-center space-x-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.725 14.406v2.481c0 .823.668 1.49 1.49 1.49h7.585c.822 0 1.49-.667 1.49-1.49v-2.481M12 2.25v12.75m0 0L8.25 11.25M12 15l3.75-3.75" />
                </svg>
                <span>Cetak / Print Laporan</span>
            </button>
        </div>
    </div>

    {{-- Filter Rentang Tanggal --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-auto flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-gray-900">
            </div>
            <div class="w-full sm:w-auto flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-gray-900">
            </div>
            <div class="w-full sm:w-auto flex items-end gap-2 sm:mt-5">
                <button type="submit" class="w-full sm:w-auto bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Filter
                </button>
                @if($startDate || $endDate)
                    <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Ringkasan Pendapatan --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Total Pendapatan (Lunas)</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Rincian Transaksi Sukses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Booking Code</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Kamar</th>
                        <th class="px-6 py-3">Metode Bayar</th>
                        <th class="px-6 py-3">Waktu Transaksi</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $index => $b)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $b->booking_code }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $b->customer->customer_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">Kamar {{ $b->room->room_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $b->payment_method ?? 'Cash' }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $b->payment_time ? \Carbon\Carbon::parse($b->payment_time)->format('d M Y H:i') : $b->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                Tidak ada data transaksi pada rentang tanggal tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
