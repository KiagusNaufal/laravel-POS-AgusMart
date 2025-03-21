<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            if ($user->role == 'admin') {
                return redirect()->route('admin')->with('success', 'Selamat datang, Admin');
            } elseif ($user->role == 'kasir') {
                return redirect()->route('kasir')->with('success', 'Selamat datang, Kasir');
            } elseif ($user->role == 'super') {
                return redirect()->route('super')->with('success', 'Selamat datang, Super Admin');
            } else {
                return redirect('/');
            }
        }
        

        return back()->with('error', 'Email atau password salah');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth');
    }
    
    public function log_in(Request $request)
    {   
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        return back()->withErrors([
            'error' => 'email atau password salah'
        ]);
    }
}
