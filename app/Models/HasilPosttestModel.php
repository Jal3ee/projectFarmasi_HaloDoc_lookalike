<?php

namespace App\Models;

use CodeIgniter\Model;

class HasilPosttestModel extends Model
{
    protected $table = 'hasil_posttest';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_pasien', 'jumlah_benar', 'jumlah_soal', 'nilai', 'waktu_posttest'];
    protected $useTimestamps = false; // Kalau pakai created_at bisa diaktifkan
}
