@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="deprivedSummary()" x-init="init()">
    <div class="flex flex-wrap gap-3">
        <template x-for="card in cards" :key="card.key">
            <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500" x-text="card.label"></div>
                <div class="text-2xl font-bold" :class="card.color" x-text="card.value"></div>
            </div>
        </template>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">نوع الحرمان</label>
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
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث بالاسم أو رقم القيد أو المادة" class="border rounded px-3 py-2 w-full">
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
                        <th class="border px-3 py-2 text-right">المادة</th>
                        <th class="border px-3 py-2 text-right">الفصل</th>
                        <th class="border px-3 py-2 text-right">نوع الحرمان</th>
                        <th class="border px-3 py-2 text-right">تاريخ الإدراج</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="7" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.student + row.subject">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.student"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.subject"></td>
                            <td class="border px-3 py-2" x-text="row.semester"></td>
                            <td class="border px-3 py-2" x-text="row.type === 'غياب' ? ('غياب — ' + (row.absence_percent || 0) + '%') : row.type"></td>
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
        Alpine.data('deprivedSummary', () => ({
            dataset: [
                { student: 'آمنة علي',   number: '2025-001', department: 'هندسة كهربائية', subject: 'رياضيات 1', semester: 'ربيع 2025', type: 'غياب',      absence_percent: 30,  date: '2025-01-05' },
                { student: 'محمد عمر',   number: '2025-010', department: 'علوم حاسوب',     subject: 'برمجة 1',  semester: 'ربيع 2025', type: 'سلوك',      absence_percent: null, date: '2025-01-08' },
                { student: 'سارة محمود', number: '2024-075', department: 'هندسة ميكانيك',  subject: 'ميكانيكا', semester: 'خريف 2024', type: 'رسوب سابق', absence_percent: null, date: '2024-12-28' }
            ],
            records: [],
            filters: { type: '', department: '', semester: '', search: '' },
            types: ['غياب', 'سلوك', 'رسوب سابق'],
            departments: ['هندسة كهربائية', 'علوم حاسوب', 'هندسة ميكانيك'],
            semesters: ['ربيع 2025', 'خريف 2024'],
            cards: [
                { key: 'total', label: 'عدد حالات الحرمان', value: '0', color: '' },
                { key: 'latest', label: 'آخر إدراج', value: '-', color: 'text-blue-600' }
            ],

            init() {
                this.applyFilters();
                // دمج العناصر المحفوظة محلياً (من شاشة الإدخال)
                try {
                    const extra = JSON.parse(localStorage.getItem('deprived_extra') || '[]');
                    if (Array.isArray(extra) && extra.length) {
                        this.dataset = this.dataset.concat(extra);
                        this.applyFilters();
                    }
                } catch {}
            },

            applyFilters() {
                const term = this.filters.search.trim().toLowerCase();
                this.records = this.dataset.filter(row => {
                    const matchesType = !this.filters.type || row.type === this.filters.type;
                    const matchesDept = !this.filters.department || row.department === this.filters.department;
                    const matchesSem  = !this.filters.semester || row.semester === this.filters.semester;
                    const matchesTerm = !term || (row.student + ' ' + row.number + ' ' + row.subject).toLowerCase().includes(term);
                    return matchesType && matchesDept && matchesSem && matchesTerm;
                });
                this.updateCards();
            },

            updateCards() {
                this.cards.find(card => card.key === 'total').value = String(this.records.length);
                const latest = this.records.slice().sort((a, b) => b.date.localeCompare(a.date))[0];
                this.cards.find(card => card.key === 'latest').value = latest ? latest.date : '-';
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['اسم الطالب','رقم القيد','القسم','المادة','الفصل','نوع الحرمان','تاريخ الإدراج'];
                const rows = this.records.map(row => [
                    row.student,
                    row.number,
                    row.department,
                    row.subject,
                    row.semester,
                    row.type === 'غياب' ? ('غياب — ' + (row.absence_percent || 0) + '%') : row.type,
                    row.date
                ]);
                const csv = [header].concat(rows).map(columns => columns.map(value => '"' + value + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'deprived-list.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
