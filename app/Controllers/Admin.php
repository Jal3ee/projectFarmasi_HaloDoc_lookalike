<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\ChatModel;
use App\Models\DokterModel;
use App\Models\OptPostTestModel;
use App\Models\OptPretestModel;
use App\Models\PasienModel;
use App\Models\PostTestModel;
use App\Models\PretestModel;
use App\Models\StatCourseModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\HasilPosttestModel;
use App\Models\AnsPosttestModel;

class Admin extends BaseController
{

    public function index()
    {
        return view('admin/login_admin');
    }

    public function login()
    {
        $adminModel = new AdminModel();

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = esc($this->request->getPost('username'));
        $password = esc($this->request->getPost('password'));

        if ($adminModel->cekUser($username)) {
            return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan.');
        }

        $hassPass = $adminModel->getData($username)["password"];

        if (!password_verify($password, $hassPass)) {
            return redirect()->back()->withInput()->with('error', 'Username atau kata sandi tidak valid');
        }

        $session_data = [
            'admin_in' => true,
        ];
        session()->set($session_data);
        return redirect()->to('/admin/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin');
    }

    public function dashboard()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $userModel = new UserModel();
            $statCourseModel = new StatCourseModel();
            $nCourseDone = $statCourseModel->countCourseComplete();
            $jumlahDokter = $userModel->countDoctors();
            $jumlahPasien = $userModel->countPatients();
            $data = [
                'n_course_done' => $nCourseDone,
                'n_dokter' => $jumlahDokter,
                'n_pasien' => $jumlahPasien
            ];
            return view('admin/home-screen-admin', $data);
        } else {
            return redirect()->to('/admin');
        }
    }

    public function users()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            return view('admin/users-screen');
        } else {
            return redirect()->to('/admin');
        }
    }

    public function dokter_screen()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $dokterModel = new DokterModel();

            $dokters = $dokterModel->getAllDoctors();

            $filteredDoctors = [];
            foreach ($dokters as $doctor) {
                $filteredDoctors[] = [
                    'id' => $doctor['id'],
                    'id_user' => $doctor['id_user'],
                    'name' => $doctor['nama'],
                    'username' => $doctor['username'],
                    'gender' => $doctor['gender'],
                    'tanggal_lahir' => $doctor['tgl_lahir'],
                    'tahunPengalaman' => $doctor['exp_years'],
                    'nomorHandphone' => $doctor['no_hp'],
                    'specialty' => $doctor['spesialis'],
                    'email' => $doctor['email'],
                    'hari_mulai' => $doctor['hari_mulai'],
                    'hari_selesai' => $doctor['hari_selesai'],
                    'jam_mulai' => substr($doctor['jam_mulai'], 0, 5),
                    'jam_selesai' => substr($doctor['jam_selesai'], 0, 5),
                ];
            }
            return view('admin/dokter-screen', ['doctors' => $filteredDoctors]);
        } else {
            return redirect()->to('/admin');
        }
    }

    public function save_dokter()
    {
        $userModel = new UserModel();
        $dokterModel = new DokterModel();
        $data = esc($this->request->getPost());

        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'tanggal-lahir' => 'required|valid_date[Y-m-d]',
            'spesialis' => 'required',
            'exp-years' => 'required|greater_than[-1]',
            'hari-mulai' => 'required',
            'hari-selesai' => 'required',
            'jam-mulai' => 'required',
            'jam-selesai' => 'required',
            'no-hp' => 'required',
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[user.email]',
                'errors' => [
                    'is_unique' => 'Email ini sudah terdaftar.'
                ]
            ],
            'username' => [
                'label' => 'Username',
                'rules' => 'required|is_unique[user.username]',
                'errors' => [
                    'is_unique' => 'Nama pengguna ini sudah digunakan.'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|regex_match[/(?=.*[A-Z])(?=.*\d).{8,}/]',
                'errors' => [
                    'regex_match' => 'Kata sandi harus minimal 8 karakter dan mengandung minimal satu huruf besar dan satu angka.'
                ]
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Simpan ke tabel user
        $userData = [
            'role' => 'dokter',
            'foto' => 'assets/images/learning/default.jpg',
            'nama' => $data['name'],
            'gender' => $data['gender'],
            'tgl_lahir' => $data['tanggal-lahir'],
            'no_hp' => $data['no-hp'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_ARGON2ID),
        ];

        $userModel->insert($userData);
        $userId = $userModel->getID($data['username']);

        // Simpan ke tabel dokter
        $dokterData = [
            'id_user' => $userId,
            'spesialis' => $data['spesialis'],
            'exp_years' => $data['exp-years'],
            'hari_mulai' => $data['hari-mulai'],
            'hari_selesai' => $data['hari-selesai'],
            'jam_mulai' => $data['jam-mulai'],
            'jam_selesai' => $data['jam-selesai'],
        ];

        $dokterModel->insert($dokterData);

        return $this->response->setJSON(['success' => true]);
    }

    public function update_dokter($id)
    {
        $userModel = new UserModel();
        $dokterModel = new DokterModel();
        $data = esc($this->request->getPost());

        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'tanggal-lahir' => 'required|valid_date[Y-m-d]',
            'spesialis' => 'required',
            'exp-years' => 'required|greater_than[-1]',
            'hari-mulai' => 'required',
            'hari-selesai' => 'required',
            'jam-mulai' => 'required',
            'jam-selesai' => 'required',
            'no-hp' => 'required',
            'email' => [
                'label' => 'Email',
                'rules' => "required|valid_email|is_unique[user.email,id,{$id}]",
                'errors' => [
                    'is_unique' => 'Email ini sudah terdaftar.'
                ]
            ],
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[user.username,id,{$id}]",
                'errors' => [
                    'is_unique' => 'Nama pengguna ini sudah digunakan.'
                ]
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Update tabel user
        $userData = [
            'nama' => $data['name'],
            'gender' => $data['gender'],
            'tgl_lahir' => $data['tanggal-lahir'],
            'no_hp' => $data['no-hp'],
            'email' => $data['email'],
            'username' => $data['username'],
        ];

        $userModel->update($id, $userData);

        // Update tabel dokter
        $dokterData = [
            'spesialis' => $data['spesialis'],
            'exp_years' => $data['exp-years'],
        ];

        $dokterModel->where('id_user', $id)->set($dokterData)->update();

        return $this->response->setJSON(['success' => true]);
    }

    public function delete_dokter($id)
    {
        $userModel = new UserModel();
        $dokterModel = new DokterModel();
        $chatModel = new ChatModel();
        $chatModel->where('id_penerima', $id)->orWhere('id_pengirim', $id)->delete();
        $dokterModel->where('id_user', $id)->delete();
        $userModel->delete($id);

        return redirect()->back()->with('success', 'Menghapus tenaga kesehatan berhasil.');
    }

    public function pasien_screen()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $pasienModel = new PasienModel();

            $pasiens = $pasienModel->getAllPatients();

            $filteredPasiens = [];
            foreach ($pasiens as $row) {
                $filteredPasiens[] = [
                    'id' => $row['id'],
                    'id_user' => $row['id_user'],
                    'name' => $row['nama'],
                    'username' => $row['username'],
                    'gender' => $row['gender'],
                    'tanggal_lahir' => $row['tgl_lahir'],
                    'nomorHandphone' => $row['no_hp'],
                    'pekerjaan' => $row['pekerjaan'],
                    'history' => $row['riwayat'],
                    'email' => $row['email']
                ];
            }
            return view('admin/pasien-screen', ['pasiens' => $filteredPasiens]);
        } else {
            return redirect()->to('/admin');
        }
    }

    public function save_pasien()
    {
        $userModel = new UserModel();
        $pasienModel = new PasienModel();
        $statCourseModel = new StatCourseModel();
        $data = esc($this->request->getPost());

        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'tanggal-lahir' => 'required|valid_date[Y-m-d]',
            'pekerjaan' => 'required',
            'riwayat' => 'required',
            'no-hp' => 'required',
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[user.email]',
                'errors' => [
                    'is_unique' => 'Email ini sudah terdaftar.'
                ]
            ],
            'username' => [
                'label' => 'Username',
                'rules' => 'required|is_unique[user.username]',
                'errors' => [
                    'is_unique' => 'Nama pengguna ini sudah digunakan.'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|regex_match[/(?=.*[A-Z])(?=.*\d).{8,}/]',
                'errors' => [
                    'regex_match' => 'Kata sandi harus minimal 8 karakter dan mengandung minimal satu huruf besar dan satu angka.'
                ]
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Simpan ke tabel user
        $userData = [
            'role' => 'pasien',
            'foto' => 'assets/images/learning/default.jpg',
            'nama' => $data['name'],
            'gender' => $data['gender'],
            'tgl_lahir' => $data['tanggal-lahir'],
            'no_hp' => $data['no-hp'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_ARGON2ID),
        ];

        $userModel->insert($userData);
        $userId = $userModel->getID($data['username']);

        // Simpan ke tabel dokter
        $pasienData = [
            'id_user' => $userId,
            'pekerjaan' => $data['pekerjaan'],
            'riwayat' => $data['riwayat'],
        ];

        $pasienModel->insert($pasienData);

        $id_pasien = $pasienModel->getDataID($userId)["id"];
        $statPasien = [
            'id_pasien' => $id_pasien,
            'pretest' => 'belum',
            'posttest' => 'belum',
            'course' => 0
        ];
        $statCourseModel->insert($statPasien);
        return $this->response->setJSON(['success' => true]);
    }

    public function update_pasien($id)
    {
        $userModel = new UserModel();
        $pasienModel = new PasienModel();
        $data = esc($this->request->getPost());

        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'tanggal-lahir' => 'required|valid_date[Y-m-d]',
            'pekerjaan' => 'required',
            'riwayat' => 'required',
            'no-hp' => 'required',
            'email' => [
                'label' => 'Email',
                'rules' => "required|valid_email|is_unique[user.email,id,{$id}]",
                'errors' => [
                    'is_unique' => 'Email ini sudah terdaftar oleh pengguna lain.'
                ]
            ],
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[user.username,id,{$id}]",
                'errors' => [
                    'is_unique' => 'Nama pengguna ini sudah digunakan oleh orang lain.'
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Update tabel user
        $userData = [
            'nama' => $data['name'],
            'gender' => $data['gender'],
            'tgl_lahir' => $data['tanggal-lahir'],
            'no_hp' => $data['no-hp'],
            'email' => $data['email'],
            'username' => $data['username'],
        ];

        $userModel->update($id, $userData);

        // Update tabel dokter
        $pasienData = [
            'pekerjaan' => $data['pekerjaan'],
            'riwayat' => $data['riwayat'],
        ];

        $pasienModel->where('id_user', $id)->set($pasienData)->update();

        return $this->response->setJSON(['success' => true]);
    }

    public function delete_pasien($id)
    {
        $userModel = new UserModel();
        $statusCourseModel = new StatCourseModel();
        $pasienModel = new PasienModel();
        $chatModel = new ChatModel();
        $chatModel->where('id_penerima', $id)->orWhere('id_pengirim', $id)->delete();
        $id_pasien = $pasienModel->where('id_user', $id)->first()['id'];
        $statusCourseModel->where('id_pasien', $id_pasien)->delete();
        $pasienModel->where('id_user', $id)->delete();
        $userModel->delete($id);

        return redirect()->back()->with('success', 'Menghapus pasien berhasil.');
    }

    public function content()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            return view('admin/content-screen');
        } else {
            return redirect()->to('/admin');
        }
    }

    public function pre_test_screen()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $pretestModel = new PretestModel();
            $optPretestModel = new OptPretestModel();
            $questions = $pretestModel->findAll();
            foreach ($questions as &$question) {
                $question['options'] = $optPretestModel->where('id_pretest', $question['id'])->findAll();
            }
            return view('admin/pre-test-screen', ['questions' => $questions]);
        } else {
            return redirect()->to('/admin');
        }
    }

    public function post_test_screen()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $postTestModel = new PostTestModel;
            $optPostTestModel = new OptPostTestModel;
            $questions = $postTestModel->findAll();
            foreach ($questions as &$question) {
                $question['options'] = $optPostTestModel->where('id_posttest', $question['id'])->findAll();
            }
            return view('admin/post-test-screen', ['questions' => $questions]);
        } else {
            return redirect()->to('/admin');
        }
    }

    public function course_screen()
    {
        $session = session();
        if ($session->has('admin_in') || $session->get('admin_in') === true) {
            $this->setHeaders();
            $courseModel = new \App\Models\CourseModel();
            $data['courses'] = $courseModel->findAll();
            return view('admin/cources-screen', $data);
        } else {
            return redirect()->to('/admin');
        }
    }
    public function nilai_posttest()
{
    $session = session();
    if ($session->has('admin_in') || $session->get('admin_in') === true) {
        $keyword = $this->request->getGet('search');

        $hasilPosttestModel = new \App\Models\HasilPosttestModel();
        $ansPosttestModel = new \App\Models\AnsPosttestModel();
        $posttestModel = new \App\Models\PosttestModel();

        // Ambil semua soal sekali saja (bisa juga per user kalau mau dinamis)
        $soalList = $posttestModel->findAll();
        $soalById = [];
        foreach ($soalList as $soal) {
            $soalById[$soal['id']] = $soal;
        }

        $users = $hasilPosttestModel
            ->select('hasil_posttest.*, user.nama, user.email, user.no_hp, pasien.id as id_pasien')
            ->join('pasien', 'pasien.id = hasil_posttest.id_pasien')
            ->join('user', 'user.id = pasien.id_user')
            ->orderBy('hasil_posttest.id', 'DESC')
            ->findAll();

        // Loop user untuk ambil jawaban & cocokkan dengan soal
        foreach ($users as $i => $u) {
            $jawabanList = $ansPosttestModel
                ->where('id_pasien', $u['id_pasien'])
                ->findAll();

            foreach ($jawabanList as $j => $jawaban) {
                $soal = $soalById[$jawaban['id_posttest']] ?? ['pertanyaan' => '(Soal tidak ditemukan)'];

                $jawabanList[$j]['soal'] = $soal;
                $jawabanList[$j]['benar'] = $jawaban['benar'] ?? 0;
            }

            $users[$i]['jawaban'] = $jawabanList;
        }

        return view('admin/nilai-posttest-screen', [
            'users' => $users,
            'pager' => null,
            'keyword' => $keyword
        ]);
    } else {
        return redirect()->to('/admin');
    }
}
}


// public function nilaiPosttest()
// {
//     $hasilPosttestModel = new HasilPosttestModel();
//     $pasienModel = new PasienModel();
//     $ansPosttestModel = new AnsPosttestModel();
//     $posttestModel = new PostTestModel();
//     $optPostTestModel = new OptPostTestModel();

//     $users = $hasilPosttestModel->findAll();

//     foreach ($users as &$user) {
//         // Ambil jawaban untuk pasien ini
//         $user['jawaban'] = $ansPosttestModel->where('id_pasien', $user['id_pasien'])->findAll();
        
//         // Pastikan ada data jawaban sebelum melanjutkan
//         if (empty($user['jawaban'])) {
//             $user['jawaban'] = []; // Atur sebagai array kosong jika tidak ada jawaban
//         }

//         // Ambil detail soal dan pilihan
//         foreach ($user['jawaban'] as &$jawaban) {
//             $soal = $posttestModel->find($jawaban['id_posttest']);
//             $jawaban['soal'] = $soal;
//             $pilihan = $optPostTestModel->where('id_posttest', $jawaban['id_posttest'])->findAll();
//             $jawaban['pilihan'] = $pilihan;

//             // Tentukan apakah jawaban benar atau salah
//             $jawaban['benar'] = ($jawaban['id_pilihan'] == $soal['id_jawaban']) ? true : false;
//         }

//         // Hitung jumlah soal yang benar
//         $user['jumlah_benar'] = array_reduce($user['jawaban'], function ($carry, $item) {
//             return $carry + ($item['benar'] ? 1 : 0);
//         }, 0);

//         $user['jumlah_soal'] = count($user['jawaban']);
//         $user['nilai'] = $user['jumlah_soal'] > 0 ? round(($user['jumlah_benar'] / $user['jumlah_soal']) * 100, 2) : 0;
//     }

//     $data = [
//         'users' => $users
//     ];

//     return view('admin/nilai-posttest-screen', $data);
// }

