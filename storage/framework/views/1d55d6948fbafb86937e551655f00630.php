<?php
    $checkIn = $booking->check_in_time->copy()->locale('id');
    $checkOut = $booking->check_out_time->copy()->locale('id');

    if ($booking->booking_type === 'Full Day') {
        $durasi = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24)) . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }

    $roomsGrouped = $booking->rooms->groupBy('room_type');
    $roomList = $roomsGrouped->map(function ($rooms, $type) {
        return $rooms->map(function ($room) use ($type) {
            return $type . ' Bed No. ' . $room->room_number;
        });
    })->flatten()->join("\n");
?>
=================================================================
KONFIRMASI PEMBAYARAN — KAZAIN HOTEL
=================================================================

Terima kasih, <?php echo e($booking->customer->customer_name); ?>


Pembayaran pemesanan kamar Anda di Kazain Hotel telah berhasil dikonfirmasi.

=================================================================
KODE BOOKING
=================================================================
<?php echo e($booking->booking_code); ?>


Tunjukkan kode ini kepada resepsionis saat check-in.

=================================================================
INFORMASI PEMESAN
=================================================================
Nama         : <?php echo e($booking->customer->customer_name); ?>

Email        : <?php echo e($booking->customer->email); ?>

Telepon      : <?php echo e($booking->customer->phone_number); ?>

Tanggal Pesan : <?php echo e($booking->created_at->translatedFormat('d F Y, H:i')); ?> WIB

=================================================================
KAMAR YANG DIPESAN
=================================================================
<?php echo e($roomList); ?>


Total Kamar  : <?php echo e($booking->rooms->count()); ?>


=================================================================
JADWAL MENGINAP
=================================================================
Check-in     : <?php echo e($checkIn->translatedFormat('l, d F Y - H:i')); ?> WIB
Check-out    : <?php echo e($checkOut->translatedFormat('l, d F Y - H:i')); ?> WIB
Durasi       : <?php echo e($durasi); ?>


=================================================================
RINGKASAN PEMBAYARAN
=================================================================
Metode Pembayaran    : <?php echo e($booking->payment_method); ?>

ID Transaksi         : <?php echo e($booking->midtrans_transaction_id ?? 'Diproses'); ?>

Total Pembayaran     : Rp <?php echo e(number_format($booking->total_price, 0, ',', '.')); ?>

Status Pembayaran    : ✓ LUNAS

=================================================================
INFORMASI PENTING
=================================================================
• Saat check-in, tamu wajib menitipkan KTP asli sebagai deposit.
• KTP akan dikembalikan saat check-out.
• Pemesanan yang sudah dibayar tidak dapat dibatalkan.
• Parkir hanya tersedia untuk sepeda motor. Tidak tersedia parkir mobil.

=================================================================
HUBUNGI KAMI
=================================================================
Kazain Hotel
Jl. Prawirotaman I, Timuran No. 562
Yogyakarta 55153

WhatsApp: 0821-4233-1906 (24 Jam)

Email ini adalah bukti konfirmasi pembayaran resmi.
Email ini dikirim otomatis. Mohon tidak membalas email ini.

=================================================================
<?php /**PATH D:\KULIAH\hotelkazain\resources\views/mail/payment-confirmation-text.blade.php ENDPATH**/ ?>