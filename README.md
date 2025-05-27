# Aplikasi Berita dengan CodeIgniter 4 dan Flutter

Aplikasi berita sederhana dengan backend CodeIgniter 4 dan frontend Flutter.

## Struktur Proyek

- Backend: CodeIgniter 4
- Frontend: Flutter
- Database: MySQL

## Menjalankan Aplikasi

### 1. Backend (CodeIgniter 4)

1. **Import database**:

   - Import file `db_news.sql` ke MySQL menggunakan phpMyAdmin atau tool MySQL lainnya.

2. **Konfigurasi database**:

   - Salin file `.env` dari file `env` (jika belum ada):
     ```
     cp env .env
     ```
   - Edit file `.env` dan sesuaikan pengaturan database:
     ```
     database.default.hostname = localhost
     database.default.database = db_news
     database.default.username = root
     database.default.password =
     database.default.DBDriver = MySQLi
     ```

3. **Jalankan server CodeIgniter**:

   ```
   php spark serve
   ```

   Server akan berjalan di `http://localhost:8080` atau `http://localhost:8081` jika port 8080 sudah digunakan.

4. **Verifikasi API**:
   - Buka browser dan kunjungi `http://localhost:8080/api/berita` atau `http://localhost:8081/api/berita`.
   - Pastikan data JSON muncul tanpa error.

### 2. Frontend (Flutter)

1. **Masuk ke direktori flutter**:

   ```
   cd flutter_berita_app
   ```

2. **Dapatkan dependencies**:

   ```
   flutter pub get
   ```

3. **Sesuaikan URL API**:

   - Buka file `lib/services/api_service.dart`.
   - Pastikan URL baseUrl sesuai dengan port yang digunakan CI4:
     ```dart
     final String baseUrl = 'http://localhost:8081/api';
     ```
   - Jika menggunakan emulator Android, gunakan `10.0.2.2` alih-alih `localhost`:
     ```dart
     final String baseUrl = 'http://10.0.2.2:8081/api';
     ```

4. **Jalankan aplikasi Flutter**:
   ```
   flutter run
   ```

## Panduan Tambahan

- Untuk panduan lebih detail tentang pengembangan Flutter, lihat file `flutter_berita_app/PANDUAN_LOKAL.md`.
- Jika terjadi masalah CORS, lihat bagian "Mengatasi Masalah CORS" pada file panduan tersebut.

## Fitur Aplikasi

- Daftar berita
- Detail berita
- Gambar berita (jika tersedia)
- Informasi tanggal publikasi

## Endpoint API

- `GET /api/berita`: Mendapatkan semua berita
- `GET /api/berita/{id}`: Mendapatkan berita berdasarkan ID
- `POST /api/berita`: Membuat berita baru
- `PUT /api/berita/{id}`: Memperbarui berita
- `DELETE /api/berita/{id}`: Menghapus berita
