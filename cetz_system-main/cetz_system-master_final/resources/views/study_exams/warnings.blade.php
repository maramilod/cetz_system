@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="warningsManager()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">نوع الإنذار</label>
                <select x-model="filters.type" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأنواع</option>
                    <template x-for="type in types" :key="type">
                        <option :value="type" x-text="type"></option>
                    </template>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="filters.department" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    <template x-for="dept in departments" :key="dept">
                        <option :value="dept" x-text="dept"></option>
                    </template>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    <template x-for="sem in semesters" :key="sem">
                        <option :value="sem" x-text="sem"></option>
                    </template>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب أو رقم القيد" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="window.print()">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">نوع الإنذار</th>
                        <th class="border px-3 py-2 text-right">الفصل</th>
                        <th class="border px-3 py-2 text-right">التاريخ</th>
                        <th class="border px-3 py-2 text-right">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="7" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.number + row.date">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.student"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.type"></td>
                            <td class="border px-3 py-2" x-text="row.semester"></td>
                            <td class="border px-3 py-2" x-text="row.date"></td>
                            <td class="border px-3 py-2">
                                <span class="px-2 py-1 rounded" :class="row.status === 'active' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'" x-text="row.status === 'active' ? 'ساري' : 'مغلق'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('warningsManager', () => ({
            dataset: [
                { student: 'آمنة علي',   number: '2025-001', department: 'هندسة كهربائية', semester: 'ربيعي 2025', type: 'غياب متكرر', date: '2025-01-04', status: 'active' },
                { student: 'محمد عمر',   number: '2025-010', department: 'علوم حاسوب',     semester: 'ربيعي 2025', type: 'سلوك',       date: '2025-01-08', status: 'closed' },
                { student: 'سارة محمود', number: '2024-075', department: 'هندسة ميكانيك',  semester: 'خريفي 2024', type: 'تأخير',      date: '2024-12-30', status: 'active' }
            ],
            records: [],
            filters: { type: '', department: '', semester: '', search: '' },
            types: ['غياب متكرر', 'سلوك', 'تأخير'],
            departments: [],
            semesters: [],

            init() {
                this.departments = Array.from(new Set(this.dataset.map(r => r.department))).filter(Boolean);
                this.semesters = Array.from(new Set(this.dataset.map(r => r.semester))).filter(Boolean);
                this.applyFilters();
            },

            applyFilters() {
                const term = this.filters.search.trim().toLowerCase();
                this.records = this.dataset.filter(row => {
                    const okType = !this.filters.type || row.type === this.filters.type;
                    const okDept = !this.filters.department || row.department === this.filters.department;
                    const okSem  = !this.filters.semester || row.semester === this.filters.semester;
                    const hay = (row.student + ' ' + row.number).toLowerCase();
                    const okSearch = !term || hay.includes(term);
                    return okType && okDept && okSem && okSearch;
                });
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['اسم الطالب','رقم القيد','القسم','نوع الإنذار','الفصل','التاريخ','الحالة'];
                const rows = this.records.map(row => [row.student, row.number, row.department, row.type, row.semester, row.date, row.status === 'active' ? 'ساري' : 'مغلق']);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'warnings.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
