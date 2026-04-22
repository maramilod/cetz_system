@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="studySemesterResults()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    <template x-for="sem in filters.semesters" :key="sem">
                        <option :value="sem" x-text="sem"></option>
                    </template>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="printTable">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportExcel">⬇️ تصدير excel</button>
                <button type="button" class="h-10 px-4 bg-gray-100 border rounded" @click="resetFilters">إعادة الضبط</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رمز المادة</th>
                        <th class="border px-3 py-2 text-right">اسم المادة</th>
                        <th class="border px-3 py-2 text-right">عدد الوحدات</th>
                        <th class="border px-3 py-2 text-right">الدرجة</th>
                        <th class="border px-3 py-2 text-right">تقييم الدرجة</th>
                        <th class="border px-3 py-2 text-right">ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="6" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.id">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.code"></td>
                            <td class="border px-3 py-2" x-text="row.subject"></td>
                            <td class="border px-3 py-2" x-text="row.units"></td>
                            <td class="border px-3 py-2" x-text="row.mark"></td>
                            <td class="border px-3 py-2" x-text="row.evaluation"></td>
                            <td class="border px-3 py-2" x-text="row.note || ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/xlsx.full.min.js') }}"></script><script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('studySemesterResults', () => ({
            dataset: [
                { id: 1, student: 'آمنة علي',   semester: 'ربيع 2025', code: 'MTH101', subject: 'رياضيات 1',   units: 3, mark: 95, note: '' },
                { id: 2, student: 'محمد عمر',   semester: 'خريف 2024', code: 'CS101',  subject: 'برمجة 1',     units: 4, mark: 87, note: '' },
                { id: 3, student: 'سارة محمود', semester: 'ربيع 2025', code: 'ME102',  subject: 'ميكانيكا',    units: 3, mark: 59, note: 'إعادة' },
                { id: 4, student: 'ليث الصادق', semester: 'خريف 2024', code: 'EE120',  subject: 'دوائر كهربائية', units: 3, mark: 72, note: '' }
            ],
            records: [],
            filters: { search: '', semester: '', semesters: [] },

            init() {
                this.filters.semesters = Array.from(new Set(this.dataset.map(i => i.semester)));
                this.applyFilters();
            },

            applyFilters() {
                const term = this.filters.search.trim();
                this.records = this.dataset
                    .filter(r => (!term || String(r.student).includes(term))
                              && (!this.filters.semester || r.semester === this.filters.semester))
                    .map(r => ({ ...r, evaluation: this.evaluate(r.mark) }));
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.semester = '';
                this.applyFilters();
            },

            evaluate(mark) {
                const m = Number(mark) || 0;
                if (m >= 85) return 'ممتاز';
                if (m >= 75) return 'جيد جداً';
                if (m >= 65) return 'جيد';
                if (m >= 50) return 'مقبول';
                return 'ضعيف';
            },
   // دالة طباعة الجدول كـ PDF أو صورة
printTable() {
    if (!this.records.length) {
        alert('لا توجد بيانات للطباعة.');
        return;
    }

    // إنشاء جدول HTML من البيانات
    let tableHtml = `
        <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">رمز المادة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">اسم المادة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">عدد الوحدات</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">الدرجة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">تقييم الدرجة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">ملاحظة</th>
                </tr>
            </thead>
            <tbody>
                ${this.records.map(r => `
                    <tr>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.code}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.subject}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.units}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.mark}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.evaluation}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.note || ''}</td>
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
                <title>طباعة الدرجات</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: right; }
                    th { background-color: #f0f0f0; }
                </style>
            </head>
            <body>
                <h2 style="text-align:center;">نتائج الطلاب</h2>
                <p>تاريخ الطباعة: ${today}</p>
                ${tableHtml}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}
,

// دالة تصدير الجدول إلى Excel
exportExcel() {
    if (!this.records.length) {
        alert('لا توجد بيانات لتصديرها.');
        return;
    }

    const data = this.records.map(r => ({
        'رمز المادة': r.code,
        'اسم المادة': r.subject,
        'عدد الوحدات': r.units,
        'الدرجة': r.mark,
        'تقييم الدرجة': this.evaluate(r.mark),
        'ملاحظة': r.note || ''
    }));

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Grades");
    XLSX.writeFile(wb, "grades.xlsx");
}

        }));
    });
</script>
@endsection
