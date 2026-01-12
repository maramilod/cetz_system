@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="semesterFinalResults()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب أو رقم القيد" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    <template x-for="sem in filters.semesters" :key="sem">
                        <option :value="sem" x-text="sem"></option>
                    </template>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">الدور</label>
                <select x-model="filters.round" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">الكل</option>
                    <template x-for="r in filters.rounds" :key="r">
                        <option :value="r" x-text="r"></option>
                    </template>
                </select>
            </div>
            <div class="flex gap-2 ml-auto">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="window.print()">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportCsv()">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">الاسم</th>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">Sub1</th>
                        <th class="border px-3 py-2 text-right">Sub2</th>
                        <th class="border px-3 py-2 text-right">Sub3</th>
                        <th class="border px-3 py-2 text-right">Sub4</th>
                        <th class="border px-3 py-2 text-right">Sub5</th>
                        <th class="border px-3 py-2 text-right">Sub6</th>
                        <th class="border px-3 py-2 text-right">Sub7</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="9" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.number">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub1)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub2)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub3)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub4)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub5)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub6)"></td>
                            <td class="border px-3 py-2" x-text="fmt(row.sub7)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('semesterFinalResults', () => ({
            dataset: [
                { number: '2025-001', name: 'آمنة علي',   semester: 'ربيع 2025', round: 'الأول',  sub1: 85, sub2: 90, sub3: 78, sub4: 88, sub5: 92, sub6: 0,  sub7: null },
                { number: '2025-010', name: 'محمد عمر',   semester: 'ربيع 2025', round: 'الأول',  sub1: 80, sub2: 75, sub3: 70, sub4: 82, sub5: 77, sub6: 69, sub7: null },
                { number: '2024-075', name: 'سارة محمود', semester: 'خريف 2024', round: 'الثاني', sub1: 68, sub2: 74, sub3: 81, sub4: 79, sub5: 0,  sub6: null, sub7: null }
            ],
            records: [],
            filters: { search: '', semester: '', round: '', semesters: [], rounds: [] },

            init() {
                this.filters.semesters = Array.from(new Set(this.dataset.map(r => r.semester)));
                this.filters.rounds    = Array.from(new Set(this.dataset.map(r => r.round)));
                this.applyFilters();
            },

            fmt(v) { return (v === null || v === undefined || v === 0) ? '' : v; },

            applyFilters() {
                const term = this.filters.search.trim().toLowerCase();
                this.records = this.dataset.filter(r => {
                    const okTerm = !term || (r.name + ' ' + r.number).toLowerCase().includes(term);
                    const okSem  = !this.filters.semester || r.semester === this.filters.semester;
                    const okRnd  = !this.filters.round || r.round === this.filters.round;
                    return okTerm && okSem && okRnd;
                });
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['الاسم','رقم القيد','Sub1','Sub2','Sub3','Sub4','Sub5','Sub6','Sub7'];
                const rows = this.records.map(r => [r.name, r.number, r.sub1||'', r.sub2||'', r.sub3||'', r.sub4||'', r.sub5||'', r.sub6||'', r.sub7||'']);
                const csv = [header].concat(rows).map(cols => cols.map(v => '"' + v + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'semester-final-results.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
