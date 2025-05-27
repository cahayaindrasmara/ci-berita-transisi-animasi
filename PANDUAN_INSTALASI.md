# Panduan Instalasi dan Konfigurasi Aplikasi Berita CI4-Flutter

Panduan ini berisi langkah-langkah untuk menginstall dan mengkonfigurasi aplikasi berita dengan fitur notifikasi yang dibangun menggunakan CodeIgniter 4 (backend) dan Flutter (frontend).

## Persyaratan Sistem

### Backend (CodeIgniter 4)

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Web Server (Apache/Nginx)

### Frontend (Flutter)

- Flutter SDK >= 2.17.0
- Dart >= 2.17.0
- Android Studio / VS Code
- Device emulator atau perangkat fisik

## Instalasi Backend (CI-Berita)

### 1. Setup Database

1. Buat database bernama `db_news` di MySQL
2. Import file `db_news.sql` ke database

### 2. Konfigurasi CodeIgniter 4

1. Buka file `.env` dan sesuaikan konfigurasi database:

```
database.default.hostname = localhost
database.default.database = db_news
database.default.username = root
database.default.password = password
database.default.DBDriver = MySQLi
```

2. Sesuaikan URL base dan upload path di `.env`:

```
app.baseURL = 'http://localhost:8081'
```

3. Jalankan server development:

```bash
cd /path/to/ci-berita
php spark serve --port 8081
```

## Instalasi Frontend (Flutter Berita App)

### 1. Konfigurasi URL API

Buka file `lib/services/api_service.dart` dan sesuaikan URL API sesuai lingkungan:

```dart
// Pilihan 1: Desktop/Browser Web (localhost)
final String baseUrl = 'http://localhost:8081/api';

// Pilihan 2: Emulator Android
// final String baseUrl = 'http://10.0.2.2:8081/api';

// Pilihan 3: Perangkat Fisik (ganti dengan IP komputer Anda)
// final String baseUrl = 'http://192.168.1.X:8081/api';
```

**Catatan:** Pastikan untuk melakukan hal yang sama pada file `lib/services/notification_service.dart`.

### 2. Install Dependencies

Jalankan perintah berikut di direktori `flutter_berita_app`:

```bash
flutter pub get
```

### 3. Jalankan Aplikasi

```bash
flutter run
```

## Fitur Notifikasi

### Komponen Backend

1. Database memiliki tabel `notifications` untuk menyimpan notifikasi
2. API endpoint di `/api/notifications` untuk mengelola notifikasi
3. Notifikasi dibuat otomatis saat ada berita baru atau update

### Komponen Frontend

1. Sistem polling untuk memeriksa notifikasi baru setiap 30 detik
2. Badge menampilkan jumlah notifikasi yang belum dibaca
3. Layar khusus untuk menampilkan dan mengelola notifikasi

### Pengujian di Perangkat Lain

Untuk menjalankan aplikasi di perangkat lain, pastikan:

1. Server backend dan database sudah terkonfigurasi dengan benar
2. URL API di frontend sudah diubah menggunakan IP yang dapat diakses dari perangkat
3. Port 8081 tidak diblokir oleh firewall

## Troubleshooting

### Masalah CORS

Jika terjadi masalah CORS, pastikan header CORS sudah dikonfigurasi dengan benar di `app/Config/Routes.php` dan `app/Controllers/Api.php`.

### Masalah Koneksi

- Pastikan server backend berjalan dengan benar
- Pastikan URL API menggunakan IP yang benar
- Cek koneksi jaringan antara perangkat frontend dan server backend

### Masalah Notifikasi

- Periksa apakah service notifikasi berjalan dengan benar
- Cek apakah tabel `notifications` sudah dibuat dengan struktur yang benar
- Periksa log untuk melihat error yang terjadi saat polling notifikasi

## Kesimpulan

Aplikasi berita ini mendemonstrasikan implementasi:

1. RESTful API dengan CodeIgniter 4
2. Konsumsi API dengan Flutter
3. Sistem notifikasi yang bekerja di berbagai perangkat
4. Pengelolaan upload dan tampilan gambar

Dengan mengikuti panduan ini, aplikasi seharusnya dapat berjalan dengan baik di berbagai perangkat selama konfigurasi jaringan sudah benar.
