# KONSUMSI API & NETWORKING MYSQL

## Catatan Materi untuk Slides

---

## 1. ARSITEKTUR SISTEM

- **Backend**:

  - CodeIgniter 4 sebagai framework PHP
  - RESTful API untuk komunikasi data
  - Controller API yang menangani request/response
  - Model untuk interaksi dengan database

- **Frontend**:

  - Flutter sebagai framework cross-platform
  - Service layer untuk konsumsi API
  - Model untuk representasi data
  - Provider pattern untuk state management

- **Database**:

  - MySQL sebagai RDBMS
  - Tabel `posting` untuk menyimpan berita
  - Koneksi langsung dengan backend

- **Komunikasi**:
  - HTTP/HTTPS sebagai protokol transfer
  - Format JSON untuk pertukaran data
  - CORS untuk keamanan cross-origin

![Arsitektur Sistem]

---

## 2. DATABASE MYSQL

- **Nama Database**: `db_news`
- **Tabel Utama**: `posting`

**Struktur Tabel:**

```sql
CREATE TABLE `posting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT 'no-image.jpg',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
)
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','berita_baru','update','umum') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `target_id` int(11) DEFAULT NULL,
  `device_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Penjelasan Field:**

- `id`: Primary key auto increment
- `judul`: Judul berita (tidak boleh kosong)
- `isi`: Konten berita (tidak boleh kosong)
- `gambar`: Path file gambar (default: 'no-image.jpg')
- `created_at`: Timestamp pembuatan record
- `updated_at`: Timestamp update terakhir

---

## 3. BACKEND DENGAN CODEIGNITER 4

- **BaseURL API**: `http://localhost:8081/api`
- **Controller**: `app/Controllers/Api.php`
- **Model**: `app/Models/BeritaModel.php`

**Struktur Controller API:**

```php
class Api extends ResourceController
{
    use ResponseTrait;
    protected $format = 'json';

    public function __construct() {
        // CORS headers
    }

    public function getBerita() {...}
    public function getBeritaById($id = null) {...}
    public function createBerita() {...}
    public function updateBerita($id = null) {...}
    public function deleteBerita($id = null) {...}
    public function uploadGambar() {...}
}
```

**Endpoint API:**

- `GET /berita` - Mengambil semua berita
- `GET /berita/{id}` - Mengambil berita berdasarkan ID
- `POST /berita` - Membuat berita baru
- `PUT /berita/{id}` - Memperbarui berita
- `DELETE /berita/{id}` - Menghapus berita
- `POST /upload` - Upload gambar

**Format Response:**

```json
{
  "status": true,
  "message": "Berita berhasil diambil",
  "data": [
    {
      "id": 1,
      "judul": "Judul Berita",
      "isi": "Isi berita...",
      "gambar": "uploads/berita/image.jpg",
      "created_at": "2023-09-05 10:00:00",
      "updated_at": "2023-09-05 10:00:00"
    }
  ]
}
```

**Konfigurasi CORS:**

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
```

---

## 4. FRONTEND DENGAN FLUTTER

- **Service Layer**: `flutter_berita_app/lib/services/api_service.dart`
- **Model**: `flutter_berita_app/lib/models/berita_model.dart`
- **State Management**: Provider Pattern

**Model Berita:**

```dart
class Berita {
  final int id;
  final String judul;
  final String isi;
  final String? gambar;
  final String? createdAt;
  final String? updatedAt;

  // Constructor, fromJson, toJson
}
```

**ApiService Class:**

```dart
class ApiService {
  final String baseUrl = 'http://localhost:8081/api';
  final String uploadBaseUrl = 'http://localhost:8081/uploads';

  Future<List<Berita>> getBerita() async {...}
  Future<Berita> getBeritaById(int id) async {...}
  Future<Map<String, dynamic>> createBerita(Berita berita) async {...}
  Future<Map<String, dynamic>> updateBerita(int id, Berita berita) async {...}
  Future<Map<String, dynamic>> deleteBerita(int id) async {...}
  Future<String> uploadGambar(File imageFile) async {...}
}
```

**Catatan Konfigurasi URL:**

```dart
// 1. Desktop/Browser Web: Gunakan localhost
final String baseUrl = 'http://localhost:8081/api';

// 2. Emulator Android: Gunakan 10.0.2.2 (pengganti localhost)
// final String baseUrl = 'http://10.0.2.2:8081/api';

// 3. Perangkat Fisik atau Emulator yang terhubung ke jaringan yang sama:
// final String baseUrl = 'http://192.168.1.X:8081/api';
```

**Contoh Konsumsi API:**

```dart
// Mengambil semua berita
Future<List<Berita>> getBerita() async {
  try {
    final response = await http.get(Uri.parse('$baseUrl/berita'));

    if (response.statusCode == 200) {
      final List<dynamic> jsonResponse = json.decode(response.body);
      return jsonResponse.map((data) => Berita.fromJson(data)).toList();
    } else {
      throw Exception('Gagal memuat berita: ${response.statusCode}');
    }
  } catch (e) {
    throw Exception('Tidak dapat memuat berita: $e');
  }
}
```

---

## 5. ALUR KOMUNIKASI DATA

1. **User** - Interaksi melalui Flutter UI (tap tombol, scroll, dll)
2. **Provider** - Menerima event dan memanggil ApiService
3. **ApiService** - Membuat HTTP Request dengan parameter yang sesuai
4. **CI4 API** - Menerima Request dan memproses di Controller
5. **Model CI4** - Query ke database MySQL
6. **Database** - Eksekusi query dan mengembalikan hasil
7. **CI4 API** - Memformat response JSON
8. **Flutter** - Parsing JSON ke objek Dart
9. **Provider** - Update state aplikasi
10. **UI** - Rebuild dengan data terbaru

**Diagram Alur:**

```
User → UI Flutter → Provider → ApiService → HTTP Request →
  → CI4 API Controller → Model → Database →
  → Response JSON → ApiService → Provider → UI → User
```

---

## 6. IMPLEMENTASI KONSUMSI API

**Metode HTTP:**

- **GET** - Membaca data (READ)
  ```dart
  await http.get(Uri.parse('$baseUrl/berita'));
  ```
- **POST** - Menambah data (CREATE)
  ```dart
  await http.post(
    Uri.parse('$baseUrl/berita'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode(berita.toJson()),
  );
  ```
- **PUT** - Memperbarui data (UPDATE)
  ```dart
  await http.put(
    Uri.parse('$baseUrl/berita/$id'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode(berita.toJson()),
  );
  ```
- **DELETE** - Menghapus data (DELETE)
  ```dart
  await http.delete(Uri.parse('$baseUrl/berita/$id'));
  ```

**Contoh kode Provider Pattern:**

```dart
class BeritaProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<Berita> _beritaList = [];
  bool _isLoading = false;
  String? _error;

  // Getters
  List<Berita> get beritaList => _beritaList;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchBerita() async {
    _isLoading = true;
    notifyListeners();

    try {
      _beritaList = await _apiService.getBerita();
      _error = null;
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Metode lainnya (create, update, delete)
}
```

---

## 7. UPLOAD FILE DENGAN API

**Tahapan Upload File:**

1. **Memilih File** - Gunakan image_picker untuk memilih gambar
2. **Multipart Request** - Buat request dengan FormData
3. **Server Processing** - Backend menerima dan memproses upload
4. **Penyimpanan** - File disimpan di direktori uploads
5. **Response** - Server mengembalikan path file

**Flutter - Multipart Request:**

```dart
Future<String> uploadGambar(File imageFile) async {
  try {
    // Validasi file
    String fileName = imageFile.path.split('/').last;
    String extension = fileName.split('.').last.toLowerCase();

    if (!['jpg', 'jpeg', 'png', 'gif'].contains(extension)) {
      throw Exception('Format file tidak didukung');
    }

    if (await imageFile.length() > 5 * 1024 * 1024) { // 5MB
      throw Exception('Ukuran file terlalu besar. Maksimal 5MB.');
    }

    // Buat request multipart
    var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/upload'));

    // Tambahkan file ke request
    var multipartFile = http.MultipartFile(
      'gambar',
      imageFile.openRead(),
      await imageFile.length(),
      filename: fileName,
      contentType: MediaType.parse('image/$extension'),
    );

    request.files.add(multipartFile);

    // Kirim request dan proses response
    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 200) {
      final responseData = json.decode(response.body);
      return responseData['data']['file_name'];
    } else {
      throw Exception('Gagal upload gambar');
    }
  } catch (e) {
    throw Exception('Tidak dapat upload gambar: $e');
  }
}
```

**CodeIgniter - Proses Upload:**

```php
public function uploadGambar()
{
    try {
        $uploadedFile = $this->request->getFile('gambar');

        // Validasi file
        if (!$uploadedFile->isValid()) {
            return $this->fail('File tidak valid');
        }

        // Validasi tipe file
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        if (!in_array($uploadedFile->getClientMimeType(), $allowedTypes)) {
            return $this->fail('Format file tidak didukung');
        }

        // Buat nama file unik
        $newName = $uploadedFile->getRandomName();

        // Pindahkan file ke direktori uploads
        if ($uploadedFile->move(FCPATH . 'uploads', $newName)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Gambar berhasil diunggah',
                'data' => [
                    'file_name' => $newName
                ]
            ]);
        } else {
            return $this->fail('Gagal mengunggah gambar');
        }
    } catch (\Exception $e) {
        return $this->fail('Gagal mengunggah gambar: ' . $e->getMessage());
    }
}
```

---

## 8. KEAMANAN & PENANGANAN ERROR

**Keamanan:**

- **Validasi Input**:
  - Client-side: Validasi format, ukuran, tipe
  - Server-side: Validasi sebelum insert/update
- **Sanitasi Data**:

  ```php
  // Di CodeIgniter
  $data = [
      'judul' => $this->request->getPost('judul') ?? '',
      'isi' => $this->request->getPost('isi') ?? '',
  ];

  // Validasi
  if (empty($data['judul']) || empty($data['isi'])) {
      return $this->fail('Judul dan isi berita tidak boleh kosong');
  }
  ```

- **CORS Configuration**:

  - Batasi origin yang diizinkan akses
  - Tentukan metode HTTP yang diizinkan
  - Atur header yang diizinkan

- **HTTPS**:
  - Enkripsi data saat transit
  - Sertifikat SSL/TLS

**Penanganan Error:**

- **Try-Catch Block**:

  ```dart
  try {
    // Kode yang mungkin throw exception
  } catch (e) {
    // Tangani error
  } finally {
    // Cleanup
  }
  ```

- **Error State di Provider**:

  ```dart
  String? _error;
  String? get error => _error;

  void setError(String message) {
    _error = message;
    notifyListeners();
  }
  ```

- **Feedback Visual**:

  ```dart
  if (provider.isLoading) {
    return CircularProgressIndicator();
  } else if (provider.error != null) {
    return Text('Error: ${provider.error}');
  } else {
    return ListView.builder(...);
  }
  ```

- **Logging**:
  ```php
  // Di CodeIgniter
  log_message('error', 'Exception saat membuat berita: ' . $e->getMessage());
  ```

---

## 9. PENGUJIAN API

**Tools Pengujian:**

- **Postman**:

  - Test endpoint API secara manual
  - Buat koleksi request untuk pengujian rutin
  - Validasi response format dan status code

- **Dio Interceptors**:

  ```dart
  dio.interceptors.add(LogInterceptor(
    requestBody: true,
    responseBody: true,
  ));
  ```

- **Chrome DevTools**:

  - Network tab untuk melihat request/response
  - Analisis header, payload, dan timing

- **CodeIgniter Logger**:
  ```php
  log_message('info', 'Update Berita Request untuk ID ' . $id);
  log_message('error', 'Gagal memperbarui berita: ' . json_encode($errors));
  ```

**Contoh Output Respons API:**

```json
{
  "status": true,
  "message": "Berita berhasil diambil",
  "data": [
    {
      "id": 1,
      "judul": "Judul Berita",
      "isi": "Isi berita...",
      "gambar": "uploads/berita/image.jpg",
      "created_at": "2023-09-05 10:00:00",
      "updated_at": "2023-09-05 10:00:00"
    }
  ]
}
```

---

## 10. BEST PRACTICES

- **Service Layer Pattern**:

  - Pisahkan logika network dari UI dan state management
  - Mudah di-test dan diganti implementasinya

- **Implementasi Caching**:

  ```dart
  // Menyimpan data di memori
  List<Berita> _cachedBerita = [];
  DateTime _lastFetch = DateTime.now();

  Future<List<Berita>> getBerita({bool forceRefresh = false}) async {
    // Jika tidak force refresh dan cache belum expired
    if (!forceRefresh &&
        _cachedBerita.isNotEmpty &&
        DateTime.now().difference(_lastFetch).inMinutes < 5) {
      return _cachedBerita;
    }

    // Ambil data baru
    final freshData = await fetchFromApi();
    _cachedBerita = freshData;
    _lastFetch = DateTime.now();
    return freshData;
  }
  ```

- **Pagination dan Lazy Loading**:

  ```dart
  // Pagination di API
  Future<List<Berita>> getBeritaPaginated(int page, int limit) async {
    final response = await http.get(
      Uri.parse('$baseUrl/berita?page=$page&limit=$limit')
    );
    // Process response
  }
  ```

- **Error Handling yang Konsisten**:

  - Standarisasi format error di seluruh aplikasi
  - Berikan pesan yang jelas dan solusi ke pengguna

- **Timeout dan Retry Mechanism**:
  ```dart
  // Set timeout untuk request
  final response = await http.get(Uri.parse('$baseUrl/berita'))
    .timeout(
      Duration(seconds: 10),
      onTimeout: () {
        throw TimeoutException('Request timeout');
      },
    );
  ```

---

## 11. DEMO APLIKASI

**Demo Skenario:**

1. **Menampilkan Daftar Berita**:

   - Implementasi ListView dengan data dari API
   - Pull to refresh untuk update data
   - Loading state dan error handling

2. **Detail Berita**:

   - Navigasi ke halaman detail dengan parameter ID
   - Fetch detail data dari API berdasarkan ID
   - Tampilkan gambar dengan cached_network_image

3. **Membuat Berita Baru**:

   - Form input dengan validasi
   - Image picker untuk pemilihan gambar
   - Progress indicator saat upload

4. **Edit Berita**:

   - Pre-fill form dengan data existing
   - Update data melalui API
   - Konfirmasi perubahan

5. **Hapus Berita**:
   - Dialog konfirmasi sebelum hapus
   - Notifikasi sukses/error
   - Refresh list setelah hapus

---

## 12. IMPLEMENTASI PRAKTIS

**Langkah-langkah Implementasi:**

1. **Setup Database**:

   ```sql
   CREATE DATABASE db_news;
   USE db_news;
   CREATE TABLE posting (
     id int(11) NOT NULL AUTO_INCREMENT,
     judul varchar(255) NOT NULL,
     isi text NOT NULL,
     gambar varchar(255) DEFAULT 'no-image.jpg',
     created_at datetime DEFAULT current_timestamp(),
     updated_at datetime DEFAULT current_timestamp(),
     PRIMARY KEY (id)
   );
   ```

2. **Setup CodeIgniter API**:

   - Install CodeIgniter 4
   - Konfigurasi database di `.env`
   - Buat Model `BeritaModel`
   - Buat Controller `Api` dengan endpoint CRUD
   - Konfigurasi CORS untuk akses cross-platform

3. **Setup Flutter Project**:

   - Install dependencies di `pubspec.yaml`:
     ```yaml
     dependencies:
       http: ^1.1.0
       provider: ^6.0.5
       image_picker: ^1.0.4
       cached_network_image: ^3.3.0
     ```
   - Buat model `Berita`
   - Implementasi `ApiService`
   - Buat Provider untuk state management
   - Bangun UI screens dan widgets

4. **Integrasi dan Testing**:
   - Test setiap endpoint dengan Postman
   - Implementasi fitur di Flutter satu per satu
   - Debug dengan DevTools dan logging
   - Test pada berbagai perangkat dan platform

---

## 13. KESIMPULAN

- **Arsitektur Terdistribusi**:

  - Backend (CI4) dan Frontend (Flutter) terpisah
  - Komunikasi melalui RESTful API
  - Format data JSON untuk pertukaran informasi

- **Keunggulan MySQL**:

  - RDBMS yang stabil dan teroptimasi
  - Mendukung relasi antar tabel
  - Performa baik untuk operasi CRUD
  - Dukungan komunitas yang luas

- **Fleksibilitas CodeIgniter 4**:

  - Framework ringan dan cepat
  - API endpoints mudah dikonfigurasi
  - Dukungan bawaan untuk RESTful API
  - Integrasi database yang sederhana

- **Flutter untuk Cross-Platform**:

  - Single codebase untuk iOS, Android, web
  - Performa native-like
  - Hot reload untuk development cepat
  - Widget-based UI yang fleksibel

- **Pengembangan Lebih Lanjut**:
  - Autentikasi dan otorisasi
  - Notifikasi real-time
  - Peningkatan performa dan caching
  - Pengujian otomatis dan CI/CD

---

## 14. REFERENSI & SUMBER BELAJAR

- **Dokumentasi Resmi**:

  - [Dokumentasi CodeIgniter 4](https://codeigniter.com/user_guide/index.html)
  - [Dokumentasi Flutter](https://flutter.dev/docs)
  - [Dokumentasi HTTP Package Flutter](https://pub.dev/packages/http)
  - [Dokumentasi MySQL](https://dev.mysql.com/doc/)

- **Tutorial dan Artikel**:

  - [Membuat RESTful API dengan CodeIgniter 4](https://codeigniter.com/user_guide/outgoing/api_responses.html)
  - [Flutter HTTP Requests dan JSON parsing](https://flutter.dev/docs/cookbook/networking/fetch-data)
  - [Provider Pattern di Flutter](https://pub.dev/packages/provider)
  - [Upload File di Flutter](https://pub.dev/packages/image_picker)

- **Tools dan Libraries**:
  - [Postman](https://www.postman.com/) - API Testing
  - [Provider Package](https://pub.dev/packages/provider) - State Management
  - [Image Picker](https://pub.dev/packages/image_picker) - File Selection
  - [Cached Network Image](https://pub.dev/packages/cached_network_image) - Image Caching
