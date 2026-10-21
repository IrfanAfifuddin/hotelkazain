# Panduan Backend Customer

Panduan ini menjelaskan alur pemesanan Customer Kazain Hotel dari cek ketersediaan sampai pembayaran Midtrans terverifikasi dan kode booking dikirim melalui email. Satu transaksi hanya memesan satu kamar, sesuai struktur tabel `bookings` yang memiliki satu `room_id`.

## 1. Alur request

```text
GET /availability?date=2026-10-16
    -> routes/customer.php
    -> Customer\BookingController::availability()
    -> AvailabilityService::checkAvailability()
    -> Booking::activeOverlapping() + Room
    -> JSON ketersediaan dan harga

POST /bookings
    -> routes/customer.php
    -> StoreCustomerBookingRequest (validasi)
    -> Customer\BookingController::store()
    -> BookingService::createBooking()
    -> transaksi DB + lock kamar + cek bentrok
    -> Customer + Booking Pending + order ID Midtrans privat
    -> PaymentService::createPayment() -> Midtrans Snap
    -> browser diarahkan ke halaman pembayaran Midtrans

POST /payments/midtrans/notifications
    -> verifikasi signature dan nominal
    -> PaymentService::applyNotification()
    -> status Paid/Confirmed atau Expired/Cancelled
    -> EmailService mengirim kode booking setelah pembayaran terkonfirmasi

GET /success?order_id=...
    -> PaymentService::refreshStatus() sebagai verifikasi tambahan
    -> menampilkan kode booking hanya untuk pembayaran Paid
```

Customer tidak perlu login. Request POST tetap harus memakai `Accept: application/json` jika pemanggil mengharapkan error validasi dalam format JSON.

## 2. Endpoint yang tersedia

### Cek ketersediaan

```http
GET /availability?date=2026-10-16
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
POST /bookings
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

Respons berhasil memiliki HTTP `201 Created` dan URL pengalihan ke Midtrans. Kode booking tidak dikirim ke browser sebelum pembayaran berhasil. Backend membuat booking dan pembayaran dengan status `Pending`, serta batas pembayaran 20 menit sejak booking dibuat. Kegagalan gateway yang hasilnya tidak pasti mempertahankan hold sementara agar booking yang mungkin sudah dibuat di Midtrans tidak langsung dilepas.

### Midtrans dan email

- Midtrans Snap dibuat server-side menggunakan Server Key. Browser hanya menerima URL pembayaran, bukan kode booking atau Server Key.
- `Virtual Account` diarahkan ke metode `bank_transfer`; `QRIS` diarahkan ke metode `qris`.
- Webhook `POST /payments/midtrans/notifications` dikecualikan dari CSRF Laravel, tetapi hanya diproses setelah signature SHA-512 valid dan nominal cocok dengan total booking.
- Status `settlement` (atau `capture` dengan fraud status `accept`) mengubah booking menjadi `Paid`/`Confirmed`. Status `expire`, `deny`, dan `cancel` melepaskan hold.
- Callback sukses dari Midtrans juga memeriksa status melalui API server Midtrans; halaman sukses tidak mempercayai parameter browser sebagai bukti pembayaran.
- Kode booking berformat `KZN-XXXXXX` dikirim lewat email dan ditampilkan pada halaman status hanya setelah pembayaran terkonfirmasi. Pengiriman normal berikutnya untuk booking yang sama tidak mengirim email duplikat.

Untuk mencoba Midtrans, isi `MIDTRANS_SERVER_KEY` dari akun Sandbox di `.env` dan pastikan `MIDTRANS_IS_PRODUCTION=false`. Daftarkan URL `https://<domain-publik>/payments/midtrans/notifications` sebagai Payment Notification URL di dashboard Midtrans; Midtrans tidak dapat mengirim webhook langsung ke `localhost`.

Untuk pengiriman Gmail, atur mailer SMTP di `.env` menggunakan `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_SCHEME=tls`, `MAIL_USERNAME`, `MAIL_PASSWORD` berupa App Password Gmail, dan `MAIL_FROM_ADDRESS`. Jangan masukkan password atau Server Key ke source control atau chat. `MAIL_MAILER=log` hanya menulis email ke log dan tidak mengirimkannya ke Gmail.

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
- Callback Midtrans tidak dianggap valid hanya karena mencapai route; signature dan gross amount harus sesuai.

## 3. Penjelasan file

| File | Tanggung jawab |
|---|---|
| `routes/customer.php` | Mendefinisikan URL Customer dan menghubungkan route ke controller. |
| `app/Http/Controllers/Customer/BookingController.php` | Menangani HTTP: validasi tanggal availability, memilih JSON atau view, memanggil service, dan membentuk respons. |
| `app/Http/Requests/StoreCustomerBookingRequest.php` | Menjaga bentuk dan nilai input POST tetap valid sebelum business logic berjalan. |
| `app/Http/Controllers/Customer/PaymentController.php` | Menangani halaman pembayaran, webhook Midtrans, dan callback status sukses. |
| `app/Services/AvailabilityService.php` | Mengambil kamar dan booking aktif untuk menghitung ketersediaan serta ringkasan jumlah kamar. |
| `app/Services/BookingService.php` | Menghitung waktu dan harga, mengunci kamar dalam transaksi, memeriksa bentrok, menyimpan customer, lalu membuat booking Pending. |
| `app/Services/PaymentService.php` | Membuat transaksi Snap, memverifikasi notifikasi, dan menerapkan status transaksi. |
| `app/Services/EmailService.php` | Mengirim kode booking hanya setelah booking Paid/Confirmed. |
| `app/Mail/BookingCodeMail.php` dan `resources/views/mail/booking-code.blade.php` | Menyusun email konfirmasi berisi kode booking dan detail kamar. |
| `app/Models/Booking.php` | Memuat relasi dan scope `activeOverlapping()` yang dipakai availability serta proses booking. |
| `database/migrations/*add_payment_expires_at_to_bookings_table.php` | Menambahkan waktu kedaluwarsa hold pembayaran ke tabel booking. |
| `database/migrations/*add_midtrans_payment_fields_to_bookings_table.php` | Menyimpan ID transaksi, token/URL Snap, dan waktu pengiriman kode booking. |
| `tests/Feature/CustomerBookingTest.php` | Menguji kontrak HTTP, penyimpanan, validasi, konflik, dan availability sesudah hold kedaluwarsa. |
| `tests/Feature/CustomerPaymentTest.php` | Menguji signature webhook, perubahan status, callback Midtrans, dan pengiriman email idempoten. |

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

### Mengapa kode booking menunggu pembayaran

Browser tidak dipercaya untuk menyatakan pembayaran sudah berhasil. Booking dibuat `Pending` dan kode booking tidak dikirim ke browser/email sampai notifikasi Midtrans atau pemeriksaan status server mengonfirmasi pembayaran.

### Hold pembayaran

`payment_expires_at` disimpan 20 menit setelah booking dibuat. Availability mengabaikan booking Pending yang sudah melewati batas tersebut. Notifikasi Midtrans `expire` mengubah status booking menjadi `Expired`; bila notifikasi belum diterima, halaman status memeriksa keadaan transaksi melalui API Midtrans.

## 5. Menjalankan dan menguji

Jalankan migrasi untuk menambahkan kolom hold:

```powershell
php artisan migrate
```

Jalankan tes alur booking dan pembayaran:

```powershell
php artisan test tests\Feature\CustomerBookingTest.php --compact
php artisan test tests\Feature\CustomerPaymentTest.php --compact
```

Tes menggunakan database SQLite in-memory yang diatur di `phpunit.xml`, sehingga data tes tidak masuk ke database development.

## 6. Batas integrasi

- Satu booking hanya dapat memesan satu kamar karena tabel `bookings` menyimpan satu `room_id`. Mendukung beberapa kamar dalam satu transaksi memerlukan perubahan skema.
- Midtrans dan Gmail memerlukan kredensial Sandbox/SMTP yang disediakan melalui `.env`; integrasi nyata tidak dapat diuji sampai konfigurasi akun tersedia.
- Webhook memerlukan URL publik yang dapat dijangkau Midtrans. Untuk pengembangan lokal gunakan tunnel HTTPS dan daftarkan URL webhook tunnel di dashboard.
- `MAIL_MAILER=log` tidak mengirim email keluar. Gunakan SMTP Gmail/App Password atau mail provider lain untuk pengiriman sungguhan.
