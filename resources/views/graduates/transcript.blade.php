@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="graduatesTranscript()" x-init="selectStudent(selectedNumber)">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">كشف الدرجات</h1>
        <p class="text-gray-600">اختر الطالب الخريج لعرض كشف درجاته مع إمكانية الطباعة أو التصدير.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">الطالب</label>
                <select x-model="selectedNumber" @change="selectStudent(selectedNumber)" class="border rounded px-3 py-2 w-full">
                    <template x-for="student in students" :key="student.number">
                        <option :value="student.number" x-text="student.name + ' — ' + student.number"></option>
                    </template>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="printTranscript">🖨️ طباعة</button>
                <button type="button" class="px-4 py-2 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-8 gap-4 bg-gray-50 border rounded-lg p-4">
            <div>
                <div class="text-sm text-gray-500">اسم الطالب</div>
                <div class="text-lg font-semibold" x-text="currentStudent.name"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">رقم القيد</div>
                <div class="text-lg font-semibold" x-text="currentStudent.number"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">القسم</div>
                <div class="text-lg font-semibold" x-text="currentStudent.department"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">الدفعة</div>
                <div class="text-lg font-semibold" x-text="currentStudent.year"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">الفصل</div>
                <div class="text-lg font-semibold" x-text="currentStudent.semester || '-' "></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">الوحدات الفصلية</div>
                <div class="text-lg font-semibold" x-text="totals.termUnits"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">الوحدات المنجزة</div>
                <div class="text-lg font-semibold" x-text="totals.passedUnits"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500">المعدل الفصلي</div>
                <div class="text-lg font-semibold" x-text="totals.termAvg.toFixed(2)"></div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border" id="transcript-table">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رمز المادة</th>
                        <th class="border px-3 py-2 text-right">اسم المادة</th>
                        <th class="border px-3 py-2 text-right">عدد الوحدات</th>
                        <th class="border px-3 py-2 text-right">الدرجة</th>
                        <th class="border px-3 py-2 text-right">اعادة</th>
                        <th class="border px-3 py-2 text-right">الدور</th>
                        <th class="border px-3 py-2 text-right">SubjectXCredit</th>
                        <th class="border px-3 py-2 text-right">ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="course in currentStudent.courses" :key="course.name">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="course.code || ''"></td>
                            <td class="border px-3 py-2" x-text="course.name"></td>
                            <td class="border px-3 py-2" x-text="course.credits"></td>
                            <td class="border px-3 py-2" x-text="Number(course.grade) || 0"></td>
                            <td class="border px-3 py-2" x-text="course.is_repeat ? 'نعم' : 'لا'"></td>
                            <td class="border px-3 py-2" x-text="course.attempt || 1"></td>
                            <td class="border px-3 py-2" x-text="((Number(course.credits) || 0) * (Number(course.grade) || 0)).toFixed(2)"></td>
                            <td class="border px-3 py-2" x-text="course.note || ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('graduatesTranscript', () => ({
            students: [
                {
                    number: '2024-001',
                    name: 'آمنة علي',
                    department: 'هندسة كهربائية',
                    year: 2024,
                    semester: 'ربيع 2025',
                    courses: [
                        { code: 'EE201', name: 'تحليل دوائر كهربائية', credits: 3, grade: 92, is_repeat: false, attempt: 1, note: '' },
                        { code: 'EE210', name: 'نظم رقمية', credits: 3, grade: 78, is_repeat: false, attempt: 1, note: '' },
                        { code: 'MG105', name: 'إدارة مشاريع', credits: 2, grade: 88, is_repeat: false, attempt: 1, note: '' }
                    ]
                },
                {
                    number: '2024-010',
                    name: 'محمد عمر',
                    department: 'علوم حاسوب',
                    year: 2024,
                    semester: 'خريف 2024',
                    courses: [
                        { code: 'CS201', name: 'هياكل البيانات', credits: 3, grade: 85, is_repeat: false, attempt: 1, note: '' },
                        { code: 'CS220', name: 'قواعد البيانات', credits: 3, grade: 74, is_repeat: true,  attempt: 2, note: 'اعادة' },
                        { code: 'CS340', name: 'ذكاء اصطناعي', credits: 3, grade: 95, is_repeat: false, attempt: 1, note: '' }
                    ]
                }
            ],
            selectedNumber: '2024-001',
            currentStudent: { number: '', name: '', department: '', year: '', semester: '', courses: [] },
            totals: { termUnits: 0, passedUnits: 0, termAvg: 0 },

            selectStudent(number) {
                const found = this.students.find(student => student.number === number);
                if (found) {
                    this.currentStudent = JSON.parse(JSON.stringify(found));
                    this.recalculateTotals();
                }
            },

            recalculateTotals() {
                const totalCredits = this.currentStudent.courses.reduce((s, c) => s + (Number(c.credits) || 0), 0);
                const totalWeighted = this.currentStudent.courses.reduce((s, c) => s + ((Number(c.credits) || 0) * (Number(c.grade) || 0)), 0);
                const passedUnits = this.currentStudent.courses.reduce((s, c) => s + (((Number(c.grade) || 0) >= 50) ? (Number(c.credits) || 0) : 0), 0);
                this.totals.termUnits = totalCredits;
                this.totals.passedUnits = passedUnits;
                this.totals.termAvg = totalCredits ? (totalWeighted / totalCredits) : 0;
            },

            exportCsv() {
                if (!this.currentStudent.courses.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['رمز المادة', 'اسم المادة', 'عدد الوحدات', 'الدرجة', 'اعادة', 'الدور', 'SubjectXCredit', 'ملاحظة'];
                const rows = this.currentStudent.courses.map(course => [
                    course.code || '',
                    course.name,
                    course.credits,
                    (Number(course.grade) || 0),
                    (course.is_repeat ? 'نعم' : 'لا'),
                    (course.attempt || 1),
                    (((Number(course.credits) || 0) * (Number(course.grade) || 0)).toFixed(2)),
                    (course.note || '')
                ]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'transcript-' + this.currentStudent.number + '.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            },

            printTranscript() {
                const list = this.currentStudent.courses || [];
                const htmlRows = list.map(c => (
                    '<tr>' +
                        '<td>' + (c.code || '') + '</td>' +
                        '<td>' + (c.name || '') + '</td>' +
                        '<td>' + (Number(c.credits) || 0) + '</td>' +
                        '<td>' + (Number(c.grade) || 0) + '</td>' +
                        '<td>' + (c.is_repeat ? 'نعم' : 'لا') + '</td>' +
                        '<td>' + (c.attempt || 1) + '</td>' +
                        '<td>' + (((Number(c.credits) || 0) * (Number(c.grade) || 0)).toFixed(2)) + '</td>' +
                        '<td>' + (c.note || '') + '</td>' +
                    '</tr>'
                )).join('');

                const meta = {
                    name: this.currentStudent.name || '',
                    number: this.currentStudent.number || '',
                    dept: this.currentStudent.department || '',
                    sem: this.currentStudent.semester || '',
                    termUnits: this.totals.termUnits || 0,
                    passedUnits: this.totals.passedUnits || 0,
                    termAvg: (this.totals.termAvg || 0).toFixed(2)
                };

                const html = '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>كشف الدرجات</title>'+
                '<style>body{font-family:\'Tahoma\',\'Arial\',sans-serif;direction:rtl;padding:24px;}'+
                'h1{margin:0 0 8px 0;font-size:18px} .meta{margin:8px 0 12px 0;font-size:13px;display:flex;gap:16px;flex-wrap:wrap}'+
                'table{width:100%;border-collapse:collapse;margin-top:8px;}thead{background:#f3f4f6}th,td{border:1px solid #999;padding:6px;text-align:center;font-size:13px;}'+
                '@media print{body{padding:0} .no-print{display:none}}'+
                '</style></head><body>'+
                '<h1>كشف الدرجات</h1>'+
                '<div class="meta">'+
                    '<div>اسم الطالب: '+meta.name+'</div>'+ 
                    '<div>رقم القيد: '+meta.number+'</div>'+ 
                    '<div>القسم: '+meta.dept+'</div>'+ 
                    '<div>الفصل: '+meta.sem+'</div>'+ 
                    '<div>الوحدات الفصلية: '+meta.termUnits+'</div>'+ 
                    '<div>الوحدات المنجزة: '+meta.passedUnits+'</div>'+ 
                    '<div>المعدل الفصلي: '+meta.termAvg+'</div>'+ 
                    '<div>'+ new Date().toLocaleDateString('ar-LY') +'</div>'+ 
                '</div>'+
                '<table><thead><tr>'+
                    '<th>رمز المادة</th>'+
                    '<th>اسم المادة</th>'+
                    '<th>عدد الوحدات</th>'+
                    '<th>الدرجة</th>'+
                    '<th>اعادة</th>'+
                    '<th>الدور</th>'+
                    '<th>SubjectXCredit</th>'+
                    '<th>ملاحظة</th>'+
                '</tr></thead><tbody>'+ htmlRows +'</tbody></table>'+
                '</body></html>';

                const w = window.open('', '_blank', 'width=900,height=650');
                w.document.write(html); w.document.close(); w.focus(); w.print();
            }
        }));
    });
</script>
@endsection
