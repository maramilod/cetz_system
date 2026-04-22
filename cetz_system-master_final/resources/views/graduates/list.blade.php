@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="graduatesList()" x-init="applyFilters()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">كشف الخريجين</h1>
        <p class="text-gray-600">ابحث واطبع أو صدّر كشف الخريجين بالحقول المطلوبة.</p>

        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="search" @input.debounce.300="applyFilters" placeholder="ابحث بالاسم أو رقم القيد أو المنطقة" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="printTable">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
                   <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportExcel">⬇️ تصدير excel</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">الاسم</th>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">المعدل التراكمي</th>
                        <th class="border px-3 py-2 text-right">ملاحظة</th>
                        <th class="border px-3 py-2 text-right">اسم الأم</th>
                        <th class="border px-3 py-2 text-right">FBNNUMBER</th>
                        <th class="border px-3 py-2 text-right">اسم المنطقة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="7" class="border px-3 py-4 text-center text-gray-500">لا يوجد خريجون مطابقون للبحث.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.number + row.name">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="Number(row.gpa).toFixed(2)"></td>
                            <td class="border px-3 py-2" x-text="row.note || ''"></td>
                            <td class="border px-3 py-2" x-text="row.mother_name || ''"></td>
                            <td class="border px-3 py-2" x-text="row.fbn_number || ''"></td>
                            <td class="border px-3 py-2" x-text="row.region_name || ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/xlsx.full.js') }}"></script><script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('graduatesList', () => ({
            dataset: [
                { name: 'آمنة علي', number: '2024-001', gpa: 3.85, note: '', mother_name: 'فاطمة أحمد', fbn_number: 'FBN-10001', region_name: 'طرابلس' },
                { name: 'محمد عمر', number: '2024-010', gpa: 3.45, note: 'مكمل إجراء', mother_name: 'ليلى حسين', fbn_number: 'FBN-10045', region_name: 'بنغازي' },
                { name: 'سارة محمود', number: '2023-022', gpa: 3.92, note: '', mother_name: 'مريم يوسف', fbn_number: 'FBN-09981', region_name: 'مصراتة' },
                { name: 'ليث الصادق', number: '2022-115', gpa: 3.55, note: '', mother_name: 'خديجة سالم', fbn_number: 'FBN-08123', region_name: 'سبها' }
            ],
            records: [],
            search: '',

            applyFilters() {
                const term = this.search.trim();
                this.records = this.dataset.filter(row => {
                    const hay = (row.name + ' ' + row.number + ' ' + (row.region_name || '')).toLowerCase();
                    return !term || hay.includes(term.toLowerCase());
                });
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['الاسم', 'رقم القيد', 'المعدل التراكمي', 'ملاحظة', 'اسم الأم', 'FBNNUMBER', 'اسم المنطقة'];
                const rows = this.records.map(row => [row.name, row.number, Number(row.gpa).toFixed(2), row.note || '', row.mother_name || '', row.fbn_number || '', row.region_name || '']);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'graduates-list.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            },

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
                    <th style="border:1px solid #000; padding:8px; text-align:right;">الاسم</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">رقم القيد</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">المعدل التراكمي</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">ملاحظة</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">اسم الأم</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">FBNNUMBER</th>
                    <th style="border:1px solid #000; padding:8px; text-align:right;">اسم المنطقة</th>
                </tr>
            </thead>
            <tbody>
                ${this.records.map(r => `
                    <tr>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.name}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.number}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${Number(r.gpa).toFixed(2)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.note || ''}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.mother_name || ''}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.fbn_number || ''}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${r.region_name || ''}</td>
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
                <title>طباعة كشف الخريجين</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: right; }
                    th { background-color: #f0f0f0; }
                </style>
            </head>
            <body>
                <h2 style="text-align:center;">كشف الخريجين</h2>
                <p>تاريخ الطباعة: ${today}</p>
                ${tableHtml}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
},

exportExcel() {
    if (!this.records.length) {
        alert('لا توجد بيانات لتصديرها.');
        return;
    }

    // إعداد البيانات من records
    const data = this.records.map(r => ({
        'الاسم': r.name,
        'رقم القيد': r.number,
        'المعدل التراكمي': Number(r.gpa).toFixed(2),
        'ملاحظة': r.note || '',
        'اسم الأم': r.mother_name || '',
        'FBNNUMBER': r.fbn_number || '',
        'اسم المنطقة': r.region_name || ''
    }));

    // تحويل البيانات إلى ورقة Excel
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "الخريجين");

    // حفظ الملف
    XLSX.writeFile(wb, `graduates-list.xlsx`);
}

        }));
    });
</script>
@endsection
