<?php

namespace App\Controllers;

// use App\Controllers\BaseController;
// use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BeritaModel;
use App\Models\NotificationModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;


class Api extends ResourceController
{
    use ResponseTrait;
    protected $format = 'json';

    /**
     * Constructor untuk menambahkan CORS headers pada setiap response
     */
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    }

    public function getBerita()
    {
        $model = new BeritaModel();
        $data = $model->findAll();

        return $this->respond($data);
    }

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

    public function createBerita()
    {
        $model = new BeritaModel();
        $json = $this->request->getJSON();

        // Log data yang diterima untuk debugging
        log_message('info', 'Create Berita Request - Raw input: ' . file_get_contents('php://input'));
        log_message('info', 'Create Berita Request - JSON: ' . json_encode($json));
        log_message('info', 'Create Berita Request - POST: ' . json_encode($this->request->getPost()));

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

        // Validasi data sebelum insert
        if (empty($data['judul']) || empty($data['isi'])) {
            return $this->fail('Judul dan isi berita tidak boleh kosong');
        }

        try {
            if ($model->insert($data)) {
                $id = $model->getInsertID();
                log_message('info', 'Berhasil membuat berita dengan ID: ' . $id);

                // Tambahkan notifikasi untuk berita baru
                $notifModel = new NotificationModel();
                $notifModel->addNewBeritaNotification($id, $data['judul']);

                return $this->respondCreated([
                    'status' => 'success',
                    'message' => 'Berita berhasil dibuat',
                    'id' => $id
                ]);
            } else {
                $errors = $model->errors();
                log_message('error', 'Gagal menyimpan berita: ' . json_encode($errors));
                return $this->fail($errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception saat membuat berita: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan berita: ' . $e->getMessage());
        }
    }

    public function updateBerita($id = null)
    {
        $model = new BeritaModel();

        // Log data yang diterima untuk debugging
        log_message('info', 'Update Berita Request untuk ID ' . $id . ' - Raw input: ' . file_get_contents('php://input'));

        if (!$model->find($id)) {
            log_message('error', 'Berita dengan ID ' . $id . ' tidak ditemukan');
            return $this->failNotFound('Berita dengan ID ' . $id . ' tidak ditemukan');
        }

        $json = $this->request->getJSON();
        log_message('info', 'Update Berita Request - JSON: ' . json_encode($json));
        log_message('info', 'Update Berita Request - POST: ' . json_encode($this->request->getPost()));

        if ($json) {
            $data = [
                'judul' => $json->judul ?? '',
                'isi' => $json->isi ?? '',
            ];

            // Hanya update gambar jika ada
            if (isset($json->gambar)) {
                $data['gambar'] = $json->gambar;
            }
        } else {
            $data = [
                'judul' => $this->request->getPost('judul') ?? '',
                'isi' => $this->request->getPost('isi') ?? '',
            ];

            // Hanya update gambar jika ada
            if ($this->request->getPost('gambar') !== null) {
                $data['gambar'] = $this->request->getPost('gambar');
            }
        }

        // Validasi data sebelum update
        if (empty($data['judul']) || empty($data['isi'])) {
            return $this->fail('Judul dan isi berita tidak boleh kosong');
        }

        try {
            if ($model->update($id, $data)) {
                log_message('info', 'Berhasil memperbarui berita dengan ID: ' . $id);

                // Tambahkan notifikasi untuk update berita
                $notifModel = new NotificationModel();
                $notifModel->createNotification(
                    'Berita Diperbarui',
                    "Berita telah diperbarui: {$data['judul']}",
                    'update',
                    $id
                );

                return $this->respondUpdated([
                    'status' => 'success',
                    'message' => 'Berita dengan ID ' . $id . ' berhasil diperbarui'
                ]);
            } else {
                $errors = $model->errors();
                log_message('error', 'Gagal memperbarui berita: ' . json_encode($errors));
                return $this->fail($errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception saat memperbarui berita: ' . $e->getMessage());
            return $this->fail('Gagal memperbarui berita: ' . $e->getMessage());
        }
    }

    public function deleteBerita($id = null)
    {
        $model = new BeritaModel();

        if (!$model->find($id)) {
            return $this->failNotFound('Berita dengan ID ' . $id . ' tidak ditemukan');
        }

        if ($model->delete($id)) {
            return $this->respondDeleted(['message' => 'Berita dengan ID ' . $id . ' berhasil dihapus']);
        } else {
            return $this->fail('Gagal menghapus berita');
        }
    }

    public function uploadGambar()
    {
        try {
            $uploadedFile = $this->request->getFile('gambar');

            // Debug info
            log_message('info', 'Upload request received: ' . json_encode($_FILES));
            log_message('info', 'Current working directory: ' . getcwd());
            log_message('info', 'FCPATH: ' . FCPATH);

            // Cek apakah file tersedia dan valid
            if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                // Batasi tipe file
                $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
                $mimeType = $uploadedFile->getClientMimeType();

                log_message('info', 'File valid, mimetype: ' . $mimeType);

                if (!in_array($mimeType, $allowedTypes)) {
                    log_message('error', 'Format file tidak didukung: ' . $mimeType);
                    return $this->fail('Format file tidak didukung. Gunakan PNG, JPEG, atau GIF.');
                }

                // Buat nama file unik
                $newName = $uploadedFile->getRandomName();
                log_message('info', 'New filename: ' . $newName);

                // Pastikan direktori uploads ada
                $uploadPath = FCPATH . 'uploads';
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0777, true)) {
                        log_message('error', 'Gagal membuat direktori uploads: ' . $uploadPath);
                        return $this->fail('Gagal membuat direktori uploads');
                    }
                    log_message('info', 'Created uploads directory: ' . $uploadPath);
                    // Set permissions untuk direktori
                    chmod($uploadPath, 0777);
                }

                // Cek permissions
                log_message('info', 'Upload directory permissions: ' . substr(sprintf('%o', fileperms($uploadPath)), -4));

                // Pindahkan file ke folder uploads dengan menangani error dengan benar
                if ($uploadedFile->move($uploadPath, $newName, true)) {
                    log_message('info', 'File uploaded successfully to: ' . $uploadPath . '/' . $newName);

                    // Set permissions untuk file yang diupload
                    chmod($uploadPath . '/' . $newName, 0644);

                    return $this->respond([
                        'status' => 'success',
                        'message' => 'Gambar berhasil diunggah',
                        'data' => [
                            'file_name' => $newName
                        ]
                    ]);
                } else {
                    $error = $uploadedFile->getError();
                    log_message('error', 'Failed to move uploaded file: ' . $error);
                    return $this->fail('Gagal mengunggah gambar: ' . $error);
                }
            } else {
                $errorMsg = 'Tidak ada gambar yang diunggah atau file tidak valid';
                if ($uploadedFile) {
                    $error = $uploadedFile->getError();
                    $errorMsg .= '. Error: ' . $error;
                    log_message('error', 'Upload file error: ' . $error);
                }
                log_message('error', $errorMsg);
                return $this->fail($errorMsg);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception during upload: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->fail('Error saat mengunggah: ' . $e->getMessage());
        }
    }

    // Endpoint untuk mendapatkan semua notifikasi
    public function getNotifications()
    {
        $model = new NotificationModel();
        $deviceToken = $this->request->getGet('device_token');

        if ($deviceToken) {
            $data = $model->where('device_token', $deviceToken)
                ->orWhere('device_token', null)
                ->orderBy('created_at', 'DESC')
                ->findAll();
        } else {
            $data = $model->orderBy('created_at', 'DESC')->findAll();
        }

        return $this->respond($data);
    }

    // Endpoint untuk mendapatkan notifikasi yang belum dibaca
    public function getUnreadNotifications()
    {
        $model = new NotificationModel();
        $deviceToken = $this->request->getGet('device_token');

        $data = $model->getUnreadNotifications($deviceToken);
        return $this->respond($data);
    }

    // Endpoint untuk menandai notifikasi sebagai telah dibaca
    public function markNotificationAsRead($id = null)
    {
        $model = new NotificationModel();

        if (!$model->find($id)) {
            return $this->failNotFound('Notifikasi dengan ID ' . $id . ' tidak ditemukan');
        }

        if ($model->markAsRead($id)) {
            return $this->respondUpdated([
                'status' => 'success',
                'message' => 'Notifikasi dengan ID ' . $id . ' ditandai telah dibaca'
            ]);
        } else {
            return $this->fail('Gagal menandai notifikasi sebagai dibaca');
        }
    }

    // Endpoint untuk menandai semua notifikasi sebagai telah dibaca
    public function markAllNotificationsAsRead()
    {
        $model = new NotificationModel();
        $deviceToken = $this->request->getGet('device_token');

        if ($model->markAllAsRead($deviceToken)) {
            return $this->respondUpdated([
                'status' => 'success',
                'message' => 'Semua notifikasi ditandai telah dibaca'
            ]);
        } else {
            return $this->fail('Gagal menandai semua notifikasi sebagai dibaca');
        }
    }

    // Endpoint untuk registrasi token perangkat
    public function registerDeviceToken()
    {
        $json = $this->request->getJSON();
        $deviceToken = $json->device_token ?? null;

        if (!$deviceToken) {
            return $this->fail('Token perangkat diperlukan');
        }

        // Buat notifikasi selamat datang untuk perangkat baru
        $notifModel = new NotificationModel();
        $notifModel->createNotification(
            'Selamat Datang',
            'Selamat datang di aplikasi Berita. Anda akan menerima notifikasi untuk berita baru dan update.',
            'info',
            null,
            $deviceToken
        );

        return $this->respond([
            'status' => 'success',
            'message' => 'Token perangkat berhasil didaftarkan',
            'device_token' => $deviceToken
        ]);
    }
}
