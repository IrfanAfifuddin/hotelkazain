<?php $__env->startSection('title', 'Status Kamar'); ?>

<?php $__env->startSection('content'); ?>
    <?php
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
    ?>

    <div class="mx-auto max-w-7xl space-y-7">
        <header class="relative isolate overflow-hidden rounded-3xl bg-kazain-primary px-6 py-7 text-white shadow-[0_20px_50px_-32px_rgba(29,48,39,0.65)] sm:px-8 sm:py-9">
            <div aria-hidden="true" class="absolute -right-16 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-2 -top-14 -z-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-28 right-36 -z-10 h-52 w-52 rounded-full bg-kazain-accent/10 blur-3xl"></div>

            <div class="flex flex-col gap-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#d9c99d]"></span>
                        <?php echo e(now()->locale('id')->translatedFormat('l, d F Y H:i')); ?>

                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Status kamar</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Pantau kondisi kamar dan perbarui statusnya dengan mudah. Status diperbarui otomatis berdasarkan jadwal check-in/check-out.</p>
                </div>

                <div aria-label="Ringkasan status kamar" class="grid grid-cols-3 gap-2 sm:min-w-[390px] sm:gap-3">
                    <div aria-label="Tersedia: <?php echo e($stats['available']); ?> kamar" class="rounded-xl border border-emerald-200/20 bg-emerald-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-emerald-100">Tersedia</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-emerald-50"><?php echo e($stats['available']); ?></p>
                    </div>
                    <div aria-label="Terisi: <?php echo e($stats['occupied']); ?> kamar" class="rounded-xl border border-rose-200/20 bg-rose-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-rose-100">Terisi</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-rose-50"><?php echo e($stats['occupied']); ?></p>
                    </div>
                    <div aria-label="Cleaning: <?php echo e($stats['cleaning']); ?> kamar" class="rounded-xl border border-orange-200/20 bg-orange-400/15 px-3 py-3 sm:px-4">
                        <p class="text-xs font-medium text-orange-100">Cleaning</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-orange-50"><?php echo e($stats['cleaning']); ?></p>
                    </div>
                </div>
            </div>
        </header>

        <?php if(session('success')): ?>
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-[#c6dfcf] bg-[#f0f7f2] px-4 py-3.5 text-sm text-[#285c3d]">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 4.5-4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <?php if($errors->has('extend')): ?>
            <div role="alert" class="rounded-2xl border border-[#e8c9c2] bg-[#fbf3f1] px-4 py-3.5 text-sm text-[#844a40]">
                <p class="font-semibold">Perpanjangan tidak dapat diproses.</p>
                <p class="mt-1"><?php echo e($errors->first('extend')); ?></p>
            </div>
        <?php endif; ?>

        <?php if($errors->has('room_status')): ?>
            <div role="alert" class="rounded-2xl border border-[#e8c9c2] bg-[#fbf3f1] px-4 py-3.5 text-sm text-[#844a40]">
                <p class="font-semibold">Perubahan status tidak diizinkan.</p>
                <p class="mt-1"><?php echo e($errors->first('room_status')); ?></p>
            </div>
        <?php endif; ?>

        <section aria-label="Daftar status kamar">
            <div class="mb-2 flex items-center gap-2.5">
                <h2 class="text-base font-semibold text-kazain-text">Daftar kamar</h2>
                <span class="rounded-full bg-[#edf1ec] px-2.5 py-1 text-xs font-medium text-kazain-primary">
                    <?php echo e($rooms->count()); ?> dari <?php echo e($stats['total']); ?> kamar
                </span>
            </div>

            <?php if($rooms->isEmpty()): ?>
                <div class="rounded-2xl border border-kazain-border bg-gray-100 px-6 py-14 text-center shadow-sm">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#edf1ec] text-kazain-primary">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V5.25L12 2l6.75 3.25V21M8.25 8.25h.008v.008H8.25V8.25Zm7.5 0h.008v.008h-.008V8.25Z" />
                        </svg>
                    </span>
                    <h2 class="mt-4 text-base font-semibold text-kazain-text">Belum ada data kamar</h2>
                    <p class="mt-1 text-sm text-kazain-muted">Kamar yang terdaftar akan muncul di halaman ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $statusClass = match ($room->current_status) {
                                'Available' => 'bg-emerald-100 text-emerald-800',
                                'Occupied' => 'bg-rose-100 text-rose-800',
                                'Cleaning' => 'bg-orange-100 text-orange-800',
                                default => 'bg-[#f4f5f1] text-kazain-muted',
                            };
                            $statusLabel = match ($room->current_status) {
                                'Available' => 'Tersedia',
                                'Occupied' => 'Terisi',
                                'Cleaning' => 'Cleaning',
                                default => $room->current_status,
                            };
                        ?>

                        <article class="flex flex-col rounded-2xl border border-kazain-border bg-gray-100 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#cbd5cc] hover:shadow-md sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.12em] text-kazain-muted">Nomor kamar</p>
                                    <h3 class="mt-1 text-xl font-semibold tracking-tight text-kazain-text">Kamar <?php echo e($room->room_number); ?></h3>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>">
                                    <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </div>

                            <p class="mt-3 text-sm text-kazain-muted">
                                Tipe kamar <span class="font-medium text-kazain-text"><?php echo e($room->room_type); ?></span>
                            </p>

                            <?php if($room->current_status === 'Occupied' && $room->active_booking): ?>
                                <div class="mt-4 space-y-2 rounded-xl border border-kazain-border bg-[#f7f8f5] p-3.5 text-sm">
                                    <p class="font-semibold text-kazain-text"><?php echo e($room->active_booking->customer->customer_name ?? 'Tamu'); ?></p>
                                    <div class="flex items-center justify-between gap-3 text-xs text-kazain-muted">
                                        <span>Check-in</span>
                                        <span class="font-medium text-kazain-text"><?php echo e(\Carbon\Carbon::parse($room->active_booking->check_in_time)->format('d/m/Y H:i')); ?></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-xs text-kazain-muted">
                                        <span>Check-out</span>
                                        <span class="font-medium text-kazain-text"><?php echo e(\Carbon\Carbon::parse($room->active_booking->check_out_time)->format('d/m/Y H:i')); ?></span>
                                    </div>
                                </div>
                            <?php elseif($room->current_status === 'Cleaning' && $room->cleaning_ends_at): ?>
                                <div class="mt-4 space-y-2 rounded-xl border border-kazain-border bg-[#fff7ed] p-3.5 text-sm">
                                    <p class="font-semibold text-orange-800">Sedang dibersihkan</p>
                                    <div class="flex items-center justify-between gap-3 text-xs text-orange-700">
                                        <span>Selesai cleaning</span>
                                        <span class="font-medium text-orange-800"><?php echo e(\Carbon\Carbon::parse($room->cleaning_ends_at)->format('d/m/Y H:i')); ?></span>
                                    </div>
                                    <p class="text-xs text-orange-600">Kamar akan tersedia otomatis pada pukul <?php echo e(\Carbon\Carbon::parse($room->cleaning_ends_at)->format('H:i')); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto border-t border-kazain-border pt-5 <?php echo e(($room->current_status === 'Occupied' && $room->active_booking) || ($room->current_status === 'Cleaning' && $room->cleaning_ends_at) ? 'mt-5' : ''); ?>">
                                <p class="mb-2.5 text-xs font-semibold text-kazain-muted">Ubah status kamar</p>
                                <div class="grid grid-cols-3 gap-1 rounded-xl border border-kazain-border bg-[#f7f8f5] p-1">
                                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isCurrentStatus = $room->current_status === $statusOption['value'];
                                            // Disable manual override to Available if room should be Occupied
                                            $shouldDisableAvailable = $statusOption['value'] === 'Available' && $room->current_status === 'Occupied';
                                            // Disable manual override to Occupied if no active booking
                                            $shouldDisableOccupied = $statusOption['value'] === 'Occupied' && $room->current_status === 'Available' && !$room->active_booking;
                                            $isDisabled = $isCurrentStatus || $shouldDisableAvailable || $shouldDisableOccupied;
                                        ?>
                                        <form action="<?php echo e(route($statusRoute, $room)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button
                                                type="submit"
                                                name="room_status"
                                                value="<?php echo e($statusOption['value']); ?>"
                                                aria-label="Set status kamar <?php echo e($room->room_number); ?> menjadi <?php echo e($statusOption['label']); ?>"
                                                aria-pressed="<?php echo e($isCurrentStatus ? 'true' : 'false'); ?>"
                                                <?php if($isDisabled): echo 'disabled'; endif; ?>
                                                class="w-full rounded-lg px-1.5 py-2 text-xs font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/15 disabled:cursor-default <?php echo e($isCurrentStatus ? $statusOption['activeClass'] : $statusOption['inactiveClass']); ?>"
                                            >
                                                <?php echo e($statusOption['label']); ?>

                                            </button>
                                        </form>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <?php if($room->current_status === 'Occupied' && $room->active_booking): ?>
                                    <a href="<?php echo e(route(auth()->user()->role === 'Admin' ? 'admin.pemesanan.extend.create' : 'receptionist.pemesanan.extend.create', $room->active_booking)); ?>" class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-kazain-primary px-4 text-sm font-semibold text-white transition hover:bg-kazain-primary-dark focus:outline-none focus-visible:ring-4 focus-visible:ring-kazain-primary/20">
                                        Extend Pemesanan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\hotelkazain\resources\views/rooms/index.blade.php ENDPATH**/ ?>