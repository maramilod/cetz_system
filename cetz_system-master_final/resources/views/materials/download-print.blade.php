<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>طباعة المواد</title>
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 4px; }
</style>
</head>
<body>
<h1>قائمة المواد</h1>

<table>
    <thead>
        <tr>
            <th>رقم</th>
            <th>رمز</th>
            <th>اسم المادة</th>
            <th>الوحدات</th>
            <th>الساعات</th>
            <th>تعتمد على</th>
            <th>بديلة عن</th>
            <th>المستخدم</th>
        </tr>
    </thead>
    <tbody>
        @foreach($materials as $m)
        <tr>
            <td>{{ $m['number'] }}</td>
            <td>{{ $m['code'] }}</td>
            <td>{{ $m['name'] }}</td>
            <td>{{ $m['units'] }}</td>
            <td>{{ $m['hours'] }}</td>
            <td>{{ $m['depends_on'] }}</td>
            <td>{{ $m['alternative_for'] }}</td>
            <td>{{ $m['user_name'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    window.onload = function() { window.print(); }
</script>
</body>
</html>
