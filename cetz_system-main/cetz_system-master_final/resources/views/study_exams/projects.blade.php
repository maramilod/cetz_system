@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="gradProjects()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex gap-2">
            <button type="button" class="px-3 py-1 rounded border" :class="mode==='projects' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white'" @click="mode='projects'">تنسيق المشاريع</button>
            <button type="button" class="px-3 py-1 rounded border" :class="mode==='graduates' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white'" @click="mode='graduates'">طلاب فصل التخرج</button>
        </div>

        <!-- تنسيق المشاريع (القائمة الحالية) -->
        <div class="flex flex-wrap gap-3 items-end" x-show="mode==='projects'">
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
                <label class="block text-sm text-gray-600 mb-1">المشرف</label>
                <select x-model="filters.supervisor" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">الكل</option>
                    <template x-for="sup in supervisors" :key="sup">
                        <option :value="sup" x-text="sup"></option>
                    </template>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="عنوان/فريق المشروع" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="flex gap-2">
                <button type="button" class="h-10 px-4 bg-gray-200 rounded" @click="window.print()">🖨️ طباعة</button>
                <button type="button" class="h-10 px-4 bg-green-600 text-white rounded" @click="exportCsv">⬇️ تصدير CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto" x-show="mode==='projects'">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">عنوان المشروع</th>
                        <th class="border px-3 py-2 text-right">الفريق</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">المشرف</th>
                        <th class="border px-3 py-2 text-right">نسبة التقدم</th>
                        <th class="border px-3 py-2 text-right">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr><td colspan="6" class="border px-3 py-4 text-center text-gray-500">لا توجد مشاريع مطابقة.</td></tr>
                    </template>
                    <template x-for="project in records" :key="project.title">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="project.title"></td>
                            <td class="border px-3 py-2" x-text="project.team"></td>
                            <td class="border px-3 py-2" x-text="project.department"></td>
                            <td class="border px-3 py-2" x-text="project.supervisor"></td>
                            <td class="border px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span x-text="project.progress + '%'" class="text-sm"></span>
                                    <div class="flex-1 h-2 bg-gray-200 rounded">
                                        <div class="h-full bg-blue-500 rounded" :style="'width:' + project.progress + '%'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="border px-3 py-2">
                                <span class="px-2 py-1 rounded" :class="statusBadge(project.status)" x-text="statusLabel(project.status)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- تنسيق طلاب فصل التخرج -->
        <div class="flex flex-wrap gap-3 items-end" x-show="mode==='graduates'">
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="gradFilters.department" @change="applyGradFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    <template x-for="dept in gradDepartments" :key="dept"><option :value="dept" x-text="dept"></option></template>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="gradFilters.semester" @change="applyGradFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    <template x-for="sem in gradSemesters" :key="sem"><option :value="sem" x-text="sem"></option></template>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="gradFilters.search" @input.debounce.300="applyGradFilters" placeholder="ابحث بالاسم أو رقم القيد" class="border rounded px-3 py-2 w-full">
            </div>
        </div>

        <div class="overflow-x-auto" x-show="mode==='graduates'">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رم</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">رقم القيد</th>
                        <th class="border px-3 py-2 text-right">المعدل التراكمي</th>
                        <th class="border px-3 py-2 text-right">ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!gradRecords.length">
                        <tr><td colspan="5" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td></tr>
                    </template>
                    <template x-for="row in gradRecords" :key="row.id">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.id"></td>
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.number"></td>
                            <td class="border px-3 py-2" x-text="Number(row.gpa).toFixed(2)"></td>
                            <td class="border px-3 py-2" x-text="row.note || ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradProjects', () => ({
            mode: 'projects',
            // مشاريع التخرج (التنسيق القديم)
            dataset: [
                { title: 'منصة تواصل داخلية للطلبة', team: 'آمنة/محمد', department: 'هندسة كهربائية', supervisor: 'د. خالد', progress: 80, status: 'on-track' },
                { title: 'تحليل صور الأقمار الصناعية', team: 'سارة/ليث', department: 'علوم حاسوب', supervisor: 'م. رنا', progress: 60, status: 'needs-support' },
                { title: 'نظام صيانة تنبئي', team: 'هاجر/ياسين', department: 'هندسة ميكانيك', supervisor: 'م. فؤاد', progress: 45, status: 'delayed' }
            ],
            records: [],
            filters: { department: '', supervisor: '', search: '' },
            departments: ['هندسة كهربائية', 'علوم حاسوب', 'هندسة ميكانيك'],
            supervisors: ['د. خالد', 'م. رنا', 'م. فؤاد'],

            // طلاب فصل التخرج
            gradDataset: [
                { id: 1, name: 'آمنة علي',   number: '2025-001', department: 'هندسة كهربائية', semester: 'ربيعي 2025', gpa: 3.82, note: '' },
                { id: 2, name: 'محمد عمر',   number: '2025-010', department: 'علوم حاسوب',     semester: 'ربيعي 2025', gpa: 3.55, note: 'مشروع ذكاء اصطناعي' },
                { id: 3, name: 'سارة محمود', number: '2024-075', department: 'هندسة ميكانيك',  semester: 'خريفي 2024', gpa: 3.70, note: '' }
            ],
            gradRecords: [],
            gradFilters: { department: '', semester: '', search: '' },
            gradDepartments: [],
            gradSemesters: [],

            init() {
                this.applyFilters();
                this.gradDepartments = Array.from(new Set(this.gradDataset.map(r => r.department)));
                this.gradSemesters   = Array.from(new Set(this.gradDataset.map(r => r.semester)));
                this.applyGradFilters();
            },

            applyFilters() {
                const term = this.filters.search.trim();
                this.records = this.dataset.filter(project => {
                    const matchesDept = !this.filters.department || project.department === this.filters.department;
                    const matchesSup  = !this.filters.supervisor || project.supervisor === this.filters.supervisor;
                    const matchesTerm = !term || project.title.includes(term) || project.team.includes(term);
                    return matchesDept && matchesSup && matchesTerm;
                });
            },

            applyGradFilters() {
                const term = this.gradFilters.search.trim().toLowerCase();
                this.gradRecords = this.gradDataset.filter(r => {
                    const okDept = !this.gradFilters.department || r.department === this.gradFilters.department;
                    const okSem  = !this.gradFilters.semester || r.semester === this.gradFilters.semester;
                    const okTerm = !term || (r.name + ' ' + r.number).toLowerCase().includes(term);
                    return okDept && okSem && okTerm;
                });
            },

            statusLabel(status) {
                if (status === 'on-track') return 'على المسار';
                if (status === 'needs-support') return 'بحاجة دعم';
                return 'متأخر';
            },

            statusBadge(status) {
                if (status === 'on-track') return 'bg-green-100 text-green-700';
                if (status === 'needs-support') return 'bg-amber-100 text-amber-700';
                return 'bg-red-100 text-red-700';
            },

            exportCsv() {
                if (this.mode === 'projects') {
                    if (!this.records.length) { alert('لا توجد بيانات لتصديرها.'); return; }
                    const header = ['عنوان المشروع','الفريق','القسم','المشرف','التقدم','الحالة'];
                    const rows = this.records.map(p => [p.title, p.team, p.department, p.supervisor, p.progress + '%', this.statusLabel(p.status)]);
                    const csv = [header].concat(rows).map(cols => cols.map(v => '"' + v + '"').join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = 'graduate-projects.csv'; link.click(); URL.revokeObjectURL(link.href);
                } else {
                    if (!this.gradRecords.length) { alert('لا توجد بيانات لتصديرها.'); return; }
                    const header = ['رم','اسم الطالب','رقم القيد','المعدل التراكمي','ملاحظة'];
                    const rows = this.gradRecords.map(r => [r.id, r.name, r.number, Number(r.gpa).toFixed(2), r.note || '']);
                    const csv = [header].concat(rows).map(cols => cols.map(v => '"' + v + '"').join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = 'graduation-semester-students.csv'; link.click(); URL.revokeObjectURL(link.href);
                }
            }
        }));
    });
</script>
@endsection
