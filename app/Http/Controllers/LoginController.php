<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Menampilkan halaman login
        return view('auth.login');
    }

    /**
     * Melakukan proses login menggunakan email dan password.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validasi input dari user (email dan password)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        // Memeriksa apakah kredensial yang dimasukkan benar
        if (Auth::attempt($request->only('email', 'password'))) {
            // Jika berhasil login, mengambil data user yang sedang login
            $user = Auth::user();
    
            // Menentukan arah redirect berdasarkan role user
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin')->with('success', 'Selamat datang, Admin');
                case 'kasir':
                    return redirect()->route('kasir')->with('success', 'Selamat datang, Kasir');
                case 'super':
                    return redirect()->route('super')->with('success', 'Selamat datang, Super Admin');
                default:
                    return redirect('/');
            }
        }
    
        // Jika login gagal, menampilkan pesan error
        return back()->with('error', 'Email atau password salah');
    }

    /**
     * Melakukan proses logout.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Melakukan logout dan menghapus sesi pengguna
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Mengarahkan pengguna kembali ke halaman login
        return redirect()->route('auth');
    }
    
}
