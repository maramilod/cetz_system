<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Language" content="ar">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <title>{{ config('app.name', 'نظام الكلية') }}</title>
  @vite('resources/css/app.css')
  @vite('resources/js/app.js')

  <style>
    :root{--app-font:"Tajawal",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Noto Sans Arabic","Noto Sans",Arial,"Apple Color Emoji","Segoe UI Emoji"}
    body{font-family:var(--app-font);}
    table thead th{font-weight:600}
  </style>
</head>
<body class="bg-gray-50 text-gray-800 ">
  <div class="min-h-screen flex">
    @include('ui.partials.sidebar')

    <div class="flex-1">
      @include('ui.partials.header')
      <main class="p-6">
        @yield('content')
      </main>
    </div>
  </div>
</body>
</html>


