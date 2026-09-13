<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Language" content="ar">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('fonts/tajawal.css') }}" rel="stylesheet">
    <title>إفادة تخرج</title>
</head>
<body style="margin:0; padding:0; background:#fff;">
    <div class="certificate-page">
        @include('students.partials.graduation-certificate-content', ['certificateData' => $certificateData])
    </div>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
