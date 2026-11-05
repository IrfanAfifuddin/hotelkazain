@php
    $checkIn = $booking->check_in_time->copy()->locale('id');
    $checkOut = $booking->check_out_time->copy()->locale('id');

    if ($booking->booking_type === 'Full Day') {
        $durasi = max(1, (int) ceil($checkIn->diffInHours($checkOut) / 24)) . ' Malam';
    } else {
        $durasi = ($checkIn && $checkOut) ? round($checkIn->diffInMinutes($checkOut) / 60) . ' Jam' : '-';
    }
@endphp
=================================================================
KONFIRMASI PEMESANAN — KAZAIN HOTEL
=================================================================

Terima kasih, {{ $booking->customer->customer_name }}

Pemesanan Anda di Kazain Hotel telah berhasil.
Berikut detail pemesanan Anda:

-----------------------------------------------------------------
KODE BOOKING
-----------------------------------------------------------------

{{ $booking->booking_code }}

Tunjukkan kode ini kepada resepsionis saat check-in.

-----------------------------------------------------------------
DETAIL PEMESANAN
-----------------------------------------------------------------

Nama Pemesan      : {{ $booking->customer->customer_name }}
Nomor Telepon     : {{ $booking->customer->phone_number }}
Tipe Pemesanan    : {{ $booking->booking_type }}
Check-in          : {{ $checkIn->translatedFormat('l, d F Y, H:i') }} WIB
Check-out         : {{ $checkOut->translatedFormat('l, d F Y, H:i') }} WIB
Durasi            : {{ $durasi }}
Metode Pembayaran : {{ $booking->payment_method }}

-----------------------------------------------------------------
KAMAR YANG DIPESAN
-----------------------------------------------------------------

@foreach ($booking->rooms as $room)
• Kamar No. {{ $room->room_number }} — {{ $room->room_type }}
  Harga: Rp {{ number_format($room->pivot->price, 0, ',', '.') }}

@endforeach
-----------------------------------------------------------------
TOTAL HARGA: Rp {{ number_format($booking->total_price, 0, ',', '.') }}
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
