@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="bankReport()" x-init="applyFilters()">
    <div class="flex flex-wrap gap-3">
        <template x-for="item in summary" :key="item.bank">
            <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500" x-text="item.bank"></div>
                <div class="text-2xl font-bold" x-text="item.count + ' طالب'" ></div>
                <div class="text-xs text-gray-400" x-text="'إجمالي المنح: ' + item.total.toLocaleString('ar-LY') + ' د.ل'" ></div>
            </div>
        </template>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-wrap gap-3 items-end flex-1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-600 mb-1">بحث</label>
                    <input type="text" x-model.trim="search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب أو القسم" class="border rounded px-3 py-2 w-full">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">المصرف</label>
                    <select x-model="bankFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل المصارف</option>
                        <template x-for="bank in banks" :key="bank">
                            <option :value="bank" x-text="bank"></option>
                        </template>
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
                        <th class="border px-3 py-2 text-right">المصرف</th>
                        <th class="border px-3 py-2 text-right">قيمة المنحة (د.ل)</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="5" class="border px-3 py-4 text-center text-gray-500">لا توجد بيانات مطابقة للبحث الحالي.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.number">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.bank"></td>
                            <td class="border px-3 py-2" x-text="row.allowance.toLocaleString('ar-LY')"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bankReport', () => ({
            dataset: [
                { number: '2025-001', name: 'آمنة علي', department: 'هندسة كهربائية', bank: 'مصرف الجمهورية', allowance: 450 },
                { number: '2025-010', name: 'محمد عمر', department: 'علوم حاسوب', bank: 'مصرف الوحدة', allowance: 450 },
                { number: '2024-075', name: 'سارة محمود', department: 'هندسة ميكانيك', bank: 'مصرف الجمهورية', allowance: 500 },
                { number: '2023-050', name: 'علي حسن', department: 'هندسة كهربائية', bank: 'مصرف التجارة والتنمية', allowance: 420 }
            ],
            records: [],
            summary: [],
            banks: ['مصرف الجمهورية', 'مصرف الوحدة', 'مصرف التجارة والتنمية'],
            search: '',
            bankFilter: '',

            applyFilters() {
                const term = this.search.trim();
                const bank = this.bankFilter;
                this.records = this.dataset.filter(row => {
                    const matchesTerm = !term || [row.name, row.department, row.number].some(field => field.includes(term));
                    const matchesBank = !bank || row.bank === bank;
                    return matchesTerm && matchesBank;
                });
                this.buildSummary();
            },

            buildSummary() {
                const groups = {};
                this.records.forEach(row => {
                    if (!groups[row.bank]) {
                        groups[row.bank] = { bank: row.bank, count: 0, total: 0 };
                    }
                    groups[row.bank].count += 1;
                    groups[row.bank].total += row.allowance;
                });
                this.summary = Object.values(groups);
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['الرقم الجامعي', 'اسم الطالب', 'القسم', 'المصرف', 'قيمة المنحة'];
                const rows = this.records.map(row => [row.number, row.name, row.department, row.bank, row.allowance]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'bank-report.csv';
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
