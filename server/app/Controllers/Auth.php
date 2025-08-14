<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Login page
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'post') {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            if (empty($username) || empty($password)) {
                session()->setFlashdata('error', 'Username and password are required');
                return view('auth/login');
            }

            $user = $this->userModel->authenticate($username, $password);

            if ($user) {
                // Set session data
                session()->set([
                    'isLoggedIn' => true,
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                    'user' => $user
                ]);

                return redirect()->to('/dashboard');
            } else {
                session()->setFlashdata('error', 'Invalid username or password');
                return view('auth/login');
            }
        }

        return view('auth/login');
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        return session()->get('isLoggedIn') === true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return session()->get('role') === 'admin';
    }
} 