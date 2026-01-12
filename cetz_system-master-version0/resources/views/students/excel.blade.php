@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="studentsList(@js($studentsForJs), @js($years), @js($departments))"
     x-init="applyFilters()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">كشف الطلبة</h1>
        <p class="text-gray-600">عرض بيانات جميع الطلبة مع إمكانية التحكم بالخانات والفلاتر</p>

        <!-- فلاتر -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-sm text-gray-600 mb-1">الاسم الكامل</label>
                <input type="text" x-model.trim="filters.full_name" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بالاسم">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الرقم الجامعي</label>
                <input type="text" x-model.trim="filters.student_number" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="مثلاً 2025-001">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الرقم اليدوي</label>
                <input type="text" x-model.trim="filters.manual_number" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بالرقم اليدوي">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الرقم الوطني</label>
                <input type="text" x-model.trim="filters.national_id" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بالرقم الوطني">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">اسم الأم</label>
                <input type="text" x-model.trim="filters.mother_name" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث باسم الأم">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الجنسية</label>
                <input type="text" x-model.trim="filters.nationality" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بالجنسية">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم جواز السفر</label>
                <input type="text" x-model.trim="filters.passport_number" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث برقم الجواز">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">تاريخ الميلاد</label>
                <input type="date" x-model="filters.birth_date" @change="applyFilters" class="border rounded px-3 py-2 w-full">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الجنس</label>
                <select x-model="filters.gender" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">الكل</option>
                    <option value="ذكر">ذكر</option>
                    <option value="أنثى">أنثى</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">سنة التسجيل</label>
                <input list="yearsList" x-model="filters.year" @input="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="اختر أو اكتب السنة">
                <datalist id="yearsList">
                    <template x-for="year in years" :key="year">
                        <option :value="year" x-text="year"></option>
                    </template>
                </datalist>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">الفصل الدراسي</label>
                <input type="text" x-model.trim="filters.semester" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="مثلاً: الأول">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">اسم المصرف</label>
                <input type="text" x-model.trim="filters.bank_name" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بالمصرف">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم الحساب المصرفي</label>
                <input type="text" x-model.trim="filters.bank_account" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث برقم الحساب">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">قيد الكتيب</label>
                <input type="text" x-model.trim="filters.family_record" @input.debounce.300="applyFilters" class="border rounded px-3 py-2 w-full" placeholder="بحث بقيد الكتيب">
            </div>
        </div>

        <!-- أزرار -->
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="resetFilters">♻️ إعادة تعيين</button>
            <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportExcel">⬇️ تصدير Excel</button>
            <button type="button" class="h-10 px-4 bg-blue-600 text-white rounded" @click="printTable">🖨️ طباعة</button>
        </div>

        <!-- التحكم في الأعمدة -->
        <div class="bg-gray-50 p-3 rounded border mt-4">
            <h2 class="font-semibold mb-2">عرض الأعمدة:</h2>
            <div class="flex flex-wrap gap-3 text-sm">
                <template x-for="(label, key) in columns" :key="key">
                    <label class="flex items-center gap-1">
                        <input type="checkbox" x-model="visibleColumns" :value="key" class="rounded">
                        <span x-text="label"></span>
                    </label>
                </template>
            </div>
        </div>

        <!-- الجدول -->
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <template x-for="(label, key) in columns" :key="'head-' + key">
                            <th x-show="visibleColumns.includes(key)" class="border px-3 py-2 text-right" x-text="label"></th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="100%" class="border px-3 py-4 text-center text-gray-500">لا يوجد طلبة مطابقون للبحث.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.id">
                        <tr class="hover:bg-gray-50">
                            <template x-for="(label, key) in columns" :key="'cell-' + key">
                                <td x-show="visibleColumns.includes(key)" class="border px-3 py-2" x-text="row[key] ?? '—'"></td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentsList', (datasetFromDb, yearsFromDb, departmentsFromDb) => ({
        dataset: datasetFromDb,
        years: yearsFromDb,
        departments: departmentsFromDb,

        columns: {
            photo: 'الصورة الشخصية',
            full_name: 'الاسم الكامل',
            mother_name: 'اسم الأم',
            nationality: 'الجنسية',
            gender: 'الجنس',
            year: 'سنة التسجيل',
            semester: 'الفصل الدراسي',
            student_number: 'الرقم الجامعي',
            manual_number: 'الرقم اليدوي',
            national_id: 'الرقم الوطني',
            passport_number: 'رقم جواز السفر',
            bank_name: 'اسم المصرف',
            bank_account: 'رقم الحساب المصرفي',
            birth_date: 'تاريخ الميلاد',
            family_record: 'قيد الكتيب',
            department: 'القسم'
        },

        visibleColumns: [
            'full_name', 'mother_name', 'year', 'student_number', 'bank_account'
        ],

        filters: {
            full_name: '',
            student_number: '',
            manual_number: '',
            national_id: '',
            mother_name: '',
            nationality: '',
            passport_number: '',
            birth_date: '',
            gender: '',
            year: '',
            semester: '',
            bank_name: '',
            bank_account: '',
            family_record: '',
            department: ''
        },

        records: [],

        resetFilters() {
            Object.keys(this.filters).forEach(key => this.filters[key] = '');
            this.applyFilters();
        },

        applyFilters() {
            this.records = this.dataset.filter(row => {
                return Object.keys(this.filters).every(key => {
                    if (!this.filters[key]) return true;
                    return row[key]?.toString().toLowerCase().includes(this.filters[key].toString().toLowerCase());
                });
            });
        },

        exportExcel() {
            if (!this.records.length) return alert('لا توجد بيانات لتصديرها.');

            const data = this.records.map(row => {
                let obj = {};
                this.visibleColumns.forEach(col => obj[this.columns[col]] = row[col] ?? '—');
                return obj;
            });

            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Students");
            XLSX.writeFile(wb, "students-list.xlsx");
        },

        printTable() {
            const tableHtml = document.querySelector('table').outerHTML;
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
                    <body>${tableHtml}</body>
                </html>
            `);
            newWin.document.close();
            newWin.focus();
            newWin.print();
            newWin.close();
        }
    }));
});
</script>
@endsection
