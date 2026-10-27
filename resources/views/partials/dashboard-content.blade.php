    @php
        $availableRooms = \App\Models\Room::query()->where('room_status', 'Available')->count();
        $occupiedRooms = \App\Models\Room::query()->where('room_status', 'Occupied')->count();
        $checkInsToday = \App\Models\Booking::query()->whereDate('check_in_time', today())->count();
        $checkOutsToday = \App\Models\Booking::query()->whereDate('check_out_time', today())->count();
        $recentBookings = \App\Models\Booking::query()->with(['customer', 'room'])->latest()->limit(5)->get();
        $isAdmin = auth()->user()->role === 'Admin';
        $bookingIndexRoute = $isAdmin ? 'admin.pemesanan.index' : 'receptionist.pemesanan.index';
        $bookingCreateRoute = $isAdmin ? 'admin.pemesanan.create' : 'receptionist.pemesanan.create';
        $roomStatusRoute = $isAdmin ? 'admin.status-kamar.index' : 'receptionist.status-kamar.index';
        $greeting = now()->hour < 11 ? 'pagi' : (now()->hour < 15 ? 'siang' : (now()->hour < 18 ? 'sore' : 'malam'));
    @endphp

    <div class="mx-auto max-w-7xl space-y-7">
        <section class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>

            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Selamat {{ $greeting }}, {{ auth()->user()->user_name }}
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">
                        {{ $isAdmin ? 'Pantau operasional dan aktivitas hotel hari ini.' : 'Semua yang Anda perlukan untuk melayani tamu dengan lebih mudah.' }}
                    </p>
                </div>

                <a href="{{ route($bookingCreateRoute) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#d8c89c] px-4 text-sm font-semibold text-[#26332c] shadow-sm transition hover:bg-[#e3d6b5] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                    </svg>
                    Buat pemesanan
                </a>
            </div>
        </section>

        <section aria-label="Ringkasan operasional" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route($roomStatusRoute) }}" class="group rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#b9c9bc] hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                <div class="flex items-start gap-4">
                    <div>
                        <p class="text-sm font-medium text-kazain-muted">Kamar tersedia</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $availableRooms }}</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-kazain-muted">Lihat status dan ketersediaan kamar <span aria-hidden="true" class="ml-1 transition group-hover:ml-2">→</span></p>
            </a>

            <a href="{{ route($roomStatusRoute) }}" class="group rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#ddc7c0] hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                <div class="flex items-start gap-4">
                    <div>
                        <p class="text-sm font-medium text-kazain-muted">Kamar terisi</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $occupiedRooms }}</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-kazain-muted">Pantau kamar yang sedang digunakan <span aria-hidden="true" class="ml-1 transition group-hover:ml-2">→</span></p>
            </a>

            <a href="{{ route($bookingIndexRoute) }}" class="group rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#c3d0d7] hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                <div class="flex items-start gap-4">
                    <div>
                        <p class="text-sm font-medium text-kazain-muted">Check-in hari ini</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $checkInsToday }}</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-kazain-muted">Lihat daftar pemesanan tamu <span aria-hidden="true" class="ml-1 transition group-hover:ml-2">→</span></p>
            </a>

            <a href="{{ route($bookingIndexRoute) }}" class="group rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#ddd2b5] hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                <div class="flex items-start gap-4">
                    <div>
                        <p class="text-sm font-medium text-kazain-muted">Check-out hari ini</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-kazain-text">{{ $checkOutsToday }}</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-kazain-muted">Siapkan proses keberangkatan tamu <span aria-hidden="true" class="ml-1 transition group-hover:ml-2">→</span></p>
            </a>
        </section>

        <section class="grid grid-cols-1 gap-5">
            <div class="min-w-0 overflow-hidden rounded-2xl border border-kazain-border bg-gray-100 shadow-sm">
                <div class="flex flex-col gap-4 border-b border-kazain-border px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-base font-semibold text-kazain-text">Pemesanan terbaru</h2>
                            <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">{{ $recentBookings->count() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-kazain-muted">Aktivitas reservasi terakhir di Kazain Hotel.</p>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-[#f7f8f5]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Kode booking</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Tamu</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Kamar</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Check-in</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Check-out</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kazain-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-kazain-border">
                            @forelse ($recentBookings as $booking)
                                @php
                                    $statusClasses = match ($booking->booking_status) {
                                        'Confirmed' => 'bg-[#e8efe9] text-kazain-primary',
                                        'Pending' => 'bg-[#f4f0e4] text-[#806b3f]',
                                        'Check-in' => 'bg-[#eaf0f2] text-[#526d7b]',
                                        'Check-out' => 'bg-[#eef0ed] text-[#5c655e]',
                                        'Cancelled' => 'bg-[#f4eae7] text-[#925d52]',
                                        default => 'bg-[#eef0ed] text-[#5c655e]',
                                    };
                                @endphp
                                <tr
                                    class="transition-colors hover:bg-[#fafbf9]"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-kazain-text">{{ $booking->booking_code }}</td>
                                    <td class="px-6 py-4 text-kazain-muted">{{ $booking->customer?->customer_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-kazain-muted">{{ $booking->room?->room_number ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $booking->check_in_time->format('d M Y, H:i') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-kazain-muted">{{ $booking->check_out_time->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                                            {{ $booking->booking_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-[#edf1ec] text-kazain-primary">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5M5.25 3.75h13.5A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                            </svg>
                                        </span>
                                        <p class="mt-3 text-sm font-medium text-kazain-text">Belum ada pemesanan</p>
                                        <p class="mt-1 text-sm text-kazain-muted">Pemesanan terbaru akan muncul di sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>
