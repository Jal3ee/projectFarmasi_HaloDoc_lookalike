<?php

namespace App\Models;

use CodeIgniter\Model;

class PasienModel extends Model
{
    protected $table = 'pasien'; // Nama tabel
    protected $primaryKey = 'id'; // Primary key tabel

    protected $allowedFields = ['id_user', 'pekerjaan', 'riwayat']; // Kolom yang dapat diisi

    // Menyimpan data pasien
    public function insertData($data)
    {
        return $this->insert($data);
    }

    public function getDataID($id_user)
    {
        return $this->where('id_user', $id_user)->first();
    }

    public function getAllPatients()
    {
        return $this->select('pasien.*, user.id, user.nama, user.gender, user.usia, user.no_hp, user.email, user.username')
            ->join('user', 'user.id = pasien.id_user')
            ->where('user.role', 'pasien')
            ->findAll();
    }
}
