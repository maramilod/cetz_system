@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="secondRoundList()" x-init="applyFilters()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
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
                <label class="block text-sm text-gray-600 mb-1">المادة</label>
                <select x-model="filters.subject" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل المواد</option>
                    <template x-for="subject in subjects" :key="subject">
                        <option :value="subject" x-text="subject"></option>
                    </template>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب" class="border rounded px-3 py-2 w-full">
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
                        <th class="border px-3 py-2 text-right">الطالب</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">المادة</th>
                        <th class="border px-3 py-2 text-right">الدرجة السابقة</th>
                        <th class="border px-3 py-2 text-right">سبب التأجيل</th>
                        <th class="border px-3 py-2 text-right">موعد الدور الثاني</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="6" class="border px-3 py-4 text-center text-gray-500">لا يوجد طلاب مسجلين للدور الثاني.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.student + row.subject">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.student"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.subject"></td>
                            <td class="border px-3 py-2" x-text="row.previous"></td>
                            <td class="border px-3 py-2" x-text="row.reason"></td>
                            <td class="border px-3 py-2" x-text="row.date"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('secondRoundList', () => ({
            dataset: [
                { student: 'آمنة علي', department: 'هندسة كهربائية', subject: 'دوائر كهربائية', previous: 45, reason: 'ظروف صحية', date: '2025-02-10' },
                { student: 'محمد عمر', department: 'علوم حاسوب', subject: 'برمجة 2', previous: 48, reason: 'تعارض جدول', date: '2025-02-12' }
            ],
            filters: { department: '', subject: '', search: '' },
            departments: ['هندسة كهربائية', 'علوم حاسوب', 'هندسة ميكانيك'],
            subjects: ['دوائر كهربائية', 'برمجة 2', 'تحليل إنشائي'],
            records: [],

            applyFilters() {
                const term = this.filters.search.trim();
                this.records = this.dataset.filter(row => {
                    const matchesDept = !this.filters.department || row.department === this.filters.department;
                    const matchesSub = !this.filters.subject || row.subject === this.filters.subject;
                    const matchesTerm = !term || row.student.includes(term);
                    return matchesDept && matchesSub && matchesTerm;
                });
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['الطالب', 'القسم', 'المادة', 'الدرجة السابقة', 'السبب', 'موعد الامتحان'];
                const rows = this.records.map(row => [row.student, row.department, row.subject, row.previous, row.reason, row.date]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'second-round.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
