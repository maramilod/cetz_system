@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="resultsApproval()" x-init="applyFilters()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">اعتماد النتائج</h1>
        <p class="text-gray-600">إدارة اعتماد نتائج الفصول الدراسية.</p>

        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[240px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="search" @input.debounce.300="applyFilters" placeholder="ابحث باسم الفصل" class="border rounded px-3 py-2 w-full">
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
                        <th class="border px-3 py-2 text-right">رم</th>
                        <th class="border px-3 py-2 text-right">الفصل الدراسي</th>
                        <th class="border px-3 py-2 text-right">معتمد؟</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="3" class="border px-3 py-4 text-center text-gray-500">لا توجد سجلات مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.id">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.id"></td>
                            <td class="border px-3 py-2" x-text="row.semester"></td>
                            <td class="border px-3 py-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" class="h-4 w-4" :checked="row.approved" @change="toggleApproval(row)">
                                    <span :class="row.approved ? 'text-green-700' : 'text-gray-500'" x-text="row.approved ? 'معتمد' : 'غير معتمد'"></span>
                                </label>
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
        Alpine.data('resultsApproval', () => ({
            dataset: [
                { id: 1, semester: 'ربيعي 2025', approved: true },
                { id: 2, semester: 'خريفي 2024', approved: false },
                { id: 3, semester: 'ربيعي 2024', approved: true },
                { id: 4, semester: 'خريفي 2023', approved: false }
            ],
            records: [],
            search: '',

            applyFilters() {
                const term = this.search.trim().toLowerCase();
                this.records = this.dataset.filter(r => !term || String(r.semester).toLowerCase().includes(term));
            },

            toggleApproval(row) {
                row.approved = !row.approved;
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['رم','الفصل الدراسي','معتمد؟'];
                const rows = this.records.map(r => [r.id, r.semester, r.approved ? 'نعم' : 'لا']);
                const csv = [header].concat(rows).map(cols => cols.map(v => '"' + v + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'results-approval.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
