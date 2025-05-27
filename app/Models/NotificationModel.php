<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'message',
        'type',
        'is_read',
        'target_id',
        'device_token'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Membuat notifikasi baru
     * 
     * @param string $title Judul notifikasi
     * @param string $message Pesan notifikasi
     * @param string $type Tipe notifikasi (default: 'info')
     * @param int|null $targetId ID target (optional)
     * @param string|null $deviceToken Token perangkat (optional)
     * @return int|false ID notifikasi baru atau false jika gagal
     */
    public function createNotification($title, $message, $type = 'info', $targetId = null, $deviceToken = null)
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_id' => $targetId,
            'device_token' => $deviceToken,
            'is_read' => 0
        ];

        return $this->insert($data) ? $this->getInsertID() : false;
    }

    /**
     * Menambahkan notifikasi untuk berita baru
     * 
     * @param int $beritaId ID berita
     * @param string $judul Judul berita
     * @return int|false ID notifikasi baru atau false jika gagal
     */
    public function addNewBeritaNotification($beritaId, $judul)
    {
        return $this->createNotification(
            'Berita Baru Ditambahkan',
            "Berita baru telah ditambahkan: $judul",
            'berita_baru',
            $beritaId
        );
    }

    /**
     * Mendapatkan semua notifikasi yang belum dibaca
     * 
     * @param string|null $deviceToken Token perangkat (optional)
     * @return array Notifikasi yang belum dibaca
     */
    public function getUnreadNotifications($deviceToken = null)
    {
        $builder = $this->where('is_read', 0);

        if ($deviceToken !== null) {
            $builder->where('device_token', $deviceToken);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Menandai notifikasi sebagai telah dibaca
     * 
     * @param int $id ID notifikasi
     * @return bool True jika berhasil, false jika gagal
     */
    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => 1]);
    }

    /**
     * Menandai semua notifikasi sebagai telah dibaca
     * 
     * @param string|null $deviceToken Token perangkat (optional)
     * @return bool True jika berhasil, false jika gagal
     */
    public function markAllAsRead($deviceToken = null)
    {
        $builder = $this->where('is_read', 0);

        if ($deviceToken !== null) {
            $builder->where('device_token', $deviceToken);
        }

        return $builder->set('is_read', 1)->update();
    }
}
