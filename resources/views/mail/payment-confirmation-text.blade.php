@php
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
@endphp
=================================================================
KONFIRMASI PEMBAYARAN — KAZAIN HOTEL
=================================================================

Terima kasih, {{ $booking->customer->customer_name }}

Pembayaran pemesanan kamar Anda di Kazain Hotel telah berhasil dikonfirmasi.

=================================================================
KODE BOOKING
=================================================================
{{ $booking->booking_code }}

Tunjukkan kode ini kepada resepsionis saat check-in.

=================================================================
INFORMASI PEMESAN
=================================================================
Nama         : {{ $booking->customer->customer_name }}
Email        : {{ $booking->customer->email }}
Telepon      : {{ $booking->customer->phone_number }}
Tanggal Pesan : {{ $booking->created_at->translatedFormat('d F Y, H:i') }} WIB

=================================================================
KAMAR YANG DIPESAN
=================================================================
{{ $roomList }}

Total Kamar  : {{ $booking->rooms->count() }}

=================================================================
JADWAL MENGINAP
=================================================================
Check-in     : {{ $checkIn->translatedFormat('l, d F Y - H:i') }} WIB
Check-out    : {{ $checkOut->translatedFormat('l, d F Y - H:i') }} WIB
Durasi       : {{ $durasi }}

=================================================================
RINGKASAN PEMBAYARAN
=================================================================
Metode Pembayaran    : {{ $booking->payment_method }}
ID Transaksi         : {{ $booking->midtrans_transaction_id ?? 'Diproses' }}
Total Pembayaran     : Rp {{ number_format($booking->total_price, 0, ',', '.') }}
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
