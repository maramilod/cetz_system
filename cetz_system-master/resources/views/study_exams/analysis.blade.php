@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="resultsAnalysis()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <template x-for="item in cards" :key="item.key">
                <div class="bg-gray-50 border rounded-lg p-4">
                    <div class="text-sm text-gray-500" x-text="item.label"></div>
                    <div class="text-2xl font-bold" :class="item.color" x-text="item.value"></div>
                    <div class="text-xs text-gray-400" x-text="item.note"></div>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-3">نسب النجاح حسب القسم</h3>
                <template x-for="point in departmentSeries" :key="point.name">
                    <div class="mb-2">
                        <div class="flex justify-between text-sm">
                            <span x-text="point.name"></span>
                            <span x-text="point.value + '%'"></span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded">
                            <div class="h-full bg-green-500 rounded" :style="'width:' + point.value + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-3">توزيع التقديرات</h3>
                <template x-for="grade in gradeDistribution" :key="grade.name">
                    <div class="flex justify-between text-sm mb-1">
                        <span x-text="grade.name"></span>
                        <span x-text="grade.count"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">عدد الطلاب</th>
                        <th class="border px-3 py-2 text-right">ناجحين</th>
                        <th class="border px-3 py-2 text-right">راسبين</th>
                        <th class="border px-3 py-2 text-right">نسبة النجاح</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in report" :key="row.department">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.total"></td>
                            <td class="border px-3 py-2" x-text="row.passed"></td>
                            <td class="border px-3 py-2" x-text="row.failed"></td>
                            <td class="border px-3 py-2" x-text="row.successRate + '%'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('resultsAnalysis', () => ({
            report: [
                { department: 'هندسة كهربائية', total: 120, passed: 102, failed: 18 },
                { department: 'علوم حاسوب', total: 95, passed: 80, failed: 15 },
                { department: 'هندسة ميكانيك', total: 88, passed: 70, failed: 18 }
            ],
            cards: [],
            departmentSeries: [],
            gradeDistribution: [
                { name: 'امتياز', count: 25 },
                { name: 'جيد جداً', count: 42 },
                { name: 'جيد', count: 56 },
                { name: 'مقبول', count: 32 },
                { name: 'ضعيف', count: 8 }
            ],

            init() {
                this.report = this.report.map(item => ({ ...item, successRate: Math.round((item.passed / item.total) * 100) }));
                this.departmentSeries = this.report.map(item => ({ name: item.department, value: item.successRate }));
                const totals = this.report.reduce((acc, row) => {
                    acc.total += row.total;
                    acc.passed += row.passed;
                    acc.failed += row.failed;
                    return acc;
                }, { total: 0, passed: 0, failed: 0 });
                const overall = totals.total ? Math.round((totals.passed / totals.total) * 100) : 0;
                this.cards = [
                    { key: 'total', label: 'إجمالي الطلبة', value: totals.total, color: '' , note: 'جميع الأقسام' },
                    { key: 'success', label: 'نسبة النجاح العام', value: overall + '%', color: 'text-green-600', note: 'مقارنة بالفصل الحالي' },
                    { key: 'gap', label: 'الفجوة بين أفضل وأسوأ قسم', value: this.calculateGap() + '%', color: 'text-amber-600', note: 'نسبة النجاح الأعلى - الأدنى' }
                ];
            },

            calculateGap() {
                if (!this.departmentSeries.length) return 0;
                const values = this.departmentSeries.map(item => item.value);
                return Math.max(...values) - Math.min(...values);
            }
        }));
    });
</script>
@endsection
