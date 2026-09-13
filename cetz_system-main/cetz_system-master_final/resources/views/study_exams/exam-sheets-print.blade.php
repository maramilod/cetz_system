<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة كشوفات المواد</title>
    <style>
        body {
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif;
            color: #111;
            margin: 18px;
        }

        .sheet {
            page-break-after: always;
        }

        .sheet:last-child {
            page-break-after: auto;
        }

        .header {
            text-align: center;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .header h2,
        .header h3 {
            margin: 0;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px 20px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            height: 24px;
        }

        th {
            background: #f2f2f2;
            font-weight: 700;
        }

        .name-cell {
            text-align: right;
            width: 210px;
        }

        .carry-row td {
            background: #ffe5e5;
            color: #9f1239;
            font-weight: 700;
        }

        .legend {
            margin-top: 10px;
            font-size: 12px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 42px;
            font-size: 13px;
            font-weight: 700;
        }

        .signatures div {
            flex: 1;
            text-align: center;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
@forelse($sheets as $sheet)
    <section class="sheet">
        <div class="header">
            <h3>كلية التقنية الهندسية زوارة</h3>
            <h2>كشف درجات الطلبة</h2>
        </div>

        <div class="meta">
            <div><strong>اسم المادة:</strong> {{ $sheet['course_name'] }}</div>
            <div><strong>رمز المادة:</strong> {{ $sheet['course_code'] ?: '-' }}</div>
            <div><strong>السيمستر:</strong> {{ $sheet['semester_label'] }}</div>
            <div><strong>الأستاذ:</strong> {{ $sheet['teacher_label'] }}</div>
            <div><strong>القسم:</strong> {{ $sheet['department_name'] }}</div>
            <div><strong>الشعبة:</strong> {{ $sheet['section_name'] }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width:40px;">ر.ت</th>
                    <th rowspan="2" class="name-cell">اسم الطالب</th>
                    <th rowspan="2" style="width:95px;">رقم القيد</th>
                    <th colspan="3">أعمال السنة</th>
                    <th colspan="3">النهائي</th>
                    <th rowspan="2" style="width:70px;">المجموع</th>
                    <th rowspan="2" style="width:75px;">الحالة</th>
                </tr>
                <tr>
                    <th>عملي</th>
                    <th>نظري</th>
                    <th>مجموع أعمال السنة 40%</th>
                    <th>عملي</th>
                    <th>نظري</th>
                    <th>مجموع الامتحان النهائي 60%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sheet['rows'] as $index => $row)
                    <tr class="{{ $row['is_carry'] ? 'carry-row' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td class="name-cell">{{ $row['student_name'] }}</td>
                        <td>{{ $row['student_number'] }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $row['is_carry'] ? 'مرحل' : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">لا يوجد طلبة يدرسون هذه المادة حاليا.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="legend">الصفوف المظللة باللون الأحمر تعني أن الطالب مرحل ويعيد دراسة المادة.</div>

        <div class="signatures">
            <div>أستاذ المادة</div>
            <div>رئيس القسم</div>
            <div>مدير مكتب الدراسة والامتحانات</div>
        </div>
    </section>
@empty
    <p>لا توجد مواد مطابقة للبحث في السيمستر الحالي.</p>
@endforelse

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
