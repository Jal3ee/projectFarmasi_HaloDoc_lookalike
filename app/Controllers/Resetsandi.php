<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ResetModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Resetsandi extends BaseController
{
    public function sendemail()
    {
        if ($this->request->getPost('submit') === null) {
            return redirect()->back();
        }
        $rules = [
            'email' => 'required|valid_email'
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $email = \Config\Services::email();
        $userModel = new UserModel();
        $resetModel = new ResetModel();

        $userEmail = esc($this->request->getPost('email'));

        if (!$userModel->cekEmail($userEmail)) {
            $token = bin2hex(random_bytes(50)); // Membuat token acak
            //$userModel->update($user['id'], ['reset_token' => $token, 'token_expiry' => date('Y-m-d H:i:s', strtotime('+1 hour'))]); // Menyimpan token dan waktu kadaluarsa
            $id_user = $userModel->getDataByEmail($userEmail)["id"];
            $dataReset = [
                'id_user' => $id_user,
                'reset_token' => $token,
                'token_exp' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ];

            //cek token sebelumnya
            if (!$resetModel->cekID($id_user)) {
                $resetModel->deleteByIDUser($id_user);
            }

            $resetModel->insertData($dataReset);

            $resetLink = base_url("resetsandi/reset/$token");


            $config = [
                'protocol' => 'smtp',
                'SMTPHost' => 'smtp.gmail.com',
                'SMTPPort' => 587,
                'SMTPUser' => 'appfarmasi@gmail.com',
                'SMTPPass' => 'duydhuztjwencgxz', // App Password
                'mailType' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n"
            ];

            $email->initialize($config);

            $email->setFrom('appfarmasi@gmail.com', 'Pharmacy App');
            $email->setTo($userEmail);
            $email->setSubject('Reset Password');
            $email->setMessage("Click this link to reset your password: <a href='{$resetLink}'>Reset Password</a>");

            if ($email->send()) {
                return redirect()->back()->with('success', 'Check your email for the password reset link.');
            } else {
                return redirect()->back()->with('error', 'Unable to send email. Please try again.');
            }
        } else {
            return redirect()->back()->with('error', 'Email not found.');
        }
    }

    public function reset($token)
    {
        $resetModel = new ResetModel();
        $user = $resetModel->where('reset_token', $token)->where('token_exp >=', date('Y-m-d H:i:s'))->first();

        if ($user) {
            $data = [
                'reset_token' => $token,
            ];
            return view('reset_password', $data);
        } else {
            $resetModel->deleteByToken($token);
            return redirect()->to('login/lupa_password')->with('error', 'Invalid or expired reset token.');
        }

    }

    public function ubah($token)
    {
        if ($this->request->getPost('submit') === null) {
            return redirect()->back();
        }

        $resetModel = new ResetModel();
        $userModel = new UserModel();
        $user = $resetModel->where('reset_token', $token)->where('token_exp >=', date('Y-m-d H:i:s'))->first();
        if (!$user) {
            $resetModel->deleteByToken($token);
            return redirect()->to('login/lupa_password')->with('error', 'Invalid or expired reset token.');
        }

        $rules = [
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|regex_match[/(?=.*[A-Z])(?=.*\d).{8,}/]',
                'errors' => [
                    'regex_match' => 'Password must be at least 8 characters long and contain at least one uppercase letter and one number.'
                ]
            ],
            're-password' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'The password confirmation does not match the password.'
                ]
            ],
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $password = password_hash(esc($this->request->getPost('password')), PASSWORD_ARGON2ID);
        // update password
        $data = [
            'password' => $password
        ];
        $id_user = $resetModel->getID($token);
        if ($userModel->update($id_user, $data)) {
            // kode disini
            $resetModel->deleteByToken($token);
            return redirect()->to('login')->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', $userModel->errors());
        }
    }


}
