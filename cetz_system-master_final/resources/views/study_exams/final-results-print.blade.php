<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة النتيجة النهائية</title>
    <style>
        body {
            font-family: "Tahoma", sans-serif;
            margin: 24px;
            color: #111827;
            direction: rtl;
        }

        .report-head {
            text-align: center;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .report-head h2,
        .report-head h3,
        .report-head p {
            margin: 0;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            margin: 12px 0;
            flex-wrap: wrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
        }

        .signatures {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 12mm;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="report-head">
        <h3>كلية التقنية الهندسية زوارة</h3>
        <h2>النتيجة النهائية</h2>
    </div>

    <div class="meta">
        <div><strong>الفصل الدراسي:</strong> {{ $selectedTermType }}</div>
        <div><strong>السنة:</strong> {{ $selectedYear }}</div>
        <div><strong>القسم:</strong> {{ $selectedDepartmentName }}</div>
        <div><strong>الفصل رقم:</strong> {{ $selectedSemesterNumber }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ر.ت</th>
                <th>الاسم</th>
                <th>رقم القيد</th>
                <th>النسبة</th>
                <th>التقدير</th>
                @foreach($courses as $course)
                    <th>{{ $course['name'] }}</th>
                @endforeach
                <th>المواد المرحلة</th>
                <th>درجة المواد المرحلة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['student_number'] }}</td>
                    <td>{{ $row['average'] }}</td>
                    <td>{{ $row['classification'] }}</td>
                    @foreach($row['course_grades'] as $courseGrade)
                        <td>{{ $courseGrade['grade'] }}</td>
                    @endforeach
                    <td style="text-align:right;">
                        @forelse($row['carry_courses'] as $carry)
                            <div>{{ $carry['course_name'] }}</div>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td>
                        @forelse($row['carry_courses'] as $carry)
                            <div>{{ $carry['grade'] }}</div>
                        @empty
                            -
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 7 + count($courses) }}">لا توجد نتائج مطابقة للفلاتر المختارة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div>مراجعة</div>
        <div>مدير مكتب التسجيل والدراسة والامتحانات</div>
        <div>عميد الكلية</div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
