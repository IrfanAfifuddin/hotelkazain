@php
    $checkIn = $booking->check_in_time->copy()->locale('id');
    $checkOut = $booking->check_out_time->copy()->locale('id');

    if ($booking->booking_type === 'Full Day') {
        $durasi = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24)) . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }

    // Build room list
    $roomsGrouped = $booking->rooms->groupBy('room_type');
    $roomList = $roomsGrouped->map(function ($rooms, $type) {
        return $rooms->map(function ($room) use ($type) {
            return $type . ' Bed No. ' . $room->room_number;
        });
    })->flatten();

    // Color scheme
    $darkGreen = '#1B4332';
    $lightGreen = '#2D6A4F';
    $accentGold = '#D4A574';
    $white = '#FFFFFF';
    $lightGray = '#F5F5F5';
    $darkGray = '#333333';
    $mediumGray = '#666666';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Kazain Hotel</title>
</head>
<body style="margin:0;padding:0;background-color:{{ $lightGray }};font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:{{ $darkGray }};line-height:1.6;">

    <!-- Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $lightGray }};">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:{{ $white }};border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);overflow:hidden;">

                    <!-- Header with green background -->
                    <tr>
                        <td style="background-color:{{ $darkGreen }};padding:40px 30px;text-align:center;">
                            <h1 style="margin:0;font-size:32px;font-weight:300;color:{{ $white }};letter-spacing:1px;">KAZAIN HOTEL</h1>
                            <p style="margin:8px 0 0;font-size:12px;color:{{ $accentGold }};letter-spacing:2px;text-transform:uppercase;">Prawirotaman, Yogyakarta</p>
                        </td>
                    </tr>

                    <!-- Success Message -->
                    <tr>
                        <td style="padding:40px 30px;text-align:center;border-bottom:2px solid {{ $lightGray }};">
                            <div style="font-size:48px;margin-bottom:16px;">✓</div>
                            <h2 style="margin:0 0 12px;font-size:28px;color:{{ $lightGreen }};font-weight:600;">Pembayaran Berhasil</h2>
                            <p style="margin:0;font-size:16px;color:{{ $mediumGray }};font-weight:300;">Terima kasih atas kepercayaan Anda kepada Kazain Hotel</p>
                        </td>
                    </tr>

                    <!-- Booking Code Section -->
                    <tr>
                        <td style="padding:30px;text-align:center;background-color:{{ $darkGreen }};">
                            <p style="margin:0 0 12px;font-size:12px;color:{{ $accentGold }};letter-spacing:2px;text-transform:uppercase;font-weight:600;">Kode Booking Anda</p>
                            <p style="margin:0;font-family:'Courier New',monospace;font-size:36px;font-weight:bold;letter-spacing:4px;color:{{ $accentGold }};">{{ $booking->booking_code }}</p>
                            <p style="margin:12px 0 0;font-size:12px;color:{{ $white }};font-style:italic;">Tunjukkan kode ini saat check-in</p>
                        </td>
                    </tr>

                    <!-- Informasi Pemesan -->
                    <tr>
                        <td style="padding:30px;border-bottom:1px solid {{ $lightGray }};">
                            <h3 style="margin:0 0 20px;font-size:14px;color:{{ $darkGreen }};letter-spacing:1px;text-transform:uppercase;font-weight:600;">Informasi Pemesan</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};width:40%;">Nama</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $booking->customer->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};">Email</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;word-break:break-word;">{{ $booking->customer->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};">Telepon</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $booking->customer->phone_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};">Tanggal Pemesanan</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $booking->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Detail Kamar -->
                    <tr>
                        <td style="padding:30px;border-bottom:1px solid {{ $lightGray }};">
                            <h3 style="margin:0 0 20px;font-size:14px;color:{{ $darkGreen }};letter-spacing:1px;text-transform:uppercase;font-weight:600;">Kamar yang Dipesan</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                @foreach($roomList as $room)
                                    <tr>
                                        <td style="padding:10px 0;color:{{ $darkGray }};border-bottom:1px solid {{ $lightGray }};">{{ $room }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:10px 0;color:{{ $mediumGray }};font-size:13px;">
                                        Total: <strong>{{ $booking->rooms->count() }} Kamar</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Jadwal Menginap -->
                    <tr>
                        <td style="padding:30px;border-bottom:1px solid {{ $lightGray }};">
                            <h3 style="margin:0 0 20px;font-size:14px;color:{{ $darkGreen }};letter-spacing:1px;text-transform:uppercase;font-weight:600;">Jadwal Menginap</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};width:40%;">Check-in</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $checkIn->translatedFormat('l, d F Y - H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};">Check-out</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $checkOut->translatedFormat('l, d F Y - H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:{{ $mediumGray }};">Durasi</td>
                                    <td style="padding:8px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $durasi }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Ringkasan Pembayaran -->
                    <tr>
                        <td style="padding:30px;background-color:{{ $lightGray }};border-bottom:1px solid #E0E0E0;">
                            <h3 style="margin:0 0 20px;font-size:14px;color:{{ $darkGreen }};letter-spacing:1px;text-transform:uppercase;font-weight:600;">Ringkasan Pembayaran</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr>
                                    <td style="padding:10px 0;color:{{ $mediumGray }};">Metode Pembayaran</td>
                                    <td style="padding:10px 0;color:{{ $darkGray }};font-weight:500;text-align:right;">{{ $booking->payment_method }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:{{ $mediumGray }};">ID Transaksi</td>
                                    <td style="padding:10px 0;color:{{ $darkGray }};font-weight:500;text-align:right;font-family:'Courier New',monospace;font-size:12px;">{{ $booking->midtrans_transaction_id ?? 'Diproses' }}</td>
                                </tr>
                                <tr style="border-top:2px solid {{ $accentGold }};">
                                    <td style="padding:15px 0;font-size:16px;font-weight:600;color:{{ $darkGreen }};">Total Pembayaran</td>
                                    <td style="padding:15px 0;font-size:20px;font-weight:700;color:{{ $darkGreen }};text-align:right;">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:10px 0;text-align:center;color:{{ $lightGreen }};font-weight:600;font-size:13px;">✓ PEMBAYARAN LUNAS</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Informasi Penting -->
                    <tr>
                        <td style="padding:30px;border-bottom:1px solid {{ $lightGray }};">
                            <h3 style="margin:0 0 16px;font-size:14px;color:{{ $darkGreen }};letter-spacing:1px;text-transform:uppercase;font-weight:600;">Informasi Penting</h3>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:{{ $mediumGray }};line-height:1.8;">
                                <li style="margin-bottom:8px;">Saat check-in, tamu wajib menitipkan <strong>KTP asli</strong> sebagai deposit.</li>
                                <li style="margin-bottom:8px;">KTP akan dikembalikan saat check-out.</li>
                                <li style="margin-bottom:8px;">Pemesanan yang sudah dibayar <strong>tidak dapat dibatalkan</strong>.</li>
                                <li>Parkir hanya tersedia untuk sepeda motor. Tidak tersedia parkir mobil.</li>
                            </ul>
                        </td>
                    </tr>

                    <!-- Contact Footer -->
                    <tr>
                        <td style="padding:30px;background-color:{{ $darkGreen }};text-align:center;color:{{ $white }};">
                            <p style="margin:0 0 12px;font-size:14px;font-weight:600;">Hubungi Kami</p>
                            <p style="margin:0 0 4px;font-size:13px;">Jl. Prawirotaman I, Timuran No. 562</p>
                            <p style="margin:0 0 4px;font-size:13px;">Yogyakarta 55153</p>
                            <p style="margin:12px 0 0;font-size:13px;">
                                <a href="https://wa.me/6282142331906" style="color:{{ $accentGold }};text-decoration:none;">WhatsApp: 0821-4233-1906</a> · 24 Jam
                            </p>
                            <p style="margin:16px 0 0;font-size:12px;color:{{ $accentGold }};font-style:italic;">Email ini adalah bukti konfirmasi pembayaran resmi.</p>
                        </td>
                    </tr>

                    <!-- Bottom spacing -->
                    <tr>
                        <td style="padding:20px;text-align:center;font-size:11px;color:{{ $mediumGray }};">
                            Email ini dikirim otomatis. Mohon tidak membalas email ini.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
