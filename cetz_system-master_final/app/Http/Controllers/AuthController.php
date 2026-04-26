<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // ✅ بيانات تجريبية فقط بدون قاعدة بيانات
        $demoEmail = 'admin@example.com';
        $demoPassword = '123456M#m';

        if ($request->email === $demoEmail && $request->password === $demoPassword) {
            // تخزين الجلسة لتحديد أن المستخدم مسجّل دخول
            session(['logged_in' => true, 'user_name' => 'Admin User']);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة.',
        ]);
    }

    public function logout()
    {
        session()->forget(['logged_in', 'user_name']);
        return redirect()->route('login');
    }
}
