<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OptPostTestModel;
use App\Models\PasienModel;
use App\Models\PostTestModel;
use App\Models\StatCourseModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AnsPosttestModel;
class Posttest extends BaseController
{
    protected $posttestModel;
    protected $optposttestModel;

    public function __construct()
    {
        $this->posttestModel = new PostTestModel();
        $this->optposttestModel = new OptPostTestModel();
        helper('cookie');
        // Cek autentikasi di konstruktor
        $this->cekAuth();
    }
    private function cekAuth()
    {
        // Cek session
        if (session()->has('logged_in') || session()->get('logged_in') === true) {
            return true;
        }

        // Cek cookie
        $remember_me_cookie = get_cookie('remember_me');

        if ($remember_me_cookie) {
            list($username, $id_user) = explode('|', $remember_me_cookie);

            $userModel = new UserModel();

            if (!$userModel->cekUser($username)) {
                $session_data = [
                    'id_user' => $id_user,
                    'username' => $username,
                    'logged_in' => true,
                ];
                session()->set($session_data);
                return;
            }
        }
        return redirect()->to('/login');
    }

    public function cek()
    {
        $pasienModel = new PasienModel();
        $id_user = session()->get('id_user'); // Ambil ID pasien dari sesi
        $id_pasien = $pasienModel->getDataID($id_user)["id"];

        // Update status course jadi 100
        $statModel = new StatCourseModel();
        $statModel->where('id_pasien', $id_pasien)->set(['course' => 100])->update();

        return redirect()->to('/posttest');
    }
    public function index()
    {
        $id_user = session()->get('id_user');
        $statModel = new StatCourseModel();
        $pasienModel = new PasienModel();
        $id_pasien = $pasienModel->getDataID($id_user)["id"];
        $statPosttest = $statModel->getData($id_pasien)['course'];

        if ($statPosttest < 100) {
            var_dump($statPosttest);
            var_dump($id_pasien);
            return redirect()->to('/pasien')->with('success', 'Login Berhasil.');
        }
        // Ambil data pertanyaan dan pilihan ganda
        $pertanyaan = $this->posttestModel->findAll();
        $pilihan = [];

        foreach ($pertanyaan as $p) {
            $pilihan[$p['id']] = $this->optposttestModel->where('id_posttest', $p['id'])->findAll();
        }

        // Kirim data ke view
        return view('pasien/post-test-screen', [
            'pertanyaan' => $pertanyaan,
            'pilihan' => $pilihan
        ]);
    }
    public function submit()
{
    $pasienModel = new PasienModel();
    $hasilModel = new \App\Models\HasilPosttestModel(); // Panggil model hasil posttest
    $jawabanModel = new \App\Models\AnsPosttestModel(); // Model untuk menyimpan jawaban posttest

    $selectedOptions = $this->request->getPost('pilihan');
    $id_user = session()->get('id_user'); // Ambil ID pasien dari sesi
    $id_pasien = $pasienModel->getDataID($id_user)["id"];

    // Ambil data pertanyaan dan pilihan ganda
    $pertanyaan = $this->posttestModel->findAll();
    $pilihan = [];

    $totalBenar = 0;
    $jumlahSoal = count($pertanyaan);
    $jawabanModel->where('id_pasien', $id_pasien)->delete(); // clear sebelum insert

    foreach ($pertanyaan as $p) {
        $pilihan[$p['id']] = $this->optposttestModel->where('id_posttest', $p['id'])->findAll();

        if (isset($selectedOptions[$p['id']])) {
            $jawabanUser = $selectedOptions[$p['id']];
            $benar = $jawabanUser == $p['id_jawaban'] ? 1 : 0; // Tentukan apakah jawaban benar atau salah

            // Simpan jawaban pasien ke dalam tabel jawaban_posttest
            $jawabanModel->save([
                'id_posttest' => $p['id'],
                'id_pilihan' => $jawabanUser,
                'id_pasien' => $id_pasien,
                'benar' => $benar
            ]);

            if ($benar) {
                $totalBenar++; // Jika jawaban benar, tambah counter
            }
        }
    }

    $nilai = $jumlahSoal > 0 ? round(($totalBenar / $jumlahSoal) * 100, 2) : 0;
    $hasilModel->where('id_pasien', $id_pasien)->delete();
    // Simpan hasil ke DB
    $hasilModel->save([
        'id_pasien' => $id_pasien,
        'jumlah_benar' => $totalBenar,
        'jumlah_soal' => $jumlahSoal,
        'nilai' => $nilai
    ]);

    $data = [
        'selectedOptions' => $selectedOptions,
        'pertanyaan' => $pertanyaan,
        'pilihan' => $pilihan,
        'totalBenar' => $totalBenar,
        'jumlahSoal' => $jumlahSoal,
        'nilai' => $nilai
    ];

    return view('pasien/posttest-review', $data);
}



    public function done()
    {
        $pasienModel = new PasienModel();
        $id_user = session()->get('id_user'); // Ambil ID pasien dari sesi
        $id_pasien = $pasienModel->getDataID($id_user)["id"];

        // Update status pretest menjadi 'sudah'
        $statModel = new StatCourseModel();
        $statModel->where('id_pasien', $id_pasien)->set(['posttest' => 'sudah'])->update();

        // Redirect atau tampilkan pesan sesuai kebutuhan
        return redirect()->to('/pasien'); // Contoh redirect ke halaman hasil
    }

    public function add_question()
    {
        $rules = [
            'text' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $text = $this->request->getPost('text');
        $data = [
            'pertanyaan' => $text,
            'id_jawaban' => 0
        ];
        if ($this->posttestModel->insert($data)) {
            return redirect()->back();
        } else {
            return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
        }

    }

    public function edit_question()
    {
        $rules = [
            'options' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pertanyaan = esc($this->request->getPost('options'));
        $idPertanyaan = esc($this->request->getPost('id_pertanyaan'));
        $data = [
            'pertanyaan' => $pertanyaan
        ];
        if ($this->posttestModel->update($idPertanyaan, $data)) {
            return redirect()->back();
        } else {
            return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
        }
    }

    public function delete_question($id)
    {
        $this->optposttestModel->where('id_posttest', $id)->delete();
        $this->posttestModel->delete($id);

        return redirect()->back();
    }

    public function add_option()
    {
        $rules = [
            'options' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $teksPilihan = esc($this->request->getPost('options'));
        $idPosttest = esc($this->request->getPost('id_pertanyaan'));
        $data = [
            'id_posttest' => $idPosttest,
            'teks_pilihan' => $teksPilihan
        ];
        if ($this->optposttestModel->insert($data)) {
            $isChecked = esc($this->request->getPost('is_correct'));
            if ($isChecked) {
                $newID = $this->optposttestModel->insertID();
                $addAns = [
                    'id_jawaban' => $newID,
                ];
                $ans = $this->posttestModel->update($idPosttest, $addAns);
                if (!$ans) {
                    return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
                }
            }
            return redirect()->back();
        } else {
            return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
        }
    }

    public function edit_option()
    {
        $rules = [
            'options' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $teksPilihan = esc($this->request->getPost('options'));
        $idPilihan = esc($this->request->getPost('id_pilihan'));
        $idAns = esc($this->request->getPost('id_jawaban'));
        $idPertanyaan = esc($this->request->getPost('id_pertanyaan'));
        $data = [
            'teks_pilihan' => $teksPilihan
        ];
        if ($this->optposttestModel->update($idPilihan, $data)) {
            $isChecked = esc($this->request->getPost('is_correct'));
            if ($isChecked) {
                $addAns = [
                    'id_jawaban' => $idPilihan,
                ];
                $ans = $this->posttestModel->update($idPertanyaan, $addAns);
                if (!$ans) {
                    return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
                }
            } else {
                if ($idPilihan === $idAns) {
                    $addAns = [
                        'id_jawaban' => 0,
                    ];
                    $ans = $this->posttestModel->update($idPertanyaan, $addAns);
                    if (!$ans) {
                        return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
                    }
                }
            }
            return redirect()->back();
        } else {
            return redirect()->back()->withInput()->with('errors', 'Gagal menambahkan pertanyaan.');
        }
    }

    public function delete_option($id)
    {
        $this->optposttestModel->delete($id);

        return redirect()->back();
    }
    public function nilai()
{
    $userModel = new UserModel();
    $data['users'] = $userModel->getAllPosttestScoresPaginated(10);
    $data['pager'] = $userModel->pager;

    return view('posttest/nilai', $data);
}
}
