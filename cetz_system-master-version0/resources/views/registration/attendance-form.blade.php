@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="attendanceForm(@json($classes))" x-init="selectClass(selectedClassId)">

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">نموذج الحضور والغياب</h1>
        <p class="text-gray-600">اختر المجموعة الدراسية ثم حدّث حالات الطلبة، يمكنك الطباعة أو التصدير كملف CSV.</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

              <div>
           <label class="block text-sm text-gray-600 mb-1">السيمستر</label>

<select x-model="selectedSemester" @change="onSemesterChange()"name="semester_id" class="border rounded px-3 py-2 w-full">
 @change="onStudentChange()"name="semester_id" class="border rounded px-3 py-2 w-full">
    <option value="">اختر السيمستر</option>

    @foreach($semesters as $semester)
        <option value="{{ $semester->id }}">
            {{ $semester->name }}
        </option>
    @endforeach
</select>


            </div>
            
            <div>
                <label class="block text-sm text-gray-600 mb-1">المجموعة الدراسية</label>
            
           <select class="border rounded px-3 py-2 w-full">
    <option value="">اختر المادة / السيكشن</option>

    @foreach($classes as $assignment)
        <option value="{{ $assignment->courseOffering?->id }}">
            {{ $assignment->courseOffering?->course?->name }}
            — {{ $assignment->courseOffering?->section?->name }}
        </option>
    @endforeach
</select>


            </div>
    
            <div>
                <label class="block text-sm text-gray-600 mb-1">التاريخ</label>
                <input type="date" x-model="currentClass.date" class="border rounded px-3 py-2 w-full">
            </div>
            
            <div>
                <label class="block text-sm text-gray-600 mb-1">المحاضر</label>
<select name="teacher_id" class="border rounded px-3 py-2 w-full">
@foreach($classes as $assignment)
    <option value="{{ $assignment->teacher?->id }}">
        {{ $assignment->teacher->full_name }}
    </option>
@endforeach

</select>
            </div>

   

            <div class="flex items-end gap-2">
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded" @click="printTable">🖨️ طباعة</button>
                <button type="button" class="px-4 py-2 bg-green-600 text-white rounded" @click="exportExcel">⬇️ تصدير excel</button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="px-4 py-2 bg-green-600 text-white rounded" @click="setAll('حاضر')">تحديد الجميع حاضر</button>
            <button type="button" class="px-4 py-2 bg-yellow-500 text-white rounded" @click="setAll('غائب بعذر')">تحديد الجميع غائب بعذر</button>
            <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="resetStatuses">إعادة الضبط</button>
            <div class="ms-auto flex items-center gap-2">
                <span class="text-sm text-gray-600">نموذج الطباعة (قديم):</span>
                <select x-model="meta.year" class="border rounded px-2 py-1 text-sm">
                    <template x-for="y in meta.years" :key="y"><option :value="y" x-text="y"></option></template>
                </select>
                <select x-model="meta.term" class="border rounded px-2 py-1 text-sm">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
                <select x-model="meta.department" class="border rounded px-2 py-1 text-sm">
                    <template x-for="d in meta.departments" :key="d"><option :value="d" x-text="d"></option></template>
                </select>
                <select x-model="meta.group" class="border rounded px-2 py-1 text-sm">
                    <template x-for="g in meta.groups" :key="g"><option :value="g" x-text="g"></option></template>
                </select>
                <select x-model="meta.subject" class="border rounded px-2 py-1 text-sm">
                    <template x-for="s in meta.subjects" :key="s"><option :value="s" x-text="s"></option></template>
                </select>
                <button type="button" class="px-3 py-2 bg-indigo-600 text-white rounded" @click="printOldSheet">طباعة</button>
            </div>
        </div>

      
          <div class="overflow-x-auto">
    <table class="min-w-full text-sm border" id="attendance-table">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2 text-right">رقم الطالب</th>
                <th class="border px-3 py-2 text-right">اسم الطالب</th>
                <th class="border px-3 py-2 text-right">الحالة</th>
                <th class="border px-3 py-2 text-right">ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enrollments as $enrollment)
                <tr>
                    <td class="border px-3 py-2 text-right">{{ $enrollment->student->student_number ?? $enrollment->student->manual_number }}</td>
                    <td class="border px-3 py-2 text-right">{{ $enrollment->student->full_name ?? '-' }}</td>
                      <td class="border px-3 py-2">
                                <select class="border rounded px-2 py-1 w-full" x-model="student.status">
                                    <template x-for="option in statusOptions" :key="student.number + '-' + option">
                                        <option x-text="option" :value="option"></option>
                                    </template>
                                </select>
                            </td>
                    <td class="border px-3 py-2">
                        <input type="text" name="note[{{ $enrollment->id }}]" class="border rounded px-2 py-1 w-full" value="{{ $enrollment->note ?? '' }}" placeholder="اكتب ملاحظة عند الحاجة">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border px-3 py-4 text-center text-gray-500">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
    </div>
</div>

</div>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('attendanceForm', (classes, enrollments) => ({
        classes: classes,
        enrollments: enrollments,
        selectedClassId: null,
        currentClass: { id: null, students: [] },
        statusOptions: ['حاضر', 'غائب', 'غائب بعذر', 'متأخر'],

        selectClass(id) {
            const found = this.classes.find(c => c.id == id);
            if (!found) {
                this.currentClass = { id: null, students: [] };
                return;
            }

            // جلب الطلاب المرتبطين بهذا الـ course_offering
            const students = this.enrollments
                .filter(e => e.course_offering_id == found.courseOffering.id)
                .map(e => ({
                    id: e.student.id,
                    number: e.student.student_number,
                    name: e.student.full_name,
                    status: 'حاضر', // القيمة الافتراضية
                    note: ''
                }));

            this.currentClass = {
                id: found.id,
                students: students
            };
        },
        setAll(status) {
            this.currentClass.students.forEach(s => s.status = status);
        },

        resetStatuses() {
            this.selectClass(this.selectedClassId);
        },
            exportCsv() {
                if (!this.currentClass.students.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['رقم الطالب', 'اسم الطالب', 'الحالة', 'ملاحظات'];
                const rows = this.currentClass.students.map(student => [student.number, student.name, student.status, student.note || '']);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'attendance-' + this.selectedClassId + '.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            },

  

    exportExcel() {
    if (!this.currentClass.students.length) {
        alert('لا توجد بيانات لتصديرها.');
        return;
    }

    const data = this.currentClass.students.map(s => ({
        'رقم الطالب': s.number,
        'اسم الطالب': s.name,
        'الحالة': s.status,
        'ملاحظات': s.note || ''
    }));

    const ws = XLSX.utils.json_to_sheet(data);

    ws['!cols'] = [
        { wch: 12 },
        { wch: 20 },
        { wch: 15 },
        { wch: 25 }
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "الحضور");
    XLSX.writeFile(wb, "attendance.xlsx");
},
selectClass(id) {
    const found = this.classes.find(c => c.id === id);
    if (!found) return;

    const students = this.enrollments
        .filter(e => e.course_offering_id === found.course_offering.id)
        .map(e => ({
            number: e.student.student_number,
            name: e.student.full_name,
            status: 'حاضر', // القيمة الافتراضية
            note: ''
        }));

    this.currentClass = {
        id: found.id,
        name: found.course_offering.course.name,
        date: found.course_offering.date || '',
        instructor: found.teacher.full_name,
        students: students
    };
},

        printTable() {
    // انشاء نسخة من الطلاب لكن مع فراغ الحالة والملاحظة
    const studentsForPrint = this.currentClass.students.map(student => ({
        number: student.number,
        name: student.name,
        status: '',  // فارغ
        note: ''     // فارغ
    }));

    // بناء جدول HTML
    let tableHtml = `
        <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">رقم الطالب</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">اسم الطالب</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">الحالة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                ${studentsForPrint.map(s => `
                    <tr>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${s.number}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${s.name}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${s.status}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${s.note}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    const today = new Date().toLocaleDateString('ar-EG');
    const newWin = window.open('', '_blank', 'width=900,height=700');
    newWin.document.write(`
        <html>
            <head>
                <title>ورقة حضور الطلاب</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
                    h2, h3 { text-align: center; margin: 5px 0; }
                    .footer { margin-top: 30px; display: flex; justify-content: space-between; }
                    .footer div { text-align: center; }
                    .signature { margin-top: 50px; border-top: 1px solid #000; width: 200px; text-align: center; }
                </style>
            </head>
            <body>
                <h2>ورقة حضور وغياب الطلاب</h2>
                <h3>القسم: ${this.currentClass.department || '---'} | المادة: ${this.currentClass.course || '---'}</h3>
                <p>التاريخ: ${this.currentClass.date} | تاريخ الطباعة: ${today}</p>
                
                ${tableHtml}

                <div class="footer">
                    <div>
                        <div class="signature">توقيع الأستاذ: ${this.currentClass.instructor}</div>
                    </div>
                    <div>
                        <p>عدد الطلاب: ${this.currentClass.students.length}</p>
                    </div>
                </div>
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
},

    exportExcel() {
    if (!this.currentClass.students.length) {
        alert('لا توجد بيانات لتصديرها.');
        return;
    }

    const data = this.currentClass.students.map(s => ({
        'رقم الطالب': s.number,
        'اسم الطالب': s.name,
        'الحالة': s.status,
        'ملاحظات': s.note || ''
    }));

    const ws = XLSX.utils.json_to_sheet(data);

    ws['!cols'] = [
        { wch: 12 },
        { wch: 20 },
        { wch: 15 },
        { wch: 25 }
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "الحضور");
    XLSX.writeFile(wb, "attendance.xlsx");
},
            printOldSheet() {
                const days = Array.from({length: 30}, (_,i)=> i+1);
                const head = days.map(d=>'<th>'+d+'</th>').join('');
                const rows = this.currentClass.students.map((s,i)=>'<tr>'+
                    '<td>'+(i+1)+'</td>'+
                    '<td>'+s.number+'</td>'+
                    '<td class="text-right">'+s.name+'</td>'+
                    days.map(()=>'<td>&nbsp;</td>').join('')+
                '</tr>').join('');
                const m=this.meta;
                const metaTbl = '<table style="width:100%;border-collapse:collapse;margin-bottom:8px" dir="rtl">'
                    +'<tr><td>السنة: '+m.year+'</td><td>الفصل: '+m.term+'</td><td>القسم: '+m.department+'</td></tr>'
                    +'<tr><td>الشعبة: '+m.group+'</td><td>المادة: '+m.subject+'</td><td>'+new Date().toLocaleDateString('ar-LY')+'</td></tr>'
                    +'</table>';
                const html = '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>كشف الحضور والغياب</title>'+
                    '<style>body{font-family:\'Tahoma\',\'Arial\',sans-serif;direction:rtl;padding:16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #999;padding:4px;text-align:center;font-size:12px}thead{background:#f3f4f6} .text-right{text-align:right}</style>'+
                    '</head><body><h3 style="margin:0 0 8px">كشف الحضور والغياب</h3>'+metaTbl+
                    '<table><thead><tr><th>#</th><th>رقم القيد</th><th class="text-right">اسم الطالب</th>'+head+'</tr></thead><tbody>'+rows+'</tbody></table></body></html>';
                const w=window.open('', '_blank', 'width=900,height=650');
                w.document.write(html); w.document.close(); w.focus(); w.print();
            }
        }));
    });
</script>
@endsection
