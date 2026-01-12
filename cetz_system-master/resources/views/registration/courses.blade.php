@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data='courseRegistration({ dataset: @json($registrations), search: @json($query) })' x-init="init()">
    <div class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[180px] bg-white border rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-500">إجمالي التسجيلات</div>
            <div class="text-2xl font-bold" x-text="summary.total"></div>
        </div>
        <div class="flex-1 min-w-[180px] bg-white border rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-500">المقبولة</div>
            <div class="text-2xl font-bold text-green-600" x-text="summary.approved"></div>
        </div>
        <div class="flex-1 min-w-[180px] bg-white border rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-500">قيد المراجعة</div>
            <div class="text-2xl font-bold text-amber-500" x-text="summary.pending"></div>
        </div>
        <div class="flex-1 min-w-[180px] bg-white border rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-500">المرفوضة</div>
            <div class="text-2xl font-bold text-red-500" x-text="summary.rejected"></div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-wrap gap-3 items-end flex-1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-600 mb-1">بحث</label>
                    <input type="text" x-model.trim="search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب أو المادة" class="border rounded px-3 py-2 w-full">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">القسم</label>
                    <select x-model="departmentFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل الأقسام</option>
                        <template x-for="dept in departments" :key="'dept-' + dept">
                            <option :value="dept" x-text="dept"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                    <select x-model="semesterFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل الفصول</option>
                        <template x-for="sem in semesters" :key="'sem-' + sem">
                            <option :value="sem" x-text="sem"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">الحالة</label>
                    <select x-model="statusFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="all">الكل</option>
                        <option value="pending">قيد المراجعة</option>
                        <option value="approved">مقبول</option>
                        <option value="rejected">مرفوض</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="printTable">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="openPrint">⬇️ تصدير excel</button>
                <button type="button" class="h-10 px-4 bg-purple-600 text-white rounded" @click="addRegistration">➕ إضافة</button>
                <button type="button" class="h-10 px-4 bg-gray-100 border rounded" @click="resetFilters">إعادة الضبط</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="3" class="border px-3 py-4 text-center text-gray-500">لا توجد تسجيلات مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.uid">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.student_number"></td>
                            <td class="border px-3 py-2" x-text="row.student"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('courseRegistration', (config) => ({
            raw: config.dataset || [],
            search: config.search || '',
            dataset: [],
            records: [],
            departments: [],
            semesters: [],
            departmentFilter: '',
            semesterFilter: '',
            statusFilter: 'all',
            summary: { total: 0, approved: 0, pending: 0, rejected: 0 },
            nextId: 9000,

            init() {
                const seedStatuses = ['pending', 'approved', 'pending', 'rejected'];
                this.dataset = (this.raw || []).map((item, index) => ({
                    uid: String(item.id !== undefined ? item.id : index) + '-' + index,
                    student_number: item.student_number || ('1091252' + String(1000 + index)),
                    student: item.student_name,
                    department: item.department
                }));
                this.departments = Array.from(new Set(this.dataset.map(item => item.department))).filter(Boolean);
                this.applyFilters();
            },

            applyFilters() {
                const term = this.search.trim();
                this.records = this.dataset.filter(row => {
                    const matchesTerm = !term || [row.student, row.department, row.student_number].some(field => String(field).includes(term));
                    const matchesDept = !this.departmentFilter || row.department === this.departmentFilter;
                    return matchesTerm && matchesDept;
                });
                this.updateSummary();
            },

            updateSummary() {
                const counts = { total: this.records.length, approved: 0, pending: 0, rejected: 0 };
                this.records.forEach(row => {
                    if (row.status === 'approved') counts.approved += 1;
                    if (row.status === 'pending') counts.pending += 1;
                    if (row.status === 'rejected') counts.rejected += 1;
                });
                Object.assign(this.summary, counts);
            },

            toggleStatus(row, status) {
                row.status = status;
                this.updateSummary();
            },

            removeRegistration(row) {
                if (!confirm('هل تريد حذف هذا التسجيل؟')) {
                    return;
                }
                this.dataset = this.dataset.filter(item => item.uid !== row.uid);
                this.applyFilters();
            },

            addRegistration() {
                const student = prompt('اسم الطالب؟');
                const subject = prompt('اسم المادة؟');
                if (!student || !subject) {
                    return;
                }
                const department = this.departments[0] || 'قسم جديد';
                const semester = this.semesters[0] || 'ربيع 2025';
                const record = {
                    uid: 'tmp-' + (++this.nextId),
                    id: this.nextId,
                    student: student,
                    department: department,
                    subject: subject,
                    semester: semester,
                    status: 'pending'
                };
                this.dataset.unshift(record);
                this.applyFilters();
                alert('تمت إضافة تسجيل جديد مؤقتاً.');
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['رقم القيد', 'اسم الطالب', 'القسم'];
                const rows = this.records.map(row => [row.student_number, row.student, row.department]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'course-registrations.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            },

      openPrint() {
    if (!this.records.length) {
        alert('لا توجد بيانات لتصديرها.');
        return;
    }

    // تجهيز البيانات للتصدير إلى Excel
    const data = this.records.map(row => ({
        'رقم القيد': row.student_number,
        'اسم الطالب': row.student,
        'القسم': row.department
    }));

    // إنشاء الورقة من البيانات
    const ws = XLSX.utils.json_to_sheet(data);

    // تنسيق العناوين
    const headerRange = XLSX.utils.decode_range(ws['!ref']);
    for (let C = headerRange.s.c; C <= headerRange.e.c; ++C) {
        const cell_ref = XLSX.utils.encode_cell({ c: C, r: 0 });
        if (!ws[cell_ref]) continue;
        ws[cell_ref].s = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "4F81BD" } },
            alignment: { horizontal: "center" }
        };
    }

    // ضبط عرض الأعمدة
    ws['!cols'] = [
        { wch: 15 }, // رقم القيد
        { wch: 25 }, // اسم الطالب
        { wch: 20 }  // القسم
    ];

    // إنشاء ملف Excel
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "قائمة الطلاب");
    XLSX.writeFile(wb, "students-list.xlsx");
},

    printTable() {
    const tableHtml = document.querySelector('table').outerHTML; // اختر الجدول فقط
    const newWin = window.open('', '_blank', 'width=800,height=600');
    newWin.document.write(`
        <html>
            <head>
                <title>طباعة الجدول</title>
                <style>
                    table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; direction: rtl; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
                    th { background-color: #f0f0f0; }
                </style>
            </head>
            <body>
                ${tableHtml}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
},
            resetFilters() {
                this.search = '';
                this.departmentFilter = '';
                this.semesterFilter = '';
                this.statusFilter = 'all';
                this.applyFilters();
            },

            statusLabel(status) {
                return status === 'approved' ? 'مقبول' : status === 'rejected' ? 'مرفوض' : 'قيد المراجعة';
            },

            statusBadge(status) {
                if (status === 'approved') return 'bg-green-100 text-green-700';
                if (status === 'rejected') return 'bg-red-100 text-red-700';
                return 'bg-amber-100 text-amber-700';
            }
        }));
    });
</script>
@endsection
