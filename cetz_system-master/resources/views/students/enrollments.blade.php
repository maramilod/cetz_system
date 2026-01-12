@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    {{-- نموذج البحث عن الطالب --}}
    <form action="{{ route('students.enrollments') }}" method="GET" class="mb-6 flex gap-2 items-center">
        <input type="text" name="student_number" placeholder="رقم القيد أو الاسم"
               value="{{ request('student_number') }}"
               class="border rounded px-3 py-2 w-64"
               required>
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            عرض كشف المواد
        </button>
    </form>

    {{-- عنوان الطالب --}}
    <h1 class="text-2xl font-bold mb-6">
        كشف الطالب: {{ $student->full_name }}
        <span class="text-gray-600 text-base">
            ({{ $student->student_number ?? $student->manual_number }})
        </span>
    </h1>

    {{-- أزرار التصدير والطباعة --}}
    <div class="flex gap-2 mb-4">
        <button onclick="exportExcel()"
                class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
            Excel (الكل)
        </button>

        <button onclick="printTable()"
                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
            PDF (الكل)
        </button>

        <button onclick="printActiveSemester()"
        class="bg-yellow-600 hover:bg-yellow-800 text-white px-4 py-2 rounded">
    طباعة الفصل الحالي 
</button>

    </div>

    {{-- عرض الفصول والمواد --}}
    @forelse($semesterEnrollments as $semester)
        <h2 class="text-xl font-bold mt-6 mb-2">
            السنة: {{ $semester['year'] }} | الفصل: {{ $semester['term_type'] }} | اسم الفصل: {{ $semester['semester_name'] }}
        </h2>
        <div class="mb-8 rounded-lg shadow
            {{ $semester['active'] ? 'border-2 border-green-500 bg-green-50' : 'bg-white' }}">
            
            {{-- عنوان الفصل --}}
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h2 class="text-lg font-semibold">{{ $semester['semester_name'] }}</h2>
                <span class="px-3 py-1 text-sm rounded-full
                    {{ $semester['active'] ? 'bg-green-600 text-white' : 'bg-gray-400 text-white' }}">
                    {{ $semester['active'] ? 'فصل مفعل' : 'فصل غير مفعل' }}
                </span>
            </div>

            {{-- جدول المواد --}}
            <div class="overflow-x-auto p-4 mt-2">
                <table class="w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">رمز المادة</th>
                            <th class="border px-2 py-1">اسم المادة</th>
                            <th class="border px-2 py-1">الوحدات</th>
                            <th class="border px-2 py-1">الساعات</th>
                            <th class="border px-2 py-1">المجموع</th>
                            <th class="border px-2 py-1">الحالة</th>
                            <th class="border px-2 py-1">المحاولة</th>
                            <th class="border px-2 py-1">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semester['enrollments'] as $e)
                            <tr class="text-center">
                                <td class="border px-2 py-1">{{ $e['course_code'] }}</td>
                                <td class="border px-2 py-1 text-right">{{ $e['course_name'] }}</td>
                                <td class="border px-2 py-1">{{ $e['units'] }}</td>
                                <td class="border px-2 py-1">{{ $e['hours'] }}</td>
                                <td class="border px-2 py-1 font-semibold">{{ $e['total'] ?? '-' }}</td>
                                <td class="border px-2 py-1">
                                    @php
                                        $colors = [
                                            'passed' => 'text-green-600',
                                            'failed' => 'text-red-600',
                                            'in_progress' => 'text-blue-600',
                                            'equivalent' => 'text-purple-600',
                                        ];
                                    @endphp
                                    <span class="{{ $colors[$e['status']] ?? '' }}">
                                        {{ __($e['status']) }}
                                    </span>
                                </td>
                                <td class="border px-2 py-1">{{ $e['attempt'] }}</td>
                                <td class="border px-2 py-1">
                                    @if($e['grade'])
                                        <button onclick="showGradeDetails({{ json_encode($e) }})"
                                                class="text-blue-600 underline text-xs">
                                            عرض
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- المجاميع للفصل --}}
                <div class="mt-3 font-semibold text-gray-700 px-4">
                    مجموع الوحدات: {{ $semester['total_units'] }} |
                    مجموع الساعات: {{ $semester['total_hours'] }} |
                    معدل الفصل: {{ $semester['gpa'] ?? '-' }}
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">
            لا توجد مواد مسجلة لهذا الطالب
        </div>
    @endforelse

    {{-- زر الرجوع وزر التعيين كخريج --}}
    <div class="mt-6 flex gap-2">
        <a href="{{ route('students.index') }}"
           class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            رجوع
        </a>

        <form action="{{ route('students.setGraduated', $student->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                    onclick="return confirm('⚠️ هذا القرار نهائي ولا يمكن التراجع عنه!\n\nيرجى التأكد من مراجعة كل مواد الطالب قبل المتابعة.\n\nهل أنت متأكد من تعيين هذا الطالب كخريج؟');"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                تعيين كخريج
            </button>
        </form>
    </div>

</div>

{{-- Modal الدرجات --}}
<div id="gradeModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-6">
        <h2 class="text-lg font-bold mb-4">تفاصيل الدرجات</h2>
        <table class="w-full text-sm border">
            <tbody id="gradeDetails"></tbody>
        </table>
        <div class="mt-4 text-right">
            <button onclick="closeModal()"
                    class="bg-gray-600 text-white px-4 py-1 rounded">
                إغلاق
            </button>
        </div>
    </div>
</div>

{{-- جداول مخفية للطباعة --}}
<div id="printTables" class="hidden">
    @forelse($semesterEnrollments as $semester)

<table
    data-start="{{ $semester['start_date'] }}"
    data-end="{{ $semester['end_date'] }}"
    data-active="{{ $semester['active'] ? '1' : '0' }}"
    data-year="{{ $semester['year'] }}"
    data-term_type="{{ $semester['term_type'] }}"
>

            <thead>
                <tr>
                    <th>رمز المادة</th>
                    <th>اسم المادة</th>
                    <th>الوحدات</th>
                    <th>الساعات</th>
                    <th>المجموع</th>
                    <th>المحاولة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semester['enrollments'] as $e)
                    <tr>
                        <td>{{ $e['course_code'] }}</td>
                        <td>{{ $e['course_name'] }}</td>
                        <td>{{ $e['units'] }}</td>
                        <td>{{ $e['hours'] }}</td>
                        <td>{{ $e['total'] ?? '-' }}</td>
                        <td>{{ $e['attempt'] }}</td>
                    </tr>
                @endforeach
                {{-- مجاميع الفصل للطباعة --}}
                <tr style="font-weight:bold; background-color:#f0f0f0;">
                    <td colspan="2" style="text-align:right;">مجاميع الفصل</td>
                    <td>{{ $semester['total_units'] }}</td>
                    <td>{{ $semester['total_hours'] }}</td>
                    <td>{{ $semester['gpa'] ?? '-' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @empty
        <tr><td colspan="6">لا توجد مواد للطباعة</td></tr>
    @endforelse
</div>

{{-- JS للتصدير والطباعة --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
function showGradeDetails(enrollment) {
    const tbody = document.getElementById('gradeDetails');
    tbody.innerHTML = '';

    if (!enrollment.grade) {
        tbody.innerHTML = '<tr><td colspan="2" class="text-center">لا توجد درجات</td></tr>';
    } else {
        const grade = enrollment.grade;
        for (const [key, value] of Object.entries(grade)) {
            if (['id','student_id','enrollment_id'].includes(key)) continue;
            tbody.innerHTML += `<tr>
                <td class="border px-2 py-1 font-semibold text-right">${key.replace('_',' ')}</td>
                <td class="border px-2 py-1 text-center">${value ?? '-'}</td>
            </tr>`;
        }
    }

    document.getElementById('gradeModal').classList.remove('hidden');
    document.getElementById('gradeModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('gradeModal').classList.add('hidden');
    document.getElementById('gradeModal').classList.remove('flex');
}

function exportExcel() {
    const table = document.querySelector('table');
    if (!table) return alert('لا توجد بيانات لتصديرها.');

    const data = [];
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(tr => {
        const rowData = {};
        tr.querySelectorAll('td').forEach((td,i)=>{ rowData[headers[i]]=td.innerText.trim(); });
        data.push(rowData);
    });

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Students");
    XLSX.writeFile(wb, "students-list.xlsx");
}

function printTable() {
  const tables = document.querySelectorAll('#printTables table');
    if (!tables.length) return alert('لا توجد بيانات للطباعة.');

    const grouped = {};

    // تجميع كل فصل حسب تاريخ البداية والنهاية
    tables.forEach(table => {
        const start = table.dataset.start;
        const end = table.dataset.end;
        const year = table.dataset.year;
        const term_type = table.dataset.term_type;
        const key = start + '_' + end; // استخدم التواريخ كمعرف

        if (!grouped[key]) {
            grouped[key] = {
                headers: [],
                rows: [],
                total_units: 0,
                total_hours: 0,
                gpa_list: [],
                year,
                term_type,
                start,
                end
            };
            grouped[key].headers = Array.from(table.querySelectorAll('thead th'))
                                        .map(th => th.innerText.trim())
                                        .map(h => h === 'المحاولة' ? 'الدور' : h);
        }

        table.querySelectorAll('tbody tr').forEach(tr => {
            const tds = Array.from(tr.querySelectorAll('td'));
            if (!tds.length) return;
            if (tds[0].innerText.includes('مجاميع')) return;

            const row = tds.map(td => td.innerText.trim());
            grouped[key].rows.push(row);

            grouped[key].total_units += parseFloat(tds[2].innerText) || 0;
            grouped[key].total_hours += parseFloat(tds[3].innerText) || 0;
            if (tds[4].innerText && tds[4].innerText !== '-') grouped[key].gpa_list.push(parseFloat(tds[4].innerText));
        });
    });

    // بناء HTML للطباعة
    let combinedHtml = '';
    for (const [key, data] of Object.entries(grouped)) {
        combinedHtml += `
        <div class="semester" style="page-break-inside: avoid;">
            <h3 style="text-align:right; margin-bottom:5px;">
                السنة: ${data.year} | الفصل: ${data.term_type} | من ${data.start} إلى ${data.end}
            </h3>
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <thead>
                    <tr>
                        ${data.headers.map(h => `<th style="border:1px solid #ccc; padding:6px; background:#f0f0f0;">${h}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${data.rows.map(r => `<tr>${r.map(c => `<td style="border:1px solid #ccc; padding:6px; text-align:center;">${c}</td>`).join('')}</tr>`).join('')}
                    <tr style="font-weight:bold; background:#d1ffd1;">
                        <td colspan="2" style="text-align:right; border:1px solid #ccc;">مجاميع الفصل</td>
                        <td style="border:1px solid #ccc; text-align:center;">${data.total_units}</td>
                        <td style="border:1px solid #ccc; text-align:center;">${data.total_hours}</td>
                        <td style="border:1px solid #ccc; text-align:center;">
                            ${data.gpa_list.length ? (data.gpa_list.reduce((a,b)=>a+b,0)/data.gpa_list.length).toFixed(2) : '-'}
                        </td>
                        <td style="border:1px solid #ccc;"></td>
                    </tr>
                </tbody>
            </table>
        </div>`;
    }

    const footerHtml = `
    <div class="footer">
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>قسم الدراسة والامتحانات</p>
        </div>
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>المسجل العام</p>
        </div>
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>عميد الكلية</p>
        </div>
    </div>
    `;
const studentName = "{{ $student->full_name }}"; // اسم الطالب من الباكيند

const win = window.open('', '_blank', 'width=900,height=1200');
win.document.write(`
    <html>
        <head>
            <title>كشف درجات الطالب</title>
            <style>
                @page { size: A4; margin: 50px; }
                body { font-family: Arial; direction: rtl; margin:0; padding:0; }
                h1, h2 { text-align: center; margin: 5px 0; }
                h3 { margin-bottom:5px; text-align:right; }
                table { width:100%; border-collapse:collapse; page-break-inside: avoid; margin-bottom:20px; }
                th, td { border:1px solid #ccc; padding:6px; text-align:center; }
                th { background:#f0f0f0; }
                tr:last-child td { font-weight:bold; background:#d1ffd1; }
                .semester { page-break-inside: avoid; margin-bottom: 60px; }
                .footer { position: fixed; bottom: 0; width:100%; display:flex; justify-content: space-between; font-family: Arial; background:white; }
            </style>
        </head>
        <body>
            <h1>كشف درجات الطالب</h1>
            <h2>${studentName}</h2> <!-- اسم الطالب -->
            ${combinedHtml}
            ${footerHtml}
        </body>
    </html>
`);
win.document.close();
win.focus();
win.print();
win.close();
}
function printActiveSemester() {
      const tables = document.querySelectorAll('#printTables table[data-active="1"]');

    if (!tables.length) {
        alert('لا يوجد فصل مفعل للطباعة');
        return;
    }

    let headers = [];
    let rows = [];
    let totalUnits = 0;
    let totalHours = 0;
    let totalGrades = 0;
    let countedGrades = 0;

    tables.forEach((table, tableIndex) => {
        if (tableIndex === 0) {
            headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        }

        table.querySelectorAll('tbody tr').forEach(tr => {
            const tds = Array.from(tr.querySelectorAll('td'));
            if (!tds.length) return;
            if (tds[0].innerText.includes('مجاميع')) return;

            rows.push(tds.map(td => td.innerText.trim()));

            totalUnits += parseFloat(tds[2].innerText) || 0;
            totalHours += parseFloat(tds[3].innerText) || 0;

            if (tds[4].innerText && tds[4].innerText !== '-') {
                totalGrades += parseFloat(tds[4].innerText) || 0;
                countedGrades++;
            }
        });
    });

    const avgGrades = countedGrades ? (totalGrades / countedGrades).toFixed(2) : '-';

    let tableHtml = `
        <table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;direction:rtl;margin-bottom:100px;">
            <thead>
                <tr>
                    ${headers.map(h => `
                        <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">
                            ${h}
                        </th>
                    `).join('')}
                </tr>
            </thead>
            <tbody>
                ${rows.map(r => `
                    <tr>
                        ${r.map(c => `
                            <td style="border:1px solid #ccc;padding:8px;text-align:center;">
                                ${c}
                            </td>
                        `).join('')}
                    </tr>
                `).join('')}

                <tr style="font-weight:bold;background:#d1ffd1;">
                    <td colspan="2" style="text-align:right;border:1px solid #ccc;">المجموع</td>
                    <td style="border:1px solid #ccc;text-align:center;">${totalUnits}</td>
                    <td style="border:1px solid #ccc;text-align:center;">${totalHours}</td>
                    <td style="border:1px solid #ccc;text-align:center;">${avgGrades}</td>
                    <td style="border:1px solid #ccc;"></td>
                </tr>
            </tbody>
        </table>
    `;

    // توقيع ثابت أسفل الصفحة
    const signaturesHtml = `
        <div style="
            position: fixed;
            bottom: 50px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            font-family: Arial, sans-serif;
        ">
            <div style="text-align:center;">
                <p>_____________________</p>
                <p>قسم الدراسة والامتحانات</p>
            </div>
            <div style="text-align:center;">
                <p>_____________________</p>
                <p>المسجل العام</p>
            </div>
        </div>
    `;

  const activeTable = tables[0];
const year = activeTable.dataset.year;
const termType = activeTable.dataset.term_type;

const win = window.open('', '_blank', 'width=900,height=1200');
win.document.write(`
<html>
<head>
    <title>كشف درجات الفصل</title>
    <style>
        @page { size: A4; margin: 50px; }
        body { font-family: Arial; direction: rtl; margin: 0; padding: 0; }
        h1 { text-align: center; margin-bottom: 20px; }
        table th, table td { border:1px solid #ccc; padding:6px; }
        table th { background-color:#f0f0f0; }
        table tr:last-child td { font-weight:bold; background-color:#d1ffd1; }
    </style>
</head>
<body>
    <h1>كشف درجات الفصل: ${year} ${termType}</h1>
    ${tableHtml}
    ${signaturesHtml}
</body>
</html>
`);


    win.document.close();
    win.focus();
    win.print();
    win.close();
}

</script>
@endsection
