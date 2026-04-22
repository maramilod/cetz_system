@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="examStatistics()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="filters.department" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    <template x-for="dept in filters.departments" :key="'dept-'+dept">
                        <option :value="dept" x-text="dept"></option>
                    </template>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    <template x-for="sem in filters.semesters" :key="'sem-'+sem">
                        <option :value="sem" x-text="sem"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <template x-for="card in cards" :key="card.key">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500" x-text="card.label"></div>
                    <div class="text-2xl font-bold" :class="card.color" x-text="card.value"></div>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-3">نسبة النجاح حسب الفصل</h3>
                <template x-for="item in successBySemester" :key="item.semester">
                    <div class="mb-2">
                        <div class="flex justify-between text-sm">
                            <span x-text="item.semester"></span>
                            <span x-text="item.rate + '%'" class="font-semibold"></span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded">
                            <div class="h-full bg-indigo-500 rounded" :style="'width:' + item.rate + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-3">متوسط النجاح حسب القسم والفصل</h3>
                <template x-for="item in averages" :key="item.department">
                    <div class="flex justify-between text-sm mb-1">
                        <span x-text="item.department"></span>
                        <span x-text="item.avg"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">الفصل</th>
                        <th class="border px-3 py-2 text-right">المسجلون</th>
                        <th class="border px-3 py-2 text-right">الناجحون</th>
                        <th class="border px-3 py-2 text-right">المؤجلون</th>
                        <th class="border px-3 py-2 text-right">المحرمون</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in filteredStats" :key="row.department + '-' + row.semester">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.semester"></td>
                            <td class="border px-3 py-2" x-text="row.enrolled"></td>
                            <td class="border px-3 py-2" x-text="row.passed"></td>
                            <td class="border px-3 py-2" x-text="row.delays"></td>
                            <td class="border px-3 py-2" x-text="row.deprived"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('examStatistics', () => ({
            departmentStats: [
                { department: 'هندسة كهربائية', semester: 'ربيع 2025', enrolled: 240, passed: 198, delays: 22, deprived: 5 },
                { department: 'علوم حاسوب',     semester: 'ربيع 2025', enrolled: 210, passed: 175, delays: 18, deprived: 7 },
                { department: 'هندسة ميكانيك',  semester: 'خريف 2024', enrolled: 180, passed: 142, delays: 25, deprived: 9 }
            ],
            filteredStats: [],
            filters: { department: '', semester: '', departments: [], semesters: [] },
            successBySemester: [
                { semester: 'ربيع 2025', rate: 84 },
                { semester: 'خريف 2024', rate: 78 },
                { semester: 'ربيع 2024', rate: 81 }
            ],
            averages: [],
            cards: [],

            init() {
                this.filters.departments = Array.from(new Set(this.departmentStats.map(r => r.department)));
                this.filters.semesters = Array.from(new Set(this.departmentStats.map(r => r.semester)));
                this.applyFilters();
            },

            applyFilters() {
                this.filteredStats = this.departmentStats.filter(r =>
                    (!this.filters.department || r.department === this.filters.department) &&
                    (!this.filters.semester || r.semester === this.filters.semester)
                );

                // تحديث المتوسطات والبطاقات وفق التصفية
                this.averages = this.filteredStats.map(row => ({
                    department: row.department + ' — ' + row.semester,
                    avg: (row.passed ? Math.round((row.passed / row.enrolled) * 100) : 0) + '%'
                }));

                const totals = this.filteredStats.reduce((acc, row) => {
                    acc.enrolled += row.enrolled;
                    acc.passed += row.passed;
                    acc.delays += row.delays;
                    acc.deprived += row.deprived;
                    return acc;
                }, { enrolled: 0, passed: 0, delays: 0, deprived: 0 });
                const successRate = totals.enrolled ? Math.round((totals.passed / totals.enrolled) * 100) : 0;
                this.cards = [
                    { key: 'enrolled', label: 'المسجلون', value: totals.enrolled, color: '' },
                    { key: 'passed',   label: 'الناجحون',   value: totals.passed,   color: 'text-green-600' },
                    { key: 'rate',     label: 'نسبة النجاح', value: successRate + '%', color: 'text-blue-600' },
                    { key: 'deprived', label: 'المحرمون',   value: totals.deprived, color: 'text-red-500' }
                ];
            }
        }));
    });
</script>
@endsection
