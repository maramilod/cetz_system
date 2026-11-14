@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="departmentReport()" x-init="applyFilters()">
    <div class="flex flex-wrap gap-3">
        <template x-for="item in summary" :key="item.department">
            <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500" x-text="item.department"></div>
                <div class="text-2xl font-bold" x-text="item.count + ' طالب'" ></div>
                <div class="text-xs text-gray-400" x-text="'مستويات: ' + item.levels"></div>
            </div>
        </template>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-wrap gap-3 items-end flex-1">
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">القسم</label>
                    <select x-model="departmentFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل الأقسام</option>
                        <template x-for="dept in departments" :key="dept">
                            <option :value="dept" x-text="dept"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">المستوى</label>
                    <select x-model="levelFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل المستويات</option>
                        <option value="الأول">الأول</option>
                        <option value="الثاني">الثاني</option>
                        <option value="الثالث">الثالث</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="printTable">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">الرقم الجامعي</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">المستوى</th>
                        <th class="border px-3 py-2 text-right">الجنسية</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="5" class="border px-3 py-4 text-center text-gray-500">لا توجد بيانات مطابقة للمرشحات الحالية.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.number">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.level"></td>
                            <td class="border px-3 py-2" x-text="row.nationality"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('departmentReport', () => ({
            dataset: [
                { number: '2025-001', name: 'آمنة علي', department: 'هندسة كهربائية', level: 'الأول', nationality: 'ليبيا' },
                { number: '2025-010', name: 'محمد عمر', department: 'علوم حاسوب', level: 'الأول', nationality: 'ليبيا' },
                { number: '2024-075', name: 'سارة محمود', department: 'هندسة ميكانيك', level: 'الثاني', nationality: 'ليبيا' },
                { number: '2023-050', name: 'علي حسن', department: 'هندسة كهربائية', level: 'الثالث', nationality: 'ليبيا' }
            ],
            records: [],
            summary: [],
            departments: ['هندسة كهربائية', 'علوم حاسوب', 'هندسة ميكانيك'],
            departmentFilter: '',
            levelFilter: '',

            applyFilters() {
                const dept = this.departmentFilter;
                const level = this.levelFilter;
                this.records = this.dataset.filter(row => {
                    const matchesDept = !dept || row.department === dept;
                    const matchesLevel = !level || row.level === level;
                    return matchesDept && matchesLevel;
                });
                this.buildSummary();
            },

            buildSummary() {
                const groups = {};
                this.records.forEach(row => {
                    if (!groups[row.department]) {
                        groups[row.department] = { department: row.department, count: 0, levels: new Set() };
                    }
                    groups[row.department].count += 1;
                    groups[row.department].levels.add(row.level);
                });
                this.summary = Object.values(groups).map(item => ({
                    department: item.department,
                    count: item.count,
                    levels: Array.from(item.levels).join('، ')
                }));
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['الرقم الجامعي', 'اسم الطالب', 'القسم', 'المستوى', 'الجنسية'];
                const rows = this.records.map(row => [row.number, row.name, row.department, row.level, row.nationality]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'department-report.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            },

            printTable() {
                window.print();
            }
        }));
    });
</script>
@endsection
