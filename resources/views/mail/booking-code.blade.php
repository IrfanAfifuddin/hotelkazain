<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Booking Kazain Hotel</title>
</head>
<body style="margin:0;background:#f4f3ef;color:#26332c;font-family:Arial,sans-serif;">
    <main style="max-width:600px;margin:32px auto;padding:32px;background:#fff;border:1px solid #dce2db;border-radius:12px;">
        <p style="margin:0;color:#59655c;">Kazain Hotel</p>
        <h1 style="margin:12px 0 8px;font-size:24px;">Pembayaran berhasil</h1>
        <p style="margin:0 0 24px;color:#59655c;">Terima kasih, {{ $booking->customer->customer_name }}. Pemesanan Anda telah dikonfirmasi.</p>

        <p style="margin:0;color:#59655c;">Kode booking</p>
        <p style="margin:6px 0 24px;font-family:monospace;font-size:28px;font-weight:bold;letter-spacing:3px;">{{ $booking->booking_code }}</p>

        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:#59655c;">Kamar</td><td style="padding:8px 0;text-align:right;">{{ $booking->room->room_number }} ({{ $booking->room->room_type }})</td></tr>
            <tr><td style="padding:8px 0;color:#59655c;">Check-in</td><td style="padding:8px 0;text-align:right;">{{ $booking->check_in_time->format('d M Y, H:i') }}</td></tr>
            <tr><td style="padding:8px 0;color:#59655c;">Check-out</td><td style="padding:8px 0;text-align:right;">{{ $booking->check_out_time->format('d M Y, H:i') }}</td></tr>
            <tr><td style="padding:8px 0;color:#59655c;">Total dibayar</td><td style="padding:8px 0;text-align:right;">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td></tr>
        </table>

        <p style="margin:24px 0 0;color:#59655c;">Simpan email ini dan tunjukkan kode booking saat check-in.</p>
    </main>
</body>
</html>
