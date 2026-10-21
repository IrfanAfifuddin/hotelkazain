# Panduan Belajar Backend Customer — Tahap 1

Panduan ini menjelaskan implementasi awal backend Customer Kazain Hotel: melihat ketersediaan kamar dan membuat satu booking tanpa akun. Pembayaran Midtrans, email, dan proses otomatis mengubah booking kedaluwarsa ke status `Expired` belum termasuk tahap ini.

## 1. Alur request

```text
GET /customer/availability?date=2026-10-16
    -> routes/customer.php
    -> Customer\BookingController::availability()
    -> AvailabilityService::checkAvailability()
    -> Booking::activeOverlapping() + Room
    -> JSON ketersediaan dan harga

POST /customer/bookings
    -> routes/customer.php
    -> StoreCustomerBookingRequest (validasi)
    -> Customer\BookingController::store()
    -> BookingService::createBooking()
    -> transaksi DB + lock kamar + cek bentrok
    -> Customer + Booking Pending
```

Customer tidak perlu login. Request POST tetap harus memakai `Accept: application/json` jika pemanggil mengharapkan error validasi dalam format JSON.

## 2. Endpoint yang tersedia

### Cek ketersediaan

```http
GET /customer/availability?date=2026-10-16
Accept: application/json
```

`date` opsional; jika tidak dikirim, server menggunakan tanggal hari ini. Format yang diterima adalah `YYYY-MM-DD` dan tanggal lampau ditolak.

Contoh respons:

```json
{
  "date": "2026-10-16",
  "data": [
    {
      "room_id": 1,
      "room_number": "101",
      "room_type": "King",
      "room_status": "Available",
      "is_available": true,
      "price_5_hours": 150000,
      "price_8_hours": 200000,
      "full_day_price": 350000
    }
  ],
  "summary": {
    "total_rooms": 1,
    "available_rooms": 1,
    "by_room_type": {
      "King": {
        "total": 1,
        "available": 1
      }
    }
  }
}
```

Availability tanggal ini memeriksa seluruh rentang hari. Sebelum menyimpan booking, backend melakukan pemeriksaan ulang untuk rentang check-in/check-out yang sebenarnya agar dua booking yang waktunya bertabrakan ditolak.

### Membuat booking

```http
POST /customer/bookings
Content-Type: application/json
Accept: application/json
```

Contoh payload:

```json
{
  "room_id": 1,
  "customer_name": "Andi",
  "phone_number": "081234567890",
  "email": "andi@example.com",
  "booking_type": "Full Day",
  "check_in_date": "2026-10-16",
  "check_in_time": "14:00",
  "duration_hours": 24,
  "payment_method": "Virtual Account",
  "agree_terms": true
}
```

Respons berhasil memiliki HTTP `201 Created`, kode booking berformat `KZN-XXXXXX`, status booking dan pembayaran `Pending`, serta waktu batas pembayaran 20 menit sejak booking dibuat.

### Aturan validasi dan perhitungan

- `room_id` harus menunjuk kamar yang ada dan berstatus `Available`.
- Nama, nomor telepon, dan email customer wajib diisi.
- Metode bayar yang diterima hanya `Virtual Account` atau `QRIS`.
- Syarat dan ketentuan wajib disetujui.
- Check-in harus pada waktu mendatang.
- `Short Time` hanya menerima durasi 5 atau 8 jam. Check-out dihitung dari check-in ditambah durasi, dan harga diambil dari kolom harga kamar yang sesuai.
- `Full Day` menggunakan check-in pukul 14:00 dan durasi kelipatan 24 jam. Satu malam check-out pukul 12:00 hari berikutnya; harga dikalikan jumlah malam.
- Klien tidak dapat menentukan `booking_status`, `payment_status`, `total_price`, atau `source`. Backend menetapkan nilai tersebut sendiri.
- Slot kamar yang tumpang tindih ditolak dengan HTTP `422`. Interval bersebelahan diperbolehkan: check-out booking lama tepat saat check-in booking baru tidak dianggap bentrok.

## 3. Penjelasan file

| File | Tanggung jawab |
|---|---|
| `routes/customer.php` | Mendefinisikan URL Customer dan menghubungkan route ke controller. |
| `app/Http/Controllers/Customer/BookingController.php` | Menangani HTTP: validasi tanggal availability, memilih JSON atau view, memanggil service, dan membentuk respons. |
| `app/Http/Requests/StoreCustomerBookingRequest.php` | Menjaga bentuk dan nilai input POST tetap valid sebelum business logic berjalan. |
| `app/Services/AvailabilityService.php` | Mengambil kamar dan booking aktif untuk menghitung ketersediaan serta ringkasan jumlah kamar. |
| `app/Services/BookingService.php` | Menghitung waktu dan harga, mengunci kamar dalam transaksi, memeriksa bentrok, menyimpan customer, lalu membuat booking Pending. |
| `app/Models/Booking.php` | Memuat relasi dan scope `activeOverlapping()` yang dipakai availability serta proses booking. |
| `database/migrations/*add_payment_expires_at_to_bookings_table.php` | Menambahkan waktu kedaluwarsa hold pembayaran ke tabel booking. |
| `tests/Feature/CustomerBookingTest.php` | Menguji kontrak HTTP, penyimpanan, validasi, konflik, dan availability sesudah hold kedaluwarsa. |

## 4. Konsep penting

### Validasi bukan pengganti transaksi

Pengecekan kamar tersedia di halaman availability hanya untuk memberi informasi. Data dapat berubah sesudah halaman dibuka. Karena itu `BookingService` mengunci baris kamar dengan `lockForUpdate()` di dalam `DB::transaction()`, lalu mengecek ulang bentrok sebelum menyimpan booking.

### Cara mendeteksi bentrok

Dua rentang waktu bertabrakan jika:

```text
booking_lama.mulai < booking_baru.selesai
AND booking_lama.selesai > booking_baru.mulai
```

Syarat ketat `<` dan `>` membuat dua interval yang bersentuhan tepat di batas check-out/check-in tetap diperbolehkan.

### Mengapa pembayaran berstatus Pending

Browser tidak dipercaya untuk menyatakan pembayaran sudah berhasil. Tahap ini selalu membuat `payment_status` dan `booking_status` sebagai `Pending`. Nantinya status berbayar harus ditetapkan dari notifikasi Midtrans yang diverifikasi server, bukan dari payload Customer.

### Hold pembayaran

`payment_expires_at` disimpan 20 menit setelah booking dibuat. Availability mengabaikan booking Pending jika waktunya sudah lewat, sehingga kamar bisa dipesan kembali. Tahap ini belum mengubah record tersebut menjadi status `Expired`; pekerjaan terjadwal untuk memperbarui status akan dibuat pada tahap lanjutan.

## 5. Menjalankan dan menguji

Jalankan migrasi untuk menambahkan kolom hold:

```powershell
php artisan migrate
```

Jalankan tes Customer:

```powershell
php artisan test tests\Feature\CustomerBookingTest.php --compact
```

Tes menggunakan database SQLite in-memory yang diatur di `phpunit.xml`, sehingga data tes tidak masuk ke database development.

## 6. Batas tahap ini dan pekerjaan berikutnya

- Tabel `bookings` hanya mempunyai satu `room_id`, jadi satu booking hanya dapat memesan satu kamar. Dukungan beberapa kamar memerlukan keputusan skema tambahan.
- Availability kalender saat ini merangkum kondisi per tanggal; status kalender seperti “Hampir Penuh” dan pemeriksaan jumlah kamar per slot masih perlu dikerjakan.
- Integrasi Midtrans, verifikasi webhook, pembuatan link/instruksi bayar, serta perubahan status pembayaran belum diimplementasikan.
- Pengiriman kode booking melalui email setelah pembayaran berhasil belum diimplementasikan.
- Belum ada scheduler yang mengubah booking Pending kedaluwarsa menjadi `Expired`.
