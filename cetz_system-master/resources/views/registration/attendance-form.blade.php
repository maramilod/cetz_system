@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto space-y-6">

    {{-- العنوان --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">📋 نموذج حضور وغياب - الفصل الدراسي</h1>
        <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm" onclick="printTable()">
            🖨️ طباعة
        </button>
    </div>

    {{-- فلتر المادة --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 flex flex-col md:flex-row gap-4 items-center">
        <form method="GET" action="{{ route('registration.attendance-form') }}" class="flex gap-2 items-center">
            <label class="text-sm font-medium text-gray-600">اختر المادة:</label>
            <select name="course_offering_id" class="border rounded-lg px-3 py-2">
                <option value="">كل المواد</option>
                @foreach($allCourseOfferings as $co)
                    <option value="{{ $co->id }}" @if(request('course_offering_id') == $co->id) selected @endif>
                        {{ $co->course->name }} - {{ $co->section->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">عرض</button>
        </form>

        <div class="ml-auto w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-600 mb-1">بحث بالاسم أو المادة:</label>
            <input type="text" id="searchMaterial" placeholder="اكتب اسم المادة أو اسم الأستاذ"
                class="border rounded-lg px-3 py-2 w-full">
        </div>
    </div>

    {{-- عرض كل مادة --}}
    @foreach($attendanceData as $class)
        @foreach($class['teachers'] as $teacher)
            <div class="bg-white rounded-xl shadow-sm border p-4 material-card">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-lg text-gray-800 material-name">
                        {{ $class['course_name'] }} - {{ $teacher['name'] }} ({{ $teacher['role'] }})
                    </h2>
                    <div class="text-gray-600">
                        <span class="mr-4"><strong>الشعبة:</strong> {{ $class['section_name'] }}</span>
                        <span><strong>الفصل:</strong> {{ $class['semester_name'] }}</span>
                    </div>
                </div>

                @if(count($class['students']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse border border-gray-200">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="border px-3 py-2 text-center">#</th>
                                    <th class="border px-3 py-2 text-center">رقم الطالب</th>
                                    <th class="border px-3 py-2 text-left">الاسم</th>
                                    <th class="border px-3 py-2 text-center">حضور</th>
                                    <th class="border px-3 py-2 text-center">غياب</th>
                                    <th class="border px-3 py-2 text-center">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($class['students'] as $index => $student)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="border px-3 py-2 text-center">{{ $index + 1 }}</td>
                                        <td class="border px-3 py-2 text-center">{{ $student['id'] }}</td>
                                        <td class="border px-3 py-2 text-left">{{ $student['name'] }}</td>
                                        <td class="border px-3 py-2"></td>
                                        <td class="border px-3 py-2"></td>
                                        <td class="border px-3 py-2"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">لا يوجد طلاب مسجلين في هذه المادة.</p>
                @endif
            </div>
        @endforeach
    @endforeach

</div>

{{-- فلتر البحث --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchMaterial');
    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        const cards = document.querySelectorAll('.material-card');

        cards.forEach(card => {
            const name = card.querySelector('.material-name').textContent.toLowerCase();
            if (name.includes(filter)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

function printTable() {
    const cards = document.querySelectorAll('.material-card');
    let tableHtml = '';

    cards.forEach(card => {
        // تحقق من أن البطاقة مرئية (بعد الفلترة)
        if (card.offsetParent === null) return; // غير مرئية، نتخطاها

        const title = card.querySelector('.material-name').textContent;
        const info = card.querySelector('div.text-gray-600').innerHTML;

        const studentsRows = card.querySelectorAll('table tbody tr');
        let tableContent = '';

        if (studentsRows.length === 0) {
            tableContent = '<p>لا يوجد طلاب مسجلين</p>';
        } else {
            // بناء جدول جديد حسب الشكل المطلوب
            tableContent = `
                <table>
                    <thead>
                        <tr>
                            <th>م</th>
                            <th>اسم الطالب</th>
                            ${Array.from({length: 14}, (_, i) => `<th> ${i+1}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.from(studentsRows).map((row, index) => {
                            const studentName = row.querySelector('td:nth-child(3)')?.textContent || '-';
                            return `<tr>
                                <td>${index + 1}</td>
                                <td>${studentName}</td>
                                ${Array.from({length: 14}, () => `<td></td>`).join('')}
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            `;
        }

        tableHtml += `
            <div style="margin-bottom: 30px;">
                <h2 style="text-align: center; font-family: Arial, sans-serif;">${title}</h2>
                <div style="text-align: center; font-family: Arial, sans-serif; margin-bottom: 10px;">${info}</div>
                ${tableContent}
            </div>
        `;
    });

    // نافذة الطباعة
    const newWin = window.open('', '_blank', 'width=900,height=700');
    newWin.document.write(`
        <html>
            <head>
                <title>نموذج حضور وغياب</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
                    h2 { margin-bottom: 5px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                    th { background-color: #f0f0f0; }
                    @media print {
                        body { margin: 0; }
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                        thead { display: table-header-group; }
                        tfoot { display: table-footer-group; }
                    }
                </style>
            </head>
            <body>
                <h1 style="text-align: center;">نموذج حضور وغياب</h1>
                ${tableHtml}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}

</script>

<style>
@media print {
    form, input, button { display: none; }
    body { margin: 0; padding: 0; font-size: 12pt; }
}
</style>
@endsection
