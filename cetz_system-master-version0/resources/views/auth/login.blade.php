<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - نظام الكلية</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-beige-100 flex items-center justify-center h-screen">

    <div class="w-full max-w-sm bg-beige-50 p-6 rounded shadow-lg text-right border border-red-600">
        <!-- شعار الكلية -->
        <div class="flex justify-center mb-4">
            <img src="{{ asset('images/college-logo.png') }}" alt="شعار الكلية" class="w-24 h-24 object-contain">
        </div>

        <h1 class="text-2xl font-bold mb-6 text-red-700 text-center">تسجيل الدخول</h1>

        @if($errors->any())
            <div class="text-red-500 mb-4 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold text-red-700">البريد الإلكتروني</label>
                <input type="email" name="email" class="w-full border border-red-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500" required>
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-semibold text-red-700">كلمة المرور</label>
                <input type="password" name="password" class="w-full border border-red-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500" required>
            </div>

            <button type="submit" class="w-full bg-red-700 text-white py-2 rounded hover:bg-red-800 transition-colors">
                دخول
            </button>
        </form>
    </div>

</body>
</html>
