@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="gradeSheet()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">المادة</label>
                <select x-model="filters.subject" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <template x-for="subject in filters.subjects" :key="subject">
                        <option :value="subject" x-text="subject"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="filters.department" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    <template x-for="dept in filters.departments" :key="dept">
                        <option :value="dept" x-text="dept"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <template x-for="sem in filters.semesters" :key="sem">
                        <option :value="sem" x-text="sem"></option>
                    </template>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="window.print()">🖨️ طباعة</button>
                <button type="button" class="px-4 py-2 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">#</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">100؟</th>
                        <th class="border px-3 py-2 text-right">عملي 1</th>
                        <th class="border px-3 py-2 text-right">نظري 1</th>
                        <th class="border px-3 py-2 text-right">مجموع 1</th>
                        <th class="border px-3 py-2 text-right">عملي 2</th>
                        <th class="border px-3 py-2 text-right">نظري 2</th>
                        <th class="border px-3 py-2 text-right">مجموع 2</th>
                        <th class="border px-3 py-2 text-right">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in records" :key="row.number">
                        <tr class="odd:bg-gray-50">
                            <td class="border px-3 py-2" x-text="idx + 1"></td>
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.on100 ? 'نعم' : 'لا'"></td>
                            <td class="border px-3 py-2" x-text="row.practical1"></td>
                            <td class="border px-3 py-2" x-text="row.theoretical1"></td>
                            <td class="border px-3 py-2" x-text="row.sum1"></td>
                            <td class="border px-3 py-2" x-text="row.practical2"></td>
                            <td class="border px-3 py-2" x-text="row.theoretical2"></td>
                            <td class="border px-3 py-2" x-text="row.sum2"></td>
                            <td class="border px-3 py-2" x-text="row.total"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradeSheet', () => ({
            dataset: [
                { number: '2025-001', name: 'آمنة علي',   department: 'هندسة كهربائية', subject: 'رياضيات 1', semester: 'ربيع 2025', practical1: 20, theoretical1: 30, practical2: 10, theoretical2: 15 },
                { number: '2025-010', name: 'محمد عمر',   department: 'علوم حاسوب',     subject: 'رياضيات 1', semester: 'ربيع 2025', practical1: 18, theoretical1: 28, practical2: 9,  theoretical2: 16 },
                { number: '2025-015', name: 'ليلى يوسف', department: 'علوم حاسوب',     subject: 'رياضيات 1', semester: 'ربيع 2025', practical1: 15, theoretical1: 25, practical2: 8,  theoretical2: 12 },
                { number: '2024-075', name: 'سارة محمود', department: 'هندسة ميكانيك',   subject: 'فيزياء 1',  semester: 'خريف 2024', practical1: 16, theoretical1: 22, practical2: 7,  theoretical2: 9  }
            ],
            filters: {
                subject: '',
                department: '',
                semester: '',
                subjects: [],
                departments: [],
                semesters: []
            },
            records: [],

            init() {
                this.filters.subjects = Array.from(new Set(this.dataset.map(item => item.subject)));
                this.filters.departments = Array.from(new Set(this.dataset.map(item => item.department)));
                this.filters.semesters = Array.from(new Set(this.dataset.map(item => item.semester)));
                this.filters.subject = this.filters.subjects[0] || '';
                this.filters.semester = this.filters.semesters[0] || '';
                this.applyFilters();
            },

            applyFilters() {
                this.records = this.dataset
                    .filter(row => (!this.filters.subject || row.subject === this.filters.subject)
                        && (!this.filters.department || row.department === this.filters.department)
                        && (!this.filters.semester || row.semester === this.filters.semester))
                    .map(row => {
                        const sum1 = (Number(row.practical1)||0) + (Number(row.theoretical1)||0);
                        const sum2 = (Number(row.practical2)||0) + (Number(row.theoretical2)||0);
                        const total = sum1 + sum2;
                        return {
                            ...row,
                            sum1,
                            sum2,
                            total,
                            on100: total <= 100
                        };
                    });
            },

            gradeFromTotal(total) {
                if (total >= 85) return 'ممتاز';
                if (total >= 75) return 'جيد جداً';
                if (total >= 65) return 'جيد';
                if (total >= 50) return 'مقبول';
                return 'ضعيف';
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['اسم الطالب','رقم القيد','القسم','100؟','عملي 1','نظري 1','مجموع 1','عملي 2','نظري 2','مجموع 2','المجموع'];
                const rows = this.records.map(row => [
                    row.name,
                    row.number,
                    row.department,
                    row.on100 ? 'نعم' : 'لا',
                    row.practical1,
                    row.theoretical1,
                    row.sum1,
                    row.practical2,
                    row.theoretical2,
                    row.sum2,
                    row.total
                ]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'grade-sheet.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
