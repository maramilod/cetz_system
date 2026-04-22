@extends('layouts.app')

@section('content')
@php
    $hasFilters = collect($filters)->contains(fn ($value) => $value !== '');
@endphp

<div class="space-y-6">
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-l from-amber-50 via-white to-sky-50 shadow-sm">
        <div class="grid gap-6 px-6 py-8 lg:grid-cols-[1.2fr_.8fr] lg:px-8">
            <div class="space-y-3 text-right">
                <span class="inline-flex w-fit items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                    كشف الطلبة الأكاديمي
                </span>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">البحث حسب الفصل والسنة والقسم والسيمستر</h1>
                <p class="max-w-3xl text-sm leading-7 text-slate-600">
                    التقرير يعرض الطلبة المسجلين في الفصل المحدد، مع رقم القيد والقسم والمعدل الفصلي والمعدل التراكمي
                    بناءً على الدرجات المخزنة فعلياً في النظام.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-2xl bg-white/80 p-4 ring-1 ring-slate-200">
                    <div class="text-sm text-slate-500">عدد الطلبة</div>
                    <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($studentsCount) }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 p-4 ring-1 ring-slate-200">
                    <div class="text-sm text-slate-500">متوسط المعدل الفصلي</div>
                    <div class="mt-2 text-3xl font-black text-sky-700">{{ $averageTermGpa !== null ? number_format($averageTermGpa, 2) : '-' }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 p-4 ring-1 ring-slate-200">
                    <div class="text-sm text-slate-500">متوسط المعدل التراكمي</div>
                    <div class="mt-2 text-3xl font-black text-emerald-700">{{ $averageCumulativeGpa !== null ? number_format($averageCumulativeGpa, 2) : '-' }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="GET" action="{{ route('students.rank') }}" class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="term_type" class="mb-2 block text-sm font-medium text-slate-700">الفصل الدراسي</label>
                    <select id="term_type" name="term_type" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500">
                        <option value="">الكل</option>
                        @foreach($termTypes as $termType)
                            <option value="{{ $termType }}" @selected($filters['term_type'] === $termType)>{{ $termType }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="mb-2 block text-sm font-medium text-slate-700">السنة</label>
                    <select id="year" name="year" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500">
                        <option value="">الكل</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected($filters['year'] === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="department_id" class="mb-2 block text-sm font-medium text-slate-700">القسم</label>
                    <select id="department_id" name="department_id" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500">
                        <option value="">الكل</option>
                        @foreach($departments as $department)
                            <option
                                value="{{ $department->id }}"
                                data-is-general="{{ $department->is_general ? '1' : '0' }}"
                                @selected($filters['department_id'] === (string) $department->id)
                            >
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="semester_number" class="mb-2 block text-sm font-medium text-slate-700">السيمستر</label>
                    <select id="semester_number" name="semester_number" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500">
                        <option value="">الكل</option>
                        @foreach($semesterNumbers as $semesterNumber)
                            <option
                                value="{{ $semesterNumber }}"
                                data-semester-number="{{ $semesterNumber }}"
                                @selected($filters['semester_number'] === (string) $semesterNumber)
                            >
                                سيمستر {{ $semesterNumber }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('students.rank') }}" class="inline-flex items-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                    إعادة التعيين
                </a>
                <button type="button" id="print-report" class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    طباعة
                </button>
                <button type="button" id="export-report" class="inline-flex items-center rounded-2xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-400">
                    تصدير Excel
                </button>
                <button type="submit" class="inline-flex items-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
                    عرض الكشف
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-[28px] bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5">
            <div class="text-right">
                <h2 class="text-xl font-black text-slate-900">نتائج الكشف</h2>
                <p class="mt-1 text-sm text-slate-500">
                    @if($hasFilters)
                        تم ترتيب النتائج حسب المعدل الفصلي من الأعلى إلى الأقل.
                    @else
                        اختر الفصل الدراسي أو السنة أو القسم أو السيمستر ثم اضغط "عرض الكشف".
                    @endif
                </p>
            </div>

            @if($hasFilters)
                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
                    {{ number_format($studentsCount) }} طالب
                </div>
            @endif
        </div>

        <div id="report-table-wrapper" class="overflow-x-auto p-6">
            <table id="report-table" class="min-w-full border-separate border-spacing-0 text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">م</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">رقم القيد</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">اسم الطالب</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">القسم</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">النطاق الدراسي</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">عدد المواد</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">المعدل الفصلي</th>
                        <th class="sticky top-0 px-4 py-3 text-right font-semibold">المعدل التراكمي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportRows as $index => $row)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} transition hover:bg-sky-50">
                            <td class="border-b border-slate-200 px-4 py-3 text-right">{{ $index + 1 }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right font-semibold text-slate-800">{{ $row['student_number'] }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right">{{ $row['full_name'] }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right">{{ $row['department_name'] }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right text-slate-600">{{ $row['matched_semesters'] }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right">{{ $row['courses_count'] }}</td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right">
                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 font-semibold text-sky-800">
                                    {{ $row['term_average'] !== null ? number_format($row['term_average'], 2) : '-' }}
                                </span>
                            </td>
                            <td class="border-b border-slate-200 px-4 py-3 text-right">
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-800">
                                    {{ $row['cumulative_average'] !== null ? number_format($row['cumulative_average'], 2) : '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-sm text-slate-500">
                                @if($hasFilters)
                                    لا توجد نتائج مطابقة للبحث الحالي.
                                @else
                                    لم يتم تنفيذ البحث بعد.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('report-table');
    const exportButton = document.getElementById('export-report');
    const printButton = document.getElementById('print-report');
    const departmentSelect = document.getElementById('department_id');
    const semesterSelect = document.getElementById('semester_number');

    function syncSemesterOptions() {
        if (!departmentSelect || !semesterSelect) {
            return;
        }

        const selectedDepartment = departmentSelect.options[departmentSelect.selectedIndex];
        const isGeneral = selectedDepartment?.dataset?.isGeneral === '1';
        const hasDepartment = departmentSelect.value !== '';

        Array.from(semesterSelect.options).forEach(option => {
            const semesterNumber = option.dataset.semesterNumber;

            if (!semesterNumber) {
                option.hidden = false;
                return;
            }

            if (isGeneral) {
                option.hidden = semesterNumber !== '1';
                return;
            }

            if (hasDepartment) {
                option.hidden = semesterNumber === '1';
                return;
            }

            option.hidden = false;
        });

        const visibleSelected = semesterSelect.selectedOptions[0] && !semesterSelect.selectedOptions[0].hidden;

        if (isGeneral && semesterSelect.value !== '1') {
            semesterSelect.value = '1';
            return;
        }

        if (hasDepartment && !isGeneral && semesterSelect.value === '1') {
            semesterSelect.value = '2';
            return;
        }

        if (!visibleSelected) {
            const firstVisible = Array.from(semesterSelect.options).find(option => !option.hidden);
            semesterSelect.value = firstVisible ? firstVisible.value : '';
        }
    }

    function hasDataRows() {
        return table.querySelectorAll('tbody tr').length > 0
            && table.querySelector('tbody tr td[colspan]') === null;
    }

    syncSemesterOptions();
    departmentSelect?.addEventListener('change', syncSemesterOptions);

    exportButton?.addEventListener('click', () => {
        if (!hasDataRows()) {
            window.alert('لا توجد بيانات لتصديرها.');
            return;
        }

        const workbook = XLSX.utils.table_to_book(table, { sheet: 'كشف الطلبة' });
        XLSX.writeFile(workbook, 'student-academic-report.xlsx');
    });

    printButton?.addEventListener('click', () => {
        if (!hasDataRows()) {
            window.alert('لا توجد بيانات للطباعة.');
            return;
        }

        const win = window.open('', '_blank', 'width=1200,height=800');
        const styles = `
            <style>
                body { font-family: Tahoma, Arial, sans-serif; direction: rtl; padding: 24px; color: #0f172a; }
                h1 { margin-bottom: 8px; }
                p { margin-top: 0; color: #475569; }
                table { width: 100%; border-collapse: collapse; margin-top: 24px; }
                th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: right; }
                th { background: #0f172a; color: white; }
                tr:nth-child(even) td { background: #f8fafc; }
            </style>
        `;

        win.document.write(`
            <html lang="ar" dir="rtl">
                <head>
                    <title>كشف الطلبة</title>
                    ${styles}
                </head>
                <body>
                    <h1>كشف الطلبة الأكاديمي</h1>
                    <p>تقرير بحسب الفصل الدراسي والسنة والقسم والسيمستر</p>
                    ${table.outerHTML}
                </body>
            </html>
        `);
        win.document.close();
        win.focus();
        win.print();
        win.close();
    });
});
</script>
@endsection
