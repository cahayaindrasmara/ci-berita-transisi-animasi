# Aplikasi Backend Berita dengan CodeIgniter 4

Backend API untuk aplikasi berita yang dibuat dengan CodeIgniter 4.

## Persiapan

1. Pastikan Anda telah menginstal:

   - PHP 7.4 atau lebih tinggi
   - MySQL/MariaDB
   - Composer
   - XAMPP/server web lainnya

2. Clone repositori ini ke direktori web server Anda (misalnya: `htdocs` di XAMPP).

3. Jalankan perintah composer untuk menginstal dependensi:

   ```
   composer install
   ```

4. Konfigurasikan file `.env` dengan pengaturan database Anda:

   ```
   database.default.hostname = localhost
   database.default.database = db_news
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

5. Import database dari file `db_news.sql` ke server MySQL Anda.

## Menjalankan Aplikasi

1. Gunakan XAMPP/server web Anda untuk menjalankan aplikasi, atau gunakan server PHP internal dengan perintah:

   ```
   php spark serve
   ```

2. Akses aplikasi di `http://localhost:8080` (jika menggunakan server PHP internal), atau `http://localhost/ci-berita` (jika menggunakan XAMPP).

## API Endpoints

### Berita API

- **GET /api/berita**: Mendapatkan daftar semua berita
- **GET /api/berita/{id}**: Mendapatkan detail berita berdasarkan ID
- **POST /api/berita**: Membuat berita baru
- **PUT /api/berita/{id}**: Memperbarui berita berdasarkan ID
- **DELETE /api/berita/{id}**: Menghapus berita berdasarkan ID

## Struktur Database

Tabel **posting**:

- `id` (int): ID berita
- `judul` (varchar): Judul berita
- `isi` (text): Konten berita
- `gambar` (varchar): Nama file gambar berita
- `created_at` (timestamp): Waktu pembuatan berita
- `updated_at` (timestamp): Waktu pembaruan berita

## Integrasi dengan Flutter

Aplikasi ini dirancang untuk berintegrasi dengan aplikasi Flutter yang ada di direktori `flutter_berita_app`. Aplikasi Flutter mengakses API dari backend CodeIgniter 4 untuk menampilkan berita.
