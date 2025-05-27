# KONSUMSI API & NETWORKING MYSQL

## Pendahuluan

Dokumen ini menjelaskan implementasi konsumsi API dan networking dengan MySQL dalam aplikasi Berita yang dibangun menggunakan CodeIgniter 4 (backend) dan Flutter (frontend). Aplikasi ini mendemonstrasikan komunikasi data antara aplikasi mobile dengan database MySQL melalui REST API.

## Arsitektur Sistem

Aplikasi ini menggunakan arsitektur client-server dengan komponen utama:

1. **Database MySQL**: Menyimpan data berita
2. **Backend CodeIgniter 4**: Menyediakan REST API dan berkomunikasi dengan database
3. **Frontend Flutter**: Mengkonsumsi API dan menampilkan data ke pengguna

```
┌─────────────┐    REST API    ┌─────────────┐    SQL    ┌─────────────┐
│   Flutter   │ ◄───────────► │ CodeIgniter │ ◄───────► │    MySQL    │
│  (Mobile)   │  HTTP Request  │  (Backend)  │  Queries  │ (Database)  │
└─────────────┘               └─────────────┘           └─────────────┘
```

## Bagian 1: Database MySQL

### Struktur Tabel

Aplikasi menggunakan tabel `posting` dengan struktur:

```sql
CREATE TABLE `posting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Penjelasan Kolom:

- **id**: Identifikasi unik (primary key) dengan auto increment
- **judul**: Judul berita (varchar, maksimal 255 karakter)
- **isi**: Konten berita (text, tidak ada batasan panjang)
- **gambar**: Nama file gambar yang disimpan (opsional)
- **created_at**: Waktu pembuatan record (otomatis saat insert)
- **updated_at**: Waktu pembaruan record (otomatis saat update)

## Bagian 2: Backend CodeIgniter 4

### Model (BeritaModel.php)

Model bertanggung jawab untuk interaksi langsung dengan database:

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class BeritaModel extends Model
{
    protected $table            = 'posting';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'judul',
        'isi',
        'gambar'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
```

### Controller API (Api.php)

Controller menangani HTTP requests dan mengembalikan responses dalam format JSON:

```php
<?php
namespace App\Controllers;

use App\Models\BeritaModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Api extends ResourceController
{
    use ResponseTrait;
    protected $format = 'json';

    public function __construct()
    {
        // Menangani CORS untuk akses dari aplikasi Flutter
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    }

    // Mengambil semua berita
    public function getBerita()
    {
        $model = new BeritaModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    // Mengambil berita berdasarkan ID
    public function getBeritaById($id = null)
    {
        $model = new BeritaModel();
        $data = $model->find($id);

        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('Berita dengan ID ' . $id . ' tidak ditemukan');
        }
    }

    // Membuat berita baru
    public function createBerita()
    {
        $model = new BeritaModel();
        $json = $this->request->getJSON();

        // Menyiapkan data dari request JSON
        if ($json) {
            $data = [
                'judul' => $json->judul ?? '',
                'isi' => $json->isi ?? '',
                'gambar' => $json->gambar ?? null
            ];
        } else {
            $data = [
                'judul' => $this->request->getPost('judul') ?? '',
                'isi' => $this->request->getPost('isi') ?? '',
                'gambar' => $this->request->getPost('gambar') ?? null
            ];
        }

        // Validasi dan menyimpan data
        if (empty($data['judul']) || empty($data['isi'])) {
            return $this->fail('Judul dan isi berita tidak boleh kosong');
        }

        try {
            if ($model->insert($data)) {
                $id = $model->getInsertID();
                return $this->respondCreated([
                    'status' => 'success',
                    'message' => 'Berita berhasil dibuat',
                    'id' => $id
                ]);
            } else {
                return $this->fail($model->errors());
            }
        } catch (\Exception $e) {
            return $this->fail('Gagal menyimpan berita: ' . $e->getMessage());
        }
    }

    // Memperbarui berita
    public function updateBerita($id = null)
    {
        // Implementasi update berita
        // Kode lengkap ada di file asli
    }

    // Menghapus berita
    public function deleteBerita($id = null)
    {
        // Implementasi delete berita
        // Kode lengkap ada di file asli
    }

    // Upload gambar
    public function uploadGambar()
    {
        // Implementasi upload gambar
        // Kode lengkap ada di file asli
    }
}
```

### Konfigurasi Routes (Config/Routes.php)

Mengatur endpoint API yang tersedia:

```php
// API Routes
$routes->group('api', function($routes) {
    $routes->get('berita', 'Api::getBerita');
    $routes->get('berita/(:num)', 'Api::getBeritaById/$1');
    $routes->post('berita', 'Api::createBerita');
    $routes->put('berita/(:num)', 'Api::updateBerita/$1');
    $routes->delete('berita/(:num)', 'Api::deleteBerita/$1');
    $routes->post('upload', 'Api::uploadGambar');
});
```

## Bagian 3: Frontend Flutter

### Model Berita (berita_model.dart)

Model merepresentasikan data berita dan menyediakan metode konversi JSON:

```dart
class Berita {
  int? id;
  String judul;
  String isi;
  String? gambar;
  String? createdAt;
  String? updatedAt;

  Berita({
    this.id,
    required this.judul,
    required this.isi,
    this.gambar,
    this.createdAt,
    this.updatedAt,
  });

  // Konversi dari JSON ke objek Berita
  factory Berita.fromJson(Map<String, dynamic> json) {
    return Berita(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      judul: json['judul'] ?? '',
      isi: json['isi'] ?? '',
      gambar: json['gambar'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  // Konversi dari objek Berita ke JSON
  Map<String, dynamic> toJson() {
    return {
      'judul': judul,
      'isi': isi,
      'gambar': gambar,
    };
  }
}
```

### Service API (api_service.dart)

Service bertanggung jawab untuk komunikasi dengan REST API:

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import '../models/berita_model.dart';

class ApiService {
  // URL API yang sesuai dengan jenis perangkat
  final String baseUrl = 'http://localhost:8081/api';
  final String uploadBaseUrl = 'http://localhost:8081/uploads';

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

  // Mengambil berita berdasarkan ID
  Future<Berita> getBeritaById(int id) async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/berita/$id'));

      if (response.statusCode == 200) {
        return Berita.fromJson(json.decode(response.body));
      } else {
        throw Exception('Gagal memuat berita dengan ID $id');
      }
    } catch (e) {
      throw Exception('Tidak dapat memuat berita: $e');
    }
  }

  // Membuat berita baru
  Future<Map<String, dynamic>> createBerita(Berita berita) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/berita'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode(berita.toJson()),
      );

      if (response.statusCode == 201) {
        return json.decode(response.body);
      } else {
        final errorMsg = response.body.isNotEmpty
            ? json.decode(response.body)['messages'] ?? 'Error code ${response.statusCode}'
            : 'Error code ${response.statusCode}';
        throw Exception('Gagal membuat berita: $errorMsg');
      }
    } catch (e) {
      throw Exception('Tidak dapat membuat berita: $e');
    }
  }

  // Memperbarui berita
  Future<Map<String, dynamic>> updateBerita(int id, Berita berita) async {
    // Implementasi update berita
    // Kode lengkap ada di file asli
  }

  // Menghapus berita
  Future<Map<String, dynamic>> deleteBerita(int id) async {
    // Implementasi delete berita
    // Kode lengkap ada di file asli
  }

  // Method untuk upload file gambar
  Future<String> uploadGambar(File imageFile) async {
    // Implementasi upload gambar
    // Kode lengkap ada di file asli
  }
}
```

### Implementasi UI: Berita List Screen

```dart
class BeritaListScreen extends StatefulWidget {
  @override
  _BeritaListScreenState createState() => _BeritaListScreenState();
}

class _BeritaListScreenState extends State<BeritaListScreen> {
  final ApiService _apiService = ApiService();
  late Future<List<Berita>> _futureBerita;

  @override
  void initState() {
    super.initState();
    _refreshBerita();
  }

  void _refreshBerita() {
    setState(() {
      _futureBerita = _apiService.getBerita();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Daftar Berita'),
      ),
      body: FutureBuilder<List<Berita>>(
        future: _futureBerita,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Error: ${snapshot.error}'));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return Center(child: Text('Tidak ada berita'));
          } else {
            return ListView.builder(
              itemCount: snapshot.data!.length,
              itemBuilder: (context, index) {
                final berita = snapshot.data![index];
                return BeritaCard(
                  berita: berita,
                  onRefresh: _refreshBerita,
                );
              },
            );
          }
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => AddEditBeritaScreen()),
          );
          if (result == true) {
            _refreshBerita();
          }
        },
        child: Icon(Icons.add),
      ),
    );
  }
}
```

## Alur Komunikasi Data

1. **Mendapatkan Data Berita**:

   ```
   App Flutter -> HTTP GET request -> API CI4 -> Query SELECT -> Database MySQL
   Database MySQL -> Hasil query -> API CI4 -> JSON Response -> App Flutter
   ```

2. **Membuat Berita Baru**:

   ```
   App Flutter -> HTTP POST request (JSON) -> API CI4 -> Query INSERT -> Database MySQL
   Database MySQL -> ID baru -> API CI4 -> JSON Response -> App Flutter
   ```

3. **Upload Gambar**:
   ```
   App Flutter -> HTTP POST Multipart -> API CI4 -> Simpan file ke folder -> Kembalikan nama file
   App Flutter -> Terima nama file -> Gunakan untuk data berita
   ```

## Implementasi Praktis Konsumsi API

### 1. Konfigurasi Dasar

Pastikan dependensi dipasang di Flutter (`pubspec.yaml`):

```yaml
dependencies:
  http: ^1.1.0
  http_parser: ^4.0.2
```

### 2. Penanganan Endpoints

Pengaturan URL API berdasarkan jenis perangkat:

```dart
// 1. Desktop/Browser Web: Gunakan localhost
final String baseUrl = 'http://localhost:8081/api';

// 2. Emulator Android: Gunakan 10.0.2.2 (pengganti localhost)
// final String baseUrl = 'http://10.0.2.2:8081/api';

// 3. Perangkat Fisik: Gunakan IP Address komputer server
// final String baseUrl = 'http://192.168.1.X:8081/api';
```

### 3. Penanganan Respons dan Error

Contoh penanganan respons API:

```dart
try {
  final response = await http.get(Uri.parse('$baseUrl/berita'));

  if (response.statusCode == 200) {
    // Sukses: Proses data
    final List<dynamic> jsonResponse = json.decode(response.body);
    return jsonResponse.map((data) => Berita.fromJson(data)).toList();
  } else {
    // Error HTTP: Lempar exception dengan kode status
    throw Exception('Gagal memuat data: HTTP ${response.statusCode}');
  }
} catch (e) {
  // Error lainnya (koneksi, parsing, dll)
  throw Exception('Terjadi error: $e');
}
```

## Keamanan dan Praktik Terbaik

1. **Validasi Input**:

   - Selalu validasi data sebelum mengirim ke API
   - Cek nilai null dan format data

2. **Penanganan Error**:

   - Implementasi error handling yang menyeluruh
   - Tampilkan pesan error yang informatif kepada pengguna

3. **CORS (Cross-Origin Resource Sharing)**:

   - Backend CI4 dikonfigurasi dengan header CORS untuk menerima request dari berbagai sumber

4. **Timeout dan Retry**:
   - Implementasikan timeout untuk request API
   - Terapkan mekanisme retry untuk kasus kegagalan jaringan

## Kesimpulan

Aplikasi Berita ini menunjukkan implementasi lengkap konsumsi REST API dari aplikasi Flutter ke backend CodeIgniter 4 yang terhubung dengan database MySQL. Arsitektur ini memungkinkan pemisahan jelas antara frontend dan backend, serta memungkinkan pengembangan paralel dan skalabilitas yang baik.

Untuk mengembangkan aplikasi lebih lanjut, dapat ditambahkan fitur seperti autentikasi pengguna, pencarian, caching, dan optimasi performa.
