@extends('layouts.app')

@section('title', 'Status Kamar')

@section('content')
    @php
        $statusRoute = auth()->user()->role === 'Admin' ? 'admin.status-kamar.update' : 'receptionist.status-kamar.update';
        $statusOptions = [
            [
                'value' => 'Available',
                'label' => 'Tersedia',
                'activeClass' => 'bg-emerald-100 text-emerald-800',
                'inactiveClass' => 'text-emerald-700 hover:bg-emerald-50',
            ],
            [
                'value' => 'Occupied',
                'label' => 'Terisi',
                'activeClass' => 'bg-rose-100 text-rose-800',
                'inactiveClass' => 'text-rose-700 hover:bg-rose-50',
            ],
            [
                'value' => 'Cleaning',
                'label' => 'Cleaning',
                'activeClass' => 'bg-orange-100 text-orange-800',
                'inactiveClass' => 'text-orange-700 hover:bg-orange-50',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>

            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Status kamar</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Pantau kondisi kamar dan perbarui statusnya dengan mudah.</p>
                </div>

                <div aria-label="Ringkasan status kamar" class="grid grid-cols-3 gap-2 sm:min-w-[390px] sm:gap-3">
                    <div aria-label="Tersedia: {{ $stats['available'] }} kamar" class="rounded-xl border border-emerald-200/20 bg-emerald-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-emerald-100">Tersedia</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-emerald-50">{{ $stats['available'] }}</p>
                    </div>
                    <div aria-label="Terisi: {{ $stats['occupied'] }} kamar" class="rounded-xl border border-rose-200/20 bg-rose-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-rose-100">Terisi</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-rose-50">{{ $stats['occupied'] }}</p>
                    </div>
                    <div aria-label="Cleaning: {{ $stats['cleaning'] }} kamar" class="rounded-xl border border-orange-200/20 bg-orange-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-orange-100">Cleaning</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-orange-50">{{ $stats['cleaning'] }}</p>
                    </div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-[#c6dfcf] bg-[#f0f7f2] px-4 py-3.5 text-sm text-[#285c3d]">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 4.5-4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->has('extend'))
            <div role="alert" class="rounded-2xl border border-[#e8c9c2] bg-[#fbf3f1] px-4 py-3.5 text-sm text-[#844a40]">
                <p class="font-semibold">Perpanjangan tidak dapat diproses.</p>
                <p class="mt-1">{{ $errors->first('extend') }}</p>
            </div>
        @endif

        <section aria-label="Daftar status kamar">
            <div class="mb-2 flex items-center gap-2.5">
                <h2 class="text-base font-semibold text-kazain-text">Daftar kamar</h2>
                <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">
                    {{ $rooms->count() }} dari {{ $stats['total'] }} kamar
                </span>
            </div>

            @if ($rooms->isEmpty())
                <div class="rounded-2xl border border-kazain-border bg-gray-100 px-6 py-14 text-center shadow-sm">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#edf1ec] text-kazain-primary">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V5.25L12 2l6.75 3.25V21M8.25 8.25h.008v.008H8.25V8.25Zm7.5 0h.008v.008h-.008V8.25Z" />
                        </svg>
                    </span>
                    <h2 class="mt-4 text-base font-semibold text-kazain-text">Belum ada data kamar</h2>
                    <p class="mt-1 text-sm text-kazain-muted">Kamar yang terdaftar akan muncul di halaman ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($rooms as $room)
                        @php
                            $activeBooking = $room->bookings->first();
                            $statusClass = match ($room->room_status) {
                                'Available' => 'bg-emerald-100 text-emerald-800',
                                'Occupied' => 'bg-rose-100 text-rose-800',
                                'Cleaning' => 'bg-orange-100 text-orange-800',
                                default => 'bg-[#f4f5f1] text-kazain-muted',
                            };
                            $statusLabel = match ($room->room_status) {
                                'Available' => 'Tersedia',
                                'Occupied' => 'Terisi',
                                'Cleaning' => 'Cleaning',
                                default => $room->room_status,
                            };
                        @endphp

                        <article class="flex flex-col rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#cbd5cc] hover:shadow-md sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.12em] text-kazain-muted">Nomor kamar</p>
                                    <h3 class="mt-1 text-xl font-semibold tracking-tight text-kazain-text">Kamar {{ $room->room_number }}</h3>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm text-kazain-muted">
                                Tipe kamar <span class="font-medium text-kazain-text">{{ $room->room_type }}</span>
                            </p>

                            @if ($room->room_status === 'Occupied' && $activeBooking)
                                <div class="mt-4 space-y-2 rounded-xl border border-kazain-border bg-[#f7f8f5] p-3.5 text-sm">
                                    <p class="font-semibold text-kazain-text">{{ $activeBooking->customer->customer_name ?? 'Tamu' }}</p>
                                    <div class="flex items-center justify-between gap-3 text-xs text-kazain-muted">
                                        <span>Check-in</span>
                                        <span class="font-medium text-kazain-text">{{ \Carbon\Carbon::parse($activeBooking->check_in_time)->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-xs text-kazain-muted">
                                        <span>Check-out</span>
                                        <span class="font-medium text-kazain-text">{{ \Carbon\Carbon::parse($activeBooking->check_out_time)->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto border-t border-kazain-border pt-5 {{ $room->room_status === 'Occupied' && $activeBooking ? 'mt-5' : '' }}">
                                <p class="mb-2.5 text-xs font-semibold text-kazain-muted">Ubah status kamar</p>
                                <div class="grid grid-cols-3 gap-1 rounded-xl border border-kazain-border bg-[#f7f8f5] p-1">
                                    @foreach ($statusOptions as $statusOption)
                                        @php
                                            $isCurrentStatus = $room->room_status === $statusOption['value'];
                                        @endphp
                                        <form action="{{ route($statusRoute, $room) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                name="room_status"
                                                value="{{ $statusOption['value'] }}"
                                                aria-label="Set status kamar {{ $room->room_number }} menjadi {{ $statusOption['label'] }}"
                                                aria-pressed="{{ $isCurrentStatus ? 'true' : 'false' }}"
                                                @disabled($isCurrentStatus)
                                                class="w-full rounded-lg px-1.5 py-2 text-xs font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 disabled:cursor-default {{ $isCurrentStatus ? $statusOption['activeClass'] : $statusOption['inactiveClass'] }}"
                                            >
                                                {{ $statusOption['label'] }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>

                                @if ($room->room_status === 'Occupied' && $activeBooking)
                                    <a href="{{ route(auth()->user()->role === 'Admin' ? 'admin.pemesanan.extend.create' : 'receptionist.pemesanan.extend.create', $activeBooking) }}" class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-kazain-primary px-4 text-sm font-semibold text-white transition hover:bg-kazain-primary-dark focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                                        Extend Pemesanan
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
