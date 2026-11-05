<?php
    $checkIn = $booking->check_in_time->copy()->locale('id');
    $checkOut = $booking->check_out_time->copy()->locale('id');

    if ($booking->booking_type === 'Full Day') {
        $durasi = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24)) . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }
?>
=================================================================
KONFIRMASI PEMESANAN — KAZAIN HOTEL
=================================================================

Terima kasih, <?php echo e($booking->customer->customer_name); ?>


Pemesanan Anda di Kazain Hotel telah berhasil.
Berikut detail pemesanan Anda:

-----------------------------------------------------------------
KODE BOOKING
-----------------------------------------------------------------

<?php echo e($booking->booking_code); ?>


Tunjukkan kode ini kepada resepsionis saat check-in.

-----------------------------------------------------------------
DETAIL PEMESANAN
-----------------------------------------------------------------

Nama Pemesan      : <?php echo e($booking->customer->customer_name); ?>

Nomor Telepon     : <?php echo e($booking->customer->phone_number); ?>

Tipe Pemesanan    : <?php echo e($booking->booking_type); ?>

Check-in          : <?php echo e($checkIn->translatedFormat('l, d F Y, H:i')); ?> WIB
Check-out         : <?php echo e($checkOut->translatedFormat('l, d F Y, H:i')); ?> WIB
Durasi            : <?php echo e($durasi); ?>

Metode Pembayaran : <?php echo e($booking->payment_method); ?>


-----------------------------------------------------------------
KAMAR YANG DIPESAN
-----------------------------------------------------------------

<?php $__currentLoopData = $booking->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
• Kamar No. <?php echo e($room->room_number); ?> — <?php echo e($room->room_type); ?>

  Harga: Rp <?php echo e(number_format($room->pivot->price, 0, ',', '.')); ?>


<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
-----------------------------------------------------------------
TOTAL HARGA: Rp <?php echo e(number_format($booking->total_price, 0, ',', '.')); ?>

-----------------------------------------------------------------

INFORMASI PENTING

• Saat check-in, tamu wajib menitipkan KTP asli kepada resepsionis 
  sebagai deposit. KTP dikembalikan saat check-out.
• Pemesanan yang sudah dibayar tidak dapat dibatalkan.
• Parkir hanya tersedia untuk sepeda motor. Tidak tersedia parkir 
  untuk mobil.

-----------------------------------------------------------------
KONTAK
-----------------------------------------------------------------

Kazain Hotel
Jl. Prawirotaman I, Timuran No. 562
Brontokusuman, Mergangsan, Yogyakarta 55153

WhatsApp: 0821-4233-1906
Resepsionis 24 jam

=================================================================
Email ini dikirim otomatis. Mohon tidak membalas email ini.
=================================================================
<?php /**PATH D:\KULIAH\hotelkazain\resources\views/mail/booking-confirmation-text.blade.php ENDPATH**/ ?>