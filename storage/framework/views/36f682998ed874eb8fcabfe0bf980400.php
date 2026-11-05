<?php
    $checkIn = $booking->check_in_time->copy()->locale('id');
    $checkOut = $booking->check_out_time->copy()->locale('id');

    if ($booking->booking_type === 'Full Day') {
        $durasi = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24)) . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }

    $ink = '#26211D';
    $gold = '#8A6A3B';
    $muted = '#6B6460';
    $line = '#E8E2D8';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan — <?php echo e($booking->booking_code); ?></title>
</head>
<body style="margin:0;padding:0;background:#F7F3EC;font-family:Arial,Helvetica,sans-serif;color:<?php echo e($ink); ?>;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F3EC;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border:1px solid <?php echo e($line); ?>;">

                    
                    <tr>
                        <td style="background:<?php echo e($ink); ?>;padding:24px 32px;">
                            <p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:26px;color:#FFFFFF;">Kazain Hotel</p>
                            <p style="margin:4px 0 0;font-size:11px;letter-spacing:3px;color:#BFB6AA;text-transform:uppercase;">Prawirotaman · Yogyakarta</p>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <p style="margin:0;font-size:12px;letter-spacing:3px;color:<?php echo e($gold); ?>;text-transform:uppercase;font-weight:bold;">Konfirmasi Pemesanan</p>
                            <h1 style="margin:10px 0 8px;font-family:Georgia,'Times New Roman',serif;font-size:26px;font-weight:normal;color:<?php echo e($ink); ?>;">Terima kasih, <?php echo e($booking->customer->customer_name); ?></h1>
                            <p style="margin:0;font-size:15px;line-height:1.6;color:<?php echo e($muted); ?>;">Pemesanan Anda di Kazain Hotel telah berhasil. Berikut detail pemesanan Anda.</p>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:24px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:<?php echo e($ink); ?>;">
                                <tr>
                                    <td align="center" style="padding:22px 16px;">
                                        <p style="margin:0;font-size:11px;letter-spacing:3px;color:#BFB6AA;text-transform:uppercase;">Kode Booking</p>
                                        <p style="margin:8px 0 0;font-family:'Courier New',monospace;font-size:30px;font-weight:bold;letter-spacing:5px;color:#FFFFFF;"><?php echo e($booking->booking_code); ?></p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:10px 0 0;font-size:13px;color:<?php echo e($muted); ?>;text-align:center;">Tunjukkan kode ini kepada resepsionis saat check-in.</p>
                        </td>
                    </tr>

                     
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Nama Pemesan</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($booking->customer->customer_name); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Nomor Telepon</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($booking->customer->phone_number); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Tipe Pemesanan</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($booking->booking_type); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Check-in</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($checkIn->translatedFormat('l, d F Y, H:i')); ?> WIB</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Check-out</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($checkOut->translatedFormat('l, d F Y, H:i')); ?> WIB</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Durasi</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($durasi); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:<?php echo e($muted); ?>;">Metode Pembayaran</td>
                                    <td style="padding:10px 0;text-align:right;"><?php echo e($booking->payment_method); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:16px 32px;">
                            <p style="margin:0 0 10px;font-size:14px;font-weight:bold;color:<?php echo e($ink); ?>;">Kamar yang Dipesan:</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border-left:3px solid <?php echo e($gold); ?>;">
                                <?php $__currentLoopData = $booking->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td style="padding:8px 0 8px 12px;">
                                            <strong>No. <?php echo e($room->room_number); ?></strong> — <?php echo e($room->room_type); ?>

                                            <br><span style="color:<?php echo e($muted); ?>;">Harga: Rp <?php echo e(number_format($room->pivot->price, 0, ',', '.')); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="padding:14px 0 0 12px;border-top:1px dashed <?php echo e($line); ?>;">
                                        <strong>Total Harga</strong>
                                        <span style="float:right;font-size:18px;color:<?php echo e($gold); ?>;">Rp <?php echo e(number_format($booking->total_price, 0, ',', '.')); ?></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:8px 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F3EC;">
                                <tr>
                                    <td style="padding:18px 20px;font-size:13px;line-height:1.7;color:<?php echo e($ink); ?>;">
                                        <p style="margin:0 0 6px;font-weight:bold;">Informasi Penting</p>
                                        &bull; Saat check-in, tamu wajib menitipkan <strong>KTP asli</strong> kepada resepsionis sebagai deposit. KTP dikembalikan saat check-out.<br>
                                        &bull; Pemesanan yang sudah dibayar <strong>tidak dapat dibatalkan</strong>.<br>
                                        &bull; Parkir hanya tersedia untuk sepeda motor. Tidak tersedia parkir untuk mobil.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding:20px 32px 28px;border-top:1px solid <?php echo e($line); ?>;font-size:13px;line-height:1.7;color:<?php echo e($muted); ?>;">
                            <strong style="color:<?php echo e($ink); ?>;">Kazain Hotel</strong><br>
                            Jl. Prawirotaman I, Timuran No. 562, Brontokusuman, Mergangsan, Yogyakarta 55153<br>
                            WhatsApp: <a href="https://wa.me/6282142331906" style="color:<?php echo e($gold); ?>;">0821-4233-1906</a> · Resepsionis 24 jam
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;font-size:11px;color:#9A928A;">Email ini dikirim otomatis. Mohon tidak membalas email ini.</p>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH D:\KULIAH\hotelkazain\resources\views/mail/booking-confirmation.blade.php ENDPATH**/ ?>