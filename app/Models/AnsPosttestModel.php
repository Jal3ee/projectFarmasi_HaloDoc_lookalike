<?php

namespace App\Models;

use CodeIgniter\Model;

class AnsPosttestModel extends Model
{
    protected $table = 'jawaban_posttest';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_posttest', 'id_pilihan', 'id_pasien', 'benar'];  // Tambahkan kolom 'benar'
}
