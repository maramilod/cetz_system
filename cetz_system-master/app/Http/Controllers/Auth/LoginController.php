<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // عرض صفحة تسجيل الدخول
    public function showLoginForm()
    {
        // إذا كان المستخدم مسجل الدخول بالفعل نوجهه للداشبورد
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login'); // صفحة تسجيل الدخول
    }

    // معالجة تسجيل الدخول
   public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // محاولة التحقق من بيانات الدخول
    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // التحقق من حالة المستخدم
        if (!$user->is_active) {
            Auth::logout(); // إنهاء الجلسة
            return back()->withErrors([
                'email' => 'حسابك غير مفعل أو تم إيقافه.'
            ])->onlyInput('email');
        }

        // حماية من هجمات الـ session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'بيانات الدخول غير صحيحة',
    ])->onlyInput('email');
}

    // تسجيل الخروج
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate(); // إنهاء كل الجلسات
        $request->session()->regenerateToken(); // حماية CSRF

        return redirect()->route('login'); // العودة لصفحة تسجيل الدخول
    }
}
