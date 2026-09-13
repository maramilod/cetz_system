<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        /* اجعل الصفحة كاملة من اليمين لليسار */
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;           /* الاتجاه من اليمين لليسار */
            text-align: right;        /* محاذاة النص لليمين */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            direction: rtl;           /* جدول من اليمين لليسار */
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;       /* خلي العناوين والبيانات متوسطة */
        }

        th {
            background-color: #f0f0f0;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<h2>كشف الطالب: {{ $student->full_name }}</h2>

<table>
    <thead>
        <tr>
            <th>رمز المادة</th>
            <th>اسم المادة</th>
            <th>الوحدات</th>
            <th>الساعات</th>
            <th>المجموع</th>
            <th>الحالة</th>
            <th>المحاولة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($enrollments as $e)
        <tr>
            <td>{{ $e->courseOffering->course->course_code }}</td>
            <td>{{ $e->courseOffering->course->name }}</td>
            <td>{{ $e->courseOffering->course->units }}</td>
            <td>{{ $e->courseOffering->course->hours }}</td>
            <td>{{ $e->grade?->total ?? '-' }}</td>
            <td>{{ $e->status }}</td>
            <td>{{ $e->attempt }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
