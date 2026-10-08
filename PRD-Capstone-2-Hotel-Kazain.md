# PRD — Sistem Informasi Manajemen Kamar Berbasis Web pada Kazain Hotel

**Capstone Project 2 · Program Studi Teknologi Informasi, Universitas Muhammadiyah Yogyakarta**

| | |
|---|---|
| Versi | 0.1 (Draft) |
| Tanggal | 4 Oktober 2026 |
| Tim | Aulia Nurfitria Dewi, Irfan Afifuddin, Muhammad Akmal Taufansyah, Tasnim Fadilah Anwar |
| Dosen pembimbing | Dr. Reza Giga Isnanda, S.T., M.Sc. |
| Mitra | Kazain Hotel (UMKM perhotelan, Yogyakarta) |
| Sumber | Proposal Capstone 1 (Bab I–III, termasuk use case, ERD, RAT, activity diagram, wireframe) |
| Status | Draft untuk direview tim dan dospem. Bagian bertanda **[Perlu konfirmasi]** harus diputuskan sebelum development dimulai. |

---

## 1. Ringkasan

Kazain Hotel mengelola seluruh operasional (pemesanan, status kamar, transaksi, laporan) secara manual dengan Microsoft Excel. Reservasi offline dan reservasi online (WhatsApp) dicatat terpisah sehingga sering terjadi double booking, status kamar sering tidak akurat, dan penyusunan laporan penjualan lambat karena harus disalin ulang dari laporan harian ke laporan bulanan.

Capstone 2 membangun **sistem informasi manajemen hotel berbasis web** yang menyatukan semua proses tersebut dalam satu platform dengan tiga peran pengguna: **Admin (pemilik)**, **Resepsionis**, dan **Customer** (tanpa akun). Customer dapat memesan dan membayar langsung lewat website; Admin dan Resepsionis mengelola pemesanan dan status kamar; Admin mendapatkan laporan penjualan otomatis.

## 2. Latar Belakang dan Masalah

| # | Masalah (hasil wawancara 2x tatap muka + WhatsApp) | Akar masalah | Dampak |
|---|---|---|---|
| P1 | **Double booking** antara reservasi offline dan online | Dua jalur pemesanan dicatat di berkas terpisah dan tidak tersinkronisasi; admin belum sempat mengabari resepsionis | Resepsionis harus menyelesaikan konflik dan klarifikasi ke customer yang sudah membayar DP |
| P2 | Resepsionis sulit tahu **status kamar** secara cepat dan akurat | Status hanya diperbarui manual di Excel saat check-in/check-out | Harus cek ulang kamar secara fisik, pelayanan lambat |
| P3 | **Laporan penjualan** lambat dan rawan salah | Transaksi dicatat di laporan harian lalu disalin ulang ke laporan bulanan satu per satu | Admin terlambat memantau pendapatan dan menentukan strategi (mis. harga musim ramai) |

Pendukung dari wawancara: pihak hotel juga membutuhkan pengelolaan data kamar (harga dan fasilitas), dashboard operasional, dan pemesanan online langsung lewat website untuk mengatasi keterlambatan respons di WhatsApp.

## 3. Tujuan dan Metrik Keberhasilan

| Tujuan | Metrik | Target | Cara ukur |
|---|---|---|---|
| G1. Mencegah double booking dari semua kanal | Jumlah kasus double booking selama UAT dan periode uji coba | 0 kasus | Uji konkurensi (booking bersamaan pada kamar dan jam yang sama) + log uji coba di hotel |
| G2. Status kamar akurat dan cepat | Status kamar di sistem sama dengan kondisi aktual; waktu resepsionis mengecek ketersediaan | Sesuai aktual pada sampel pengecekan harian; waktu cek turun dibanding sebelum sistem **[Perlu konfirmasi baseline dengan mitra]** | Observasi + wawancara resepsionis saat UAT |
| G3. Laporan penjualan otomatis | Waktu menghasilkan laporan bulanan | Dari proses manual berjam-jam menjadi kurang dari 1 menit (filter lalu cetak/ekspor) | Demo ke admin; bandingkan total dengan rekap Excel periode yang sama |
| G4. Sistem layak pakai oleh mitra | Skor kepuasan UAT admin dan resepsionis; tugas inti dapat diselesaikan tanpa bantuan | Seluruh skenario UAT inti lulus; skor SUS ≥ 68 **[Usulan, setujui dengan dospem]** | Kuesioner SUS + checklist skenario |
| G5. Sistem live dan stabil | Deployment ke domain publik, uptime selama masa uji coba | Dapat diakses online; tidak ada gangguan yang menghentikan pelayanan jam operasional | Monitoring sederhana (uptime checker) |

## 4. Ruang Lingkup

### 4.1 Dalam lingkup (In Scope)
- Autentikasi Admin dan Resepsionis (akses via URL `/login`, tidak ditampilkan di halaman utama).
- Dashboard operasional (kamar tersedia, terisi, check-in dan check-out hari ini, pemesanan terbaru).
- Status ketersediaan kamar (lihat dan ubah status).
- Manajemen pemesanan (CRUD + pencarian) untuk reservasi offline dan online dalam satu data.
- Laporan penjualan (lihat, filter periode, cetak/ekspor) khusus Admin.
- Kelola akun resepsionis (CRUD) khusus Admin.
- Website customer: informasi hotel, kalender ketersediaan, pilih tipe pemesanan (Short Time / Full Day), pilih kamar, isi data diri, pembayaran online via Midtrans, kode booking via email.
- Deployment ke VPS/cloud hosting dengan domain, pelatihan singkat Admin dan Resepsionis, dokumentasi.

### 4.2 Di luar lingkup (Out of Scope)
Sesuai batasan masalah Capstone 1 dan hasil analisis:
- Aplikasi mobile native dan aplikasi desktop yang diinstal (platform dipilih: web).
- Integrasi kanal reservasi lain (OTA seperti Traveloka/Booking.com) dan integrasi otomatis dengan WhatsApp. Pemesanan via WhatsApp tetap dicatat manual oleh Admin/Resepsionis ke sistem.
- Laporan keuangan hotel secara keseluruhan (hanya laporan transaksi penjualan).
- Fitur lain yang tidak ada di proposal (housekeeping detail, inventori, payroll, loyalty/diskon).

> **[Perlu konfirmasi]** Fitur *extend* (perpanjangan durasi) ada di Excel laporan bulanan (kolom Extend TF dan Extend Cash) tetapi tidak ada di kebutuhan fungsional proposal. Putuskan: masuk lingkup, atau dicatat sebagai pengembangan lanjutan.

## 5. Pengguna dan Peran

| Peran | Deskripsi | Login | Kebutuhan utama |
|---|---|---|---|
| **Admin** | Pemilik hotel, satu orang. Mengelola data, memantau pemesanan WhatsApp, menyusun laporan | Ya | Kontrol penuh, laporan penjualan cepat dan akurat, kelola akun resepsionis |
| **Resepsionis** | Tiga orang di front desk. Melayani tamu langsung, mencatat pemesanan offline, check-in/check-out, mencatat pembayaran | Ya | Tahu status kamar seketika, input pemesanan cepat, tidak ada double booking |
| **Customer** | Calon tamu yang memesan lewat website | Tidak (wajib isi data diri) | Cek ketersediaan, pesan dan bayar mudah, dapat bukti pemesanan |

Matriks hak akses:

| Fitur | Admin | Resepsionis | Customer |
|---|:-:|:-:|:-:|
| Login/Logout | ✔ | ✔ | – |
| Dashboard | ✔ | ✔ | – |
| Lihat status kamar | ✔ | ✔ | – |
| CRUD + cari pemesanan | ✔ | ✔ | – |
| Laporan penjualan (lihat, filter, cetak) | ✔ | ✘ | – |
| Kelola akun resepsionis | ✔ | ✘ | – |
| Lihat info hotel, cek ketersediaan, pesan, bayar | – | – | ✔ |

## 6. Alur Pengguna Utama

**Alur A — Customer memesan online**
1. Buka website, lihat info hotel, masuk ke Cek Ketersediaan (kalender).
2. Pilih tanggal, pilih tipe pemesanan (Short Time 5/8 jam atau Full Day), pilih kamar (boleh lebih dari satu), cek detail pesanan.
3. Isi data diri (nama, telepon, email), setujui syarat dan ketentuan, pilih metode bayar (Virtual Account / QRIS).
4. Sistem membuat booking berstatus **Pending**, **menahan kamar** selama batas waktu bayar, dan menampilkan halaman pembayaran dengan hitung mundur.
5. Pembayaran berhasil (notifikasi Midtrans) → status **Paid/Confirmed**, kode booking dibuat dan dikirim ke email, halaman sukses menampilkan kode booking.
6. Pembayaran gagal atau kedaluwarsa → booking dibatalkan/Expired dan kamar kembali tersedia.

**Alur B — Resepsionis/Admin mencatat pemesanan offline atau WhatsApp**
Data Pemesanan → Tambah Pemesanan → isi data (kamar, tipe, tanggal dan jam, durasi, customer, pembayaran) → validasi bentrok jadwal → simpan → muncul di daftar dan langsung memengaruhi ketersediaan di kalender customer.

**Alur C — Check-in/Check-out**
Resepsionis mengubah status pemesanan (Check-in / Check-out) → status kamar diperbarui → dashboard dan kalender ikut berubah.

**Alur D — Laporan**
Admin buka Laporan Penjualan → pilih periode → Filter → lihat total transaksi dan pendapatan → Cetak/Ekspor.

## 7. Kebutuhan Fungsional

Prioritas memakai MoSCoW (M = Must, S = Should, C = Could).

### 7.1 Autentikasi dan Akses (Admin, Resepsionis)
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-AUTH-01 | Login memakai username dan password melalui URL `/login` | M | Kredensial benar → masuk dashboard sesuai role; salah → pesan "Username atau Password Salah" |
| FR-AUTH-02 | Verifikasi role; pengguna tanpa role Admin/Resepsionis ditolak | M | Muncul pesan "Akses Ditolak" dan kembali ke login |
| FR-AUTH-03 | Logout dengan dialog konfirmasi dan penghapusan sesi | M | Setelah logout, akses halaman internal diarahkan ke login |
| FR-AUTH-04 | Proteksi rute berbasis role (middleware) | M | Resepsionis yang membuka URL laporan atau kelola akun mendapat 403 |
| FR-AUTH-05 | Akun dengan status Nonaktif tidak bisa login | S | Akun nonaktif ditolak saat login |

### 7.2 Dashboard
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-DSH-01 | Menampilkan total kamar tersedia, total kamar terisi, jumlah check-in hari ini, jumlah check-out hari ini | M | Angka sesuai data aktual di database |
| FR-DSH-02 | Menampilkan daftar pemesanan terbaru | S | Menampilkan kode booking, kamar, customer, tipe, durasi, check-in/out, status |
| FR-DSH-03 | Menu navigasi sesuai role (Resepsionis tanpa Laporan dan Kelola Akun) | M | Menu tidak muncul untuk role yang tidak berhak |

### 7.3 Status Ketersediaan Kamar
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-ROOM-01 | Menampilkan seluruh kamar (nomor, tipe, status) dalam bentuk kartu, dengan ringkasan jumlah per status | M | Semua kamar tampil dengan status terkini |
| FR-ROOM-02 | Untuk kamar terisi, tampilkan nama tamu dan tanggal check-out | S | Sesuai booking yang sedang berjalan |
| FR-ROOM-03 | Mengubah status kamar secara manual lewat badge status | M | Perubahan tersimpan dan langsung terlihat oleh pengguna lain |
| FR-ROOM-04 | Status kamar berubah otomatis mengikuti siklus booking (check-in → terisi, check-out → sesuai aturan BR-12) | M | Tidak perlu update manual pada alur normal |
| FR-ROOM-05 | Admin mengelola data kamar (nomor, tipe, harga, fasilitas) | S | **[Perlu konfirmasi]** Disebut di analisis kebutuhan, tetapi tidak ada di tabel kebutuhan fungsional proposal |

### 7.4 Manajemen Pemesanan (Admin dan Resepsionis)
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-BKG-01 | Melihat daftar pemesanan (tabel + paginasi) dan kalender pemesanan dengan indikator jumlah booking per tanggal | M | Klik tanggal menampilkan detail booking tanggal itu |
| FR-BKG-02 | Tambah pemesanan: pilih kamar (tipe terisi otomatis), tipe pemesanan, tanggal/jam check-in, durasi, nama, telepon, email, status; check-out dihitung otomatis; kode booking dibuat otomatis | M | Data tersimpan, muncul di daftar, notifikasi sukses |
| FR-BKG-03 | **Validasi bentrok jadwal** sebelum menyimpan (lihat BR-01) | M | Booking yang bentrok ditolak dengan pesan jelas; berlaku sama untuk input manual dan online |
| FR-BKG-04 | Ubah pemesanan, dengan validasi bentrok yang sama | M | Perubahan tersimpan, daftar diperbarui |
| FR-BKG-05 | Hapus/batalkan pemesanan dengan dialog konfirmasi | M | Setelah konfirmasi, data hilang dari daftar aktif (lihat BR-11 soal soft delete) |
| FR-BKG-06 | Cari berdasarkan nama customer, kode booking, nomor kamar | M | Hasil muncul sesuai kata kunci |
| FR-BKG-07 | Mengubah status pemesanan (Pending, Confirmed, Check-in, Check-out, Cancelled) | M | Perubahan memicu pembaruan status kamar |
| FR-BKG-08 | Mencatat pembayaran offline (metode Cash/QRIS/transfer, nominal, waktu) pada pemesanan | M | Terhitung di laporan penjualan |
| FR-BKG-09 | Menandai pemesanan berasal dari kanal mana (Offline / WhatsApp / Website) | S | Berguna untuk evaluasi dan laporan; **[Usulan, belum ada di ERD]** |

### 7.5 Laporan Penjualan (khusus Admin)
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-RPT-01 | Melihat laporan transaksi: kode booking, kamar, customer, tipe kamar, durasi, total harga, tanggal transaksi | M | Data sama dengan data pemesanan yang dibayar |
| FR-RPT-02 | Filter berdasarkan rentang tanggal (harian, bulanan, periode bebas) | M | Hasil, total transaksi, dan total pendapatan sesuai periode |
| FR-RPT-03 | Cetak dan ekspor laporan | M | Dokumen siap cetak; ekspor CSV (wireframe) **[Perlu konfirmasi: format PDF/Excel?]** |
| FR-RPT-04 | Laporan memuat rincian pembayaran seperti Excel bulanan saat ini (DP, pelunasan, cash, extend) | S | Padanan kolom dengan laporan Excel agar admin tidak kehilangan informasi |

### 7.6 Kelola Akun Resepsionis (khusus Admin)
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-USR-01 | Melihat daftar akun resepsionis (nama, username, status) | M | Daftar tampil; password tidak pernah ditampilkan |
| FR-USR-02 | Tambah akun (nama, username, password + konfirmasi, status) dengan validasi | M | Username unik; password terenkripsi; pesan kesalahan jika tidak valid |
| FR-USR-03 | Ubah akun; password boleh dikosongkan bila tidak diubah | M | Perubahan tersimpan |
| FR-USR-04 | Hapus akun dengan konfirmasi | M | Akun hilang atau nonaktif; booking lama tetap utuh |

### 7.7 Website Customer
| ID | Kebutuhan | Prioritas | Kriteria penerimaan |
|---|---|---|---|
| FR-CUS-01 | Halaman utama: profil hotel, fasilitas kamar, FAQ, kontak/lokasi, tombol Pesan Sekarang | M | Informasi tampil lengkap dan responsif |
| FR-CUS-02 | Kalender ketersediaan per tanggal dengan label (Tersedia, Hampir Penuh, Penuh, Sudah Lewat) dan sisa kamar per tipe pemesanan | M | Status kalender konsisten dengan data booking (termasuk booking offline) |
| FR-CUS-03 | Pilih tipe pemesanan: Short Time (5 atau 8 jam) atau Full Day (per malam) + tabel harga | M | Harga sesuai data harga |
| FR-CUS-04 | Pilih tipe/jumlah kamar, tanggal dan jam check-in, jumlah tamu; check-out otomatis; ringkasan pesanan dan total harga real-time | M | Hanya kamar yang benar-benar tersedia pada slot waktu itu yang bisa dipilih |
| FR-CUS-05 | Isi data diri (nama, telepon, email) + persetujuan syarat dan ketentuan; tanpa akun | M | Validasi format email dan telepon; tidak bisa lanjut tanpa setuju |
| FR-CUS-06 | Pilih metode bayar (Virtual Account / QRIS) dan bayar via Midtrans, dengan hitung mundur batas bayar | M | Status berubah otomatis setelah notifikasi pembayaran |
| FR-CUS-07 | Halaman sukses menampilkan kode booking; kode juga dikirim via email (Gmail SMTP) | M | Email terkirim setelah status Paid; kode unik |
| FR-CUS-08 | Cek status pemesanan memakai kode booking | S | **[Perlu konfirmasi]** Proposal menyebut kode booking dipakai untuk melihat status pemesanan, tetapi belum ada use case dan wireframe-nya |

## 8. Aturan Bisnis

| ID | Aturan |
|---|---|
| BR-01 | Satu kamar tidak boleh punya dua pemesanan aktif yang rentang waktunya beririsan. Pemesanan aktif = Pending yang belum kedaluwarsa, Confirmed, dan Check-in. Berlaku untuk semua kanal. |
| BR-02 | Booking online dibuat berstatus Pending dan menahan kamar selama batas waktu bayar (wireframe menunjukkan hitung mundur 20 menit **[Perlu konfirmasi]**). Jika tidak dibayar → Expired dan slot dilepas otomatis. |
| BR-03 | Short Time: durasi 5 atau 8 jam. Full Day: dihitung per malam; wireframe menunjukkan check-in 14:00 dan check-out 12:00 **[Perlu konfirmasi]**. |
| BR-04 | Harga (dari wireframe customer, **[konfirmasi dengan admin]**): Short Time 5 jam King Rp150.000 / Twin Rp130.000; 8 jam King Rp200.000 / Twin Rp175.000; Full Day King Rp350.000 / Twin Rp300.000 per malam. |
| BR-05 | Total harga = jumlah harga seluruh kamar yang dipesan sesuai tipe dan durasi. |
| BR-06 | Kode booking unik, dibuat sistem. Format mengikuti wireframe customer `KZN-XXXXXX`. Wireframe admin memakai `BK-2024-001`, sehingga perlu diseragamkan. |
| BR-07 | Kode booking baru dikirim ke email customer setelah pembayaran berstatus Paid. |
| BR-08 | Customer tidak perlu akun, tetapi wajib mengisi nama, nomor telepon, dan email. Data ini hanya dapat dilihat Admin dan Resepsionis. |
| BR-09 | Laporan penjualan hanya menghitung transaksi yang sudah dibayar (usulan; **[konfirmasi dengan admin]**). Pembatalan dan Expired tidak dihitung sebagai pendapatan. |
| BR-10 | Hanya Admin yang dapat melihat laporan dan mengelola akun resepsionis. |
| BR-11 | Rekomendasi: penghapusan pemesanan memakai *soft delete* atau status Cancelled agar riwayat laporan dan audit tidak hilang. Proposal menyebut "hapus" tanpa menjelaskan mekanismenya. |
| BR-12 | Siklus status kamar: Tersedia → Terisi (saat check-in) → Sedang Dibersihkan (saat check-out) → Tersedia (setelah dibersihkan). **[Perlu konfirmasi]** ERD hanya memiliki Available/Occupied, sedangkan wireframe status kamar memiliki tiga status. |

## 9. Kebutuhan Non-Fungsional

| ID | Kebutuhan | Spesifikasi yang dapat diuji |
|---|---|---|
| NFR-01 | Akses internet | Sistem online; data tersimpan dan terbaca real-time dari database |
| NFR-02 | Hak akses berbeda | Otorisasi di sisi server (bukan hanya menyembunyikan menu) |
| NFR-03 | Keamanan autentikasi | Password di-hash (bcrypt bawaan Laravel), proteksi CSRF, sesi aman, batas percobaan login |
| NFR-04 | Keamanan data customer | Data pribadi hanya diakses Admin dan Resepsionis; HTTPS wajib |
| NFR-05 | Multi-perangkat | Responsif dan berjalan di browser desktop, laptop, dan smartphone tanpa instalasi |
| NFR-06 | Multi-pengguna | Admin dan beberapa resepsionis memakai sistem bersamaan tanpa konflik data (transaksi database + penguncian untuk pembuatan booking) |
| NFR-07 | Validasi input | Notifikasi jelas untuk data tidak valid atau tidak lengkap |
| NFR-08 | Kinerja | Halaman utama dimuat ≤ 3 detik pada koneksi normal; pengecekan ketersediaan dan penyimpanan booking ≤ 2 detik **[Usulan target]** |
| NFR-09 | Keandalan | Stabil selama jam operasional; backup database terjadwal **[Usulan]** |
| NFR-10 | Antarmuka sederhana | Resepsionis dapat menjalankan tugas inti setelah pelatihan singkat tanpa panduan teknis panjang |
| NFR-11 | Integritas pembayaran | Verifikasi tanda tangan notifikasi Midtrans; pemrosesan notifikasi idempoten (notifikasi ganda tidak membuat data ganda) |

## 10. Model Data

Berdasarkan RAT proposal (4 tabel) dengan catatan perbaikan.

**USER**: user_id (PK), username, password, user_name, role (Admin | Receptionist)
**CUSTOMER**: customer_id (PK), customer_name, phone_number, email
**ROOM**: room_id (PK), room_number, room_type (King | Twin), room_status, price_5_hours, price_8_hours, full_day_price
**BOOKING**: booking_id (PK), user_id (FK), customer_id (FK), room_id (FK), booking_code (UNIQUE), check_in_time, check_out_time, booking_status (Pending | Confirmed | Check-in | Check-out | Cancelled), payment_method (Cash | QRIS | Virtual Account), payment_status (Pending | Paid | Failed | Expired | Cancelled), payment_time, total_price

**Catatan perbaikan yang disarankan sebelum implementasi** (semuanya **[Perlu konfirmasi dengan tim]**):
1. **Satu booking, banyak kamar.** Wireframe customer memungkinkan memilih lebih dari satu kamar dalam satu pesanan, sedangkan BOOKING hanya punya satu `room_id`. Solusi: tambah tabel `booking_rooms` (booking_id, room_id, harga), atau batasi satu kamar per booking.
2. **`user_id` pada booking untuk pemesanan online.** Booking dari website tidak diproses user mana pun. Jadikan `user_id` nullable.
3. **Tipe pemesanan dan durasi.** Tidak ada kolom `booking_type` (Short Time/Full Day) dan `duration`; check-in/out saja tidak cukup untuk laporan "durasi" seperti di wireframe.
4. **Harga sebaiknya per tipe kamar**, bukan per kamar, atau minimal harga disalin ke booking saat transaksi (snapshot) agar laporan lama tidak berubah ketika harga diubah.
5. **Status kamar** perlu nilai "Sedang Dibersihkan" bila wireframe dipertahankan (BR-12).
6. **Rincian pembayaran.** Excel saat ini mencatat DP transfer, pelunasan, cash, dan extend. Pertimbangkan tabel `payments` terpisah (booking_id, metode, nominal, tipe, waktu) agar mendukung DP, pelunasan, dan perpanjangan.
7. **Kolom pendukung**: `payment_expires_at` (batas bayar/hold), `source` (kanal), `deleted_at` (soft delete), `midtrans_order_id`, status akun (Aktif/Nonaktif) pada USER.
8. **Relasi di ERD konseptual** menyebut User–Room "manages" (1:N) dan Customer–Room "books" (M:N), sedangkan RAT memakai Booking sebagai tabel transaksi. Sebaiknya narasi ERD dibuat konsisten dengan RAT.

## 11. Arsitektur dan Integrasi

| Komponen | Pilihan (dari proposal) |
|---|---|
| Backend dan frontend | Laravel v10+ (PHP 8.1+) dengan Blade |
| Database | MySQL |
| Payment gateway | Midtrans (Virtual Account, QRIS) |
| Email | Gmail SMTP (pengiriman kode booking) |
| Desain UI | Figma (wireframe sudah ada) |
| Version control | GitHub |
| Hosting | Cloud hosting/VPS + domain |
| Lokal/dev | XAMPP atau Laragon, VS Code, PlantUML |

**Keputusan teknis yang perlu diambil:**
- **Pembaruan "real-time".** Proposal menyebut real-time, tetapi Laravel + Blade tidak real-time secara bawaan. Opsi: (a) pembacaan data selalu langsung dari DB + *polling* ringan atau tombol refresh, (b) Livewire, (c) Laravel Reverb/WebSocket. Untuk skala 12 kamar dan 3–4 pengguna internal, opsi (a) atau (b) umumnya cukup. Yang terpenting adalah **konsistensi data**, bukan push notification.
- **Pencegahan double booking di level database.** Gunakan transaksi + penguncian baris (`SELECT … FOR UPDATE`) saat membuat booking, dan cek irisan waktu `new.start < existing.end AND new.end > existing.start`.
- **Pelepasan hold otomatis.** Scheduled command Laravel (scheduler/cron) untuk meng-expire booking Pending yang melewati batas bayar. Pastikan cron tersedia di hosting yang dipilih.
- **Webhook Midtrans** memerlukan URL publik HTTPS; saat development gunakan sandbox + tunnel.
- **Batas Gmail SMTP.** Cukup untuk skala UMKM, namun perlu *app password* dan ada risiko masuk spam; sediakan opsi kirim ulang kode booking.

## 12. Desain dan UX

Wireframe lengkap sudah tersedia di proposal Bab II (Gambar 28–56) dan menjadi acuan implementasi: login, dashboard, data pemesanan (kalender + tabel), tambah/edit/hapus pemesanan, status kamar, laporan, kelola akun, serta alur customer 5 langkah (Cek Ketersediaan → Pilih Pemesanan → Pilih Kamar → Isi Data Diri → Pembayaran).

Prinsip UI: sederhana dan konsisten, sidebar gelap untuk area internal, feedback jelas (notifikasi sukses/gagal, dialog konfirmasi untuk aksi destruktif), responsif untuk smartphone.

**Temuan pada wireframe yang perlu dirapikan saat implementasi:**
- Contoh data dummy di wireframe memakai tahun 2024 dan total kamar (12 tersedia + 8 terisi) tidak cocok dengan 12 kamar di halaman status kamar.
- Halaman Edit Akun menampilkan isi password; di sistem nyata password tidak boleh ditampilkan, kolom dikosongkan.
- Gambar 48 (Logout Resepsionis) menampilkan halaman Kelola Akun yang seharusnya tidak dapat diakses resepsionis; ganti tampilannya.
- Caption Gambar 56 tertulis "Riwayat Pesan" padahal isinya halaman sukses/kode booking.
- Penomoran sub-bab di proposal bentrok: Activity Diagram Customer dan Wireframe Resepsionis sama-sama bernomor 2.2.5.2 (seharusnya Activity Diagram Customer = 2.2.4.2); rapikan di laporan Capstone 2.

## 13. Rencana Rilis dan Milestone

Proposal merencanakan 4 bulan dengan 4 tahap. Berikut pembagian rilis yang diusulkan, mengikuti urutan pengembangan di Bab III (tanggal mengikuti kalender semester, **[tentukan bersama dospem]**).

| Rilis | Cakupan | Penanggung jawab utama (sesuai proposal) | Kriteria selesai |
|---|---|---|---|
| **R0 — Fondasi** | Setup repo GitHub, project Laravel, skema database dan migrasi, seeder kamar dan akun, autentikasi + role | Irfan | Login Admin/Resepsionis berjalan, database siap |
| **R1 — Operasional internal** | Dashboard, status kamar, CRUD pemesanan + validasi bentrok + pencarian, kelola akun resepsionis | Irfan | Resepsionis dapat mencatat dan melihat pemesanan tanpa bentrok |
| **R2 — Website customer** | Halaman info, kalender ketersediaan, alur pesan 5 langkah, integrasi Midtrans, email kode booking | Akmal | Customer dapat memesan, membayar (sandbox), dan menerima kode |
| **R3 — Laporan dan penyesuaian UI** | Laporan penjualan (filter, cetak/ekspor), penyesuaian UI seluruh website | Tasnim (UI), Irfan/Akmal (laporan) | Laporan sama dengan rekap Excel periode uji |
| **R4 — Pengujian** | Unit test, integration test (fokus skenario double booking), UAT dengan Admin dan Resepsionis, kumpulan dan analisis feedback | Aulia (testing/feedback), Irfan (UAT) | Semua skenario kritis lulus; daftar feedback terdokumentasi |
| **R5 — Penyempurnaan dan deployment** | Perbaikan bug dan fitur dari feedback, deployment ke hosting + domain, Midtrans mode produksi, pelatihan Admin dan Resepsionis | Irfan (perbaikan, pelatihan), Akmal (deployment) | Sistem live, pelatihan selesai |
| **R6 — Dokumentasi dan laporan akhir** | Dokumentasi sistem, panduan pengguna per peran, laporan akhir Capstone 2, presentasi | Tasnim (dokumentasi), Aulia (laporan) | Laporan akhir siap diajukan ke dospem dan penguji |

> Pembagian PJ di atas mengikuti Tabel 5 proposal. Karena modul dan kompleksitasnya, beban R1 dan R5 menumpuk di satu orang; pertimbangkan pairing agar tidak jadi bottleneck.

## 14. Strategi Pengujian dan Kriteria Penerimaan

**Level pengujian:** unit testing (logika harga, durasi, validasi bentrok, pembuatan kode booking), integration/feature testing (alur booking end-to-end, webhook pembayaran, role access), UAT dengan Admin dan Resepsionis di Kazain Hotel.

**Skenario kritis (wajib lulus):**
1. Dua customer online memesan kamar dan jam yang sama secara bersamaan → hanya satu yang berhasil.
2. Resepsionis input booking offline pada slot yang baru saja di-hold customer online → ditolak.
3. Booking online tidak dibayar sampai batas waktu → otomatis Expired, kamar tersedia lagi di kalender.
4. Pembayaran berhasil → status Paid, kode booking dikirim, kamar terblokir di kalender dan status kamar.
5. Notifikasi Midtrans terkirim dua kali → tidak ada data ganda.
6. Short Time 5/8 jam dan Full Day menghasilkan check-out dan total harga yang benar, termasuk lintas tengah malam.
7. Resepsionis mengakses URL laporan dan kelola akun → ditolak.
8. Laporan bulanan sama dengan rekap Excel manual untuk periode yang sama.
9. Edit booking ke slot yang bentrok → ditolak.

**Definition of Done tiap fitur:** sesuai wireframe dan kriteria penerimaan, validasi dan pesan error ada, tidak ada bug blocker, sudah di-review dan di-merge lewat pull request, lulus test otomatis terkait.

## 15. Anggaran

| Item | Biaya |
|---|---|
| Domain (tahunan) | Rp 200.000 |
| Cloud hosting (tahunan) | Rp 500.000 |
| **Total** | **Rp 700.000** |

**Catatan:** biaya yang belum masuk anggaran proposal dan perlu dipastikan: biaya transaksi Midtrans (dipotong per transaksi, siapa yang menanggung: hotel atau customer?), spesifikasi hosting yang mendukung cron/scheduler dan SSL, serta apakah hosting shared cukup atau perlu VPS (proposal menyebut VPS pada deployment tetapi "cloud hosting" pada anggaran).

## 16. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Aturan bisnis belum final (status kamar, harga, check-in/out, hold, extend) | Rework di tengah development | Review dokumen ini dengan admin hotel dan dospem sebelum R0 selesai; selesaikan semua item **[Perlu konfirmasi]** |
| Race condition menyebabkan double booking tetap terjadi | Tujuan utama gagal | Transaksi + row locking, uji konkurensi otomatis (skenario 1 dan 2) |
| Integrasi Midtrans (webhook, verifikasi, mode produksi) | Pembayaran tidak tercatat | Mulai dari sandbox sejak R2, idempotensi, log notifikasi, halaman verifikasi manual untuk admin |
| Email masuk spam atau gagal terkirim | Customer tidak menerima kode | Tampilkan kode di halaman sukses, tombol kirim ulang, SPF/DKIM bila memakai domain sendiri |
| Resepsionis dan Admin tidak terbiasa dengan sistem baru | Sistem ditinggalkan, kembali ke Excel | Pelatihan singkat, UI sederhana, masa transisi paralel dengan Excel |
| Waktu terbatas dan ketergantungan antar anggota | Jadwal mundur | Milestone mingguan, rilis bertahap, review kode lewat pull request |
| Keamanan data customer dan akun | Kebocoran data | HTTPS, hash password, otorisasi server-side, backup terjadwal |
| Ketersediaan mitra untuk UAT | Feedback terlambat | Jadwalkan UAT sejak awal, siapkan skenario UAT tertulis |

## 17. Asumsi dan Ketergantungan

- Kazain Hotel memiliki 12 kamar dengan dua tipe (King dan Twin); daftar akhir dan harga dikonfirmasi admin.
- Hotel mengelola pemesanan WhatsApp secara manual dan memasukkannya ke sistem (tidak ada integrasi WhatsApp).
- Hotel memiliki akun yang dapat didaftarkan ke Midtrans (verifikasi merchant dapat memakan waktu; mulai lebih awal).
- Koneksi internet tersedia di front desk selama jam operasional.
- Pembayaran online melalui Midtrans; pembayaran offline dicatat manual oleh resepsionis.
- Dosen pembimbing menyetujui perubahan lingkup yang diputuskan pada bagian Pertanyaan Terbuka.

## 18. Pertanyaan Terbuka untuk Diputuskan

| # | Pertanyaan | Pemilik keputusan | Usulan |
|---|---|---|---|
| Q1 | Pembayaran online penuh (pelunasan) atau DP? Latar belakang menyebut DP, kebutuhan fungsional menyebut pelunasan. | Admin hotel | Pelunasan penuh di website agar sederhana; DP dicatat hanya pada booking manual |
| Q2 | Apakah fitur extend (perpanjangan) masuk lingkup? | Tim + dospem + admin | Masukkan sebagai Should (R3) bila waktu cukup |
| Q3 | Apakah Admin perlu mengelola data kamar dan harga lewat sistem? | Admin hotel | Ya, minimal ubah harga dan status aktif kamar |
| Q4 | Status kamar: dua atau tiga status? | Tim + admin | Tiga status sesuai wireframe; perbarui ERD/RAT |
| Q5 | Satu booking boleh lebih dari satu kamar? | Tim | Ya (sesuai wireframe), tambah tabel `booking_rooms` |
| Q6 | Batas waktu bayar dan aturan jam check-in/out Full Day | Admin hotel | 20 menit, 14:00/12:00 sesuai wireframe |
| Q7 | Mekanisme hapus: hard delete atau soft delete/cancel? | Tim | Soft delete + status Cancelled |
| Q8 | Format ekspor laporan: CSV, PDF, atau Excel? | Admin hotel | PDF untuk cetak + CSV/Excel untuk olah data |
| Q9 | Siapa menanggung biaya Midtrans? | Admin hotel | Ditentukan hotel; tampilkan sebagai biaya terpisah hanya bila diperlukan |
| Q10 | Fitur cek status pemesanan memakai kode booking (customer) | Tim | Masukkan sebagai Should; halaman sederhana input kode |
| Q11 | Level "real-time" yang diterima (polling/Livewire/WebSocket) | Tim + dospem | Polling/Livewire cukup |

## 19. Keterlacakan terhadap Proposal Capstone 1

| Kebutuhan proposal | ID PRD |
|---|---|
| Admin: login, dashboard, status kamar, CRUD + cari pemesanan, laporan (lihat, filter, cetak), kelola akun resepsionis, logout (Tabel 1) | FR-AUTH, FR-DSH, FR-ROOM, FR-BKG, FR-RPT, FR-USR |
| Resepsionis: login, dashboard, status kamar, CRUD + cari pemesanan, logout (Tabel 2) | FR-AUTH, FR-DSH, FR-ROOM, FR-BKG |
| Customer: info hotel, kalender, pilih Short Time/Full Day, pilih kamar dan detail, pesan tanpa akun, bayar, kode booking via email (Tabel 3) | FR-CUS-01 s.d. FR-CUS-07 |
| Non-fungsional 1–10 (Tabel 4) | NFR-01 s.d. NFR-10 (+ NFR-11 tambahan) |
| Rumusan masalah 1 (double booking) | G1, BR-01, BR-02, FR-BKG-03, FR-CUS-02 |
| Rumusan masalah 2 (status kamar akurat) | G2, FR-ROOM-01 s.d. 04 |
| Rumusan masalah 3 (pencatatan dan laporan) | G3, FR-RPT, FR-BKG-08 |
| Tahapan, timeline, alat dan bahan, anggaran (Bab III) | Bagian 11, 13, 15 |
