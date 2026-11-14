<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>طباعة تسجيل المواد</title>
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 4px; }
</style>
</head>
<body>
<h1>إدارة تسجيل المواد</h1>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>اسم الطالب</th>
            <th>القسم</th>
            <th>المادة</th>
            <th>الفصل</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registrations as $reg)
        <tr>
            <td>{{ $reg['id'] }}</td>
            <td>{{ $reg['student_name'] }}</td>
            <td>{{ $reg['department'] }}</td>
            <td>{{ $reg['subject'] }}</td>
            <td>{{ $reg['semester'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    window.onload = function() { window.print(); }
</script>
</body>
</html>
