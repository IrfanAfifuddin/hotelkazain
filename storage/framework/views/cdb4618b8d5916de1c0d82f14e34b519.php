<?php $__env->startSection('title', 'Pembayaran'); ?>
<?php $__env->startSection('step', '3'); ?>

<?php
    $checkIn = $booking->check_in_time?->copy()->locale('id');
    $checkOut = $booking->check_out_time?->copy()->locale('id');

    if ($booking->booking_type === 'Full Day' && $checkIn && $checkOut) {
        $nights = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24));
        $durasi = $nights . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }

    $isPaid = $booking->payment_status === 'Paid';
    $isPending = $booking->payment_status === 'Pending' && $booking->midtrans_redirect_url;
    $expiresAt = $booking->payment_expires_at;
?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Pembayaran</h1>

        <div class="mt-8 space-y-6">

                
                <?php if($isPending && $expiresAt): ?>
                    <div class="border-2 border-gray-300 rounded-lg px-6 py-7 text-center">
                        <p id="timerTitle" class="text-sm font-medium tracking-[0.2em] text-gray-500 uppercase">Selesaikan pembayaran dalam</p>
                        <p id="timerText" class="mt-2 text-5xl sm:text-6xl font-bold tracking-wider text-gray-900 tabular-nums">--:--</p>
                        <div class="mt-5 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div id="timerBar" class="h-full bg-gray-800 rounded-full transition-[width] duration-1000 ease-linear" style="width: 100%"></div>
                        </div>
                        <p class="mt-3 text-sm text-gray-400">
                            Kamar ditahan sampai <?php echo e($expiresAt->copy()->locale('id')->translatedFormat('d F Y, H:i')); ?>. Pesanan dibatalkan otomatis jika belum dibayar.
                        </p>
                    </div>
                <?php endif; ?>

                
                <?php if($isPaid): ?>
                    <div class="flex items-start gap-3 border-2 border-gray-900 rounded-lg px-5 py-4">
                        <svg class="w-6 h-6 shrink-0 text-gray-900" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="m8.5 12 2.5 2.5 4.5-5" /></svg>
                        <div>
                            <p class="font-semibold text-gray-900">Pembayaran sudah diterima</p>
                            <p class="text-sm text-gray-500">Kode booking telah dikirim ke email Anda.</p>
                        </div>
                    </div>
                <?php elseif(! $isPending): ?>
                    <div class="flex items-start gap-3 border border-dashed border-gray-300 bg-gray-50 rounded-lg px-5 py-4">
                        <svg class="w-6 h-6 shrink-0 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                        <div>
                            <p class="font-semibold text-gray-900">Transaksi tidak dapat dibayar</p>
                            <p class="text-sm text-gray-500">Waktu pembayaran habis atau transaksi dibatalkan. Silakan mulai pemesanan baru.</p>
                        </div>
                    </div>
                <?php endif; ?>

                
                <section class="border border-gray-200 rounded-lg overflow-hidden">
                    <h2 class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm font-semibold tracking-wider text-gray-700 uppercase">Ringkasan Pesanan</h2>
                    <div class="p-6">
                        <dl class="space-y-3">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Kamar</dt>
                                <dd class="text-right text-gray-900"><?php echo e($booking->room->room_type); ?> Bed · No. <?php echo e($booking->room->room_number); ?></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Check-in</dt>
                                <dd class="text-right text-gray-900"><?php echo e($checkIn ? $checkIn->translatedFormat('d F Y, H:i') . ' WIB' : '-'); ?></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Check-out</dt>
                                <dd class="text-right text-gray-900"><?php echo e($checkOut ? $checkOut->translatedFormat('d F Y, H:i') . ' WIB' : '-'); ?></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Durasi</dt>
                                <dd class="text-right text-gray-900"><?php echo e($durasi); ?></dd>
                            </div>
                        </dl>
                        <div class="mt-5 pt-5 border-t border-gray-200 flex items-end justify-between gap-4">
                            <div>
                                <p class="font-medium text-gray-900">Total Tagihan</p>
                                <p class="text-xs text-gray-400">Bayar Penuh (Lunas)</p>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($booking->total_price, 0, ',', '.')); ?></p>
                        </div>
                    </div>
                </section>

                
                <section class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold tracking-wider text-gray-700 uppercase">Metode Pembayaran</h2>
                        <span class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-900"><?php echo e($booking->payment_method); ?></span>
                    </div>
                    <div class="p-6">
                        <?php if($isPaid): ?>
                            <a href="<?php echo e(route('customer.success', ['order_id' => $booking->midtrans_order_id])); ?>"
                               class="flex w-full justify-center py-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-white text-lg font-semibold transition">
                                Lihat Kode Booking &rarr;
                            </a>
                        <?php elseif($isPending): ?>
                            <p class="text-gray-600">
                                Pembayaran diproses melalui <strong class="text-gray-900">Midtrans</strong>. Nomor
                                <?php echo e($booking->payment_method === 'QRIS' ? 'kode QR' : 'Virtual Account'); ?>

                                dan cara membayar akan ditampilkan di halaman berikutnya.
                            </p>
                            <a href="<?php echo e($booking->midtrans_redirect_url); ?>"
                               class="mt-5 flex w-full justify-center py-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-white text-lg font-semibold transition">
                                Lanjutkan pembayaran di Midtrans
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('customer.availability')); ?>"
                               class="flex w-full justify-center py-4 rounded-lg border border-gray-300 text-lg font-semibold text-gray-900 hover:bg-gray-50 transition">
                                Pesan Kamar Kembali
                            </a>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if($isPending): ?>
                    <p class="px-6 py-4 bg-gray-50 border border-gray-200 rounded-lg text-center text-gray-600">
                        Setelah pembayaran diterima, Anda akan diarahkan ke halaman konfirmasi dan kode booking dikirim ke email Anda.
                    </p>
                <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php if($isPending && $expiresAt): ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            (function () {
                const deadline = new Date(<?php echo json_encode($expiresAt->toIso8601String(), 15, 512) ?>).getTime();
                const HOLD_SECONDS = 20 * 60;
                const timerText = document.getElementById('timerText');
                const timerBar = document.getElementById('timerBar');
                const pad = (n) => String(n).padStart(2, '0');

                function tick() {
                    const remaining = Math.max(0, Math.round((deadline - Date.now()) / 1000));
                    timerText.textContent = `${pad(Math.floor(remaining / 60))}:${pad(remaining % 60)}`;
                    timerBar.style.width = `${Math.min(100, (remaining / HOLD_SECONDS) * 100)}%`;

                    if (remaining <= 0) {
                        clearInterval(timer);
                        // Muat ulang supaya server menampilkan status terbaru (kedaluwarsa)
                        setTimeout(() => window.location.reload(), 1500);
                    }
                }

                const timer = setInterval(tick, 1000);
                tick();
            })();
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php echo $__env->make('layouts.booking', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\hotelkazain\resources\views/customer/payment.blade.php ENDPATH**/ ?>