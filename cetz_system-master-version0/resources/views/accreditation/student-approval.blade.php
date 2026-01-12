@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="studentsApproval()" x-init="applyFilters()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">اعتماد بيانات الطلبة</h1>
        <p class="text-gray-600">إدارة واعتماد بيانات الطلبة. استخدم البحث لتصفية القائمة، ويمكنك تبديل حالة الاعتماد لكل سجل.</p>

        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[240px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="search" @input.debounce.300="applyFilters" placeholder="ابحث بالاسم أو القسم أو الرقم الوطني" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="departmentFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    <template x-for="dept in departments" :key="dept">
                        <option :value="dept" x-text="dept"></option>
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
                        <th class="border px-3 py-2 text-right">رقم</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">القسم</th>
                        <th class="border px-3 py-2 text-right">الرقم الوطني</th>
                        <th class="border px-3 py-2 text-right">رقم البطاقة الشخصية</th>
                        <th class="border px-3 py-2 text-right">معتمد؟</th>
                        <th class="border px-3 py-2 text-right">المستخدم</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="!records.length">
                        <tr>
                            <td colspan="7" class="border px-3 py-4 text-center text-gray-500">لا توجد سجلات مطابقة.</td>
                        </tr>
                    </template>
                    <template x-for="row in records" :key="row.id">
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2" x-text="row.id"></td>
                            <td class="border px-3 py-2" x-text="row.name"></td>
                            <td class="border px-3 py-2" x-text="row.department"></td>
                            <td class="border px-3 py-2" x-text="row.national_id"></td>
                            <td class="border px-3 py-2" x-text="row.personal_id"></td>
                            <td class="border px-3 py-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" class="h-4 w-4" :checked="row.approved" @change="toggleApproval(row)">
                                    <span :class="row.approved ? 'text-green-700' : 'text-gray-500'" x-text="row.approved ? 'معتمد' : 'غير معتمد'"></span>
                                </label>
                            </td>
                            <td class="border px-3 py-2" x-text="row.user"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('studentsApproval', () => ({
            dataset: [
                { id: 1, name: 'آمنة علي',   department: 'هندسة كهربائية', national_id: '218123456789', personal_id: 'A1234567', approved: true,  user: 'admin' },
                { id: 2, name: 'محمد عمر',   department: 'علوم حاسوب',     national_id: '218987654321', personal_id: 'B7654321', approved: false, user: 'reda'  },
                { id: 3, name: 'سارة محمود', department: 'هندسة ميكانيك',   national_id: '218456789123', personal_id: 'C1122334', approved: true,  user: 'omar'  },
                { id: 4, name: 'ليث الصادق', department: 'هندسة كهربائية', national_id: '218321654987', personal_id: 'D5566778', approved: false, user: 'admin' }
            ],
            records: [],
            search: '',
            departments: [],
            departmentFilter: '',

            init() {
                this.departments = Array.from(new Set(this.dataset.map(r => r.department))).filter(Boolean);
                this.applyFilters();
            },

            applyFilters() {
                const term = this.search.trim().toLowerCase();
                this.records = this.dataset.filter(row => {
                    const hay = (row.name + ' ' + row.department + ' ' + row.national_id).toLowerCase();
                    const okTerm = !term || hay.includes(term);
                    const okDept = !this.departmentFilter || row.department === this.departmentFilter;
                    return okTerm && okDept;
                });
            },

            toggleApproval(row) {
                row.approved = !row.approved;
            },

            exportCsv() {
                if (!this.records.length) {
                    alert('لا توجد بيانات لتصديرها.');
                    return;
                }
                const header = ['رقم','اسم الطالب','القسم','الرقم الوطني','رقم البطاقة الشخصية','معتمد؟','المستخدم'];
                const rows = this.records.map(r => [r.id, r.name, r.department, r.national_id, r.personal_id, r.approved ? 'نعم' : 'لا', r.user]);
                const csv = [header].concat(rows).map(cols => cols.map(v => '"' + v + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'students-approval.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
