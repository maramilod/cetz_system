@extends('layouts.app')

@section('content')
@php
$srv = [];
if (isset($students) && $students->count()) {
    foreach ($students as $s) {
        $srv[] = [
            'id' => $s->id,
            'student_number' => (string)($s->student_number ?? ''),
                        'manual_number' => (string)($s->manual_number ?? ''),
            'full_name' => (string)($s->full_name ?? ''),
            'department' => (string)($s->department->name ?? ''),
            'status' => (string)($s->status ?? 'active'),
            'nationality' => (string)($s->nationality ?? ''),
            'gender' => (string)($s->gender ?? ''),
            'passport' => (string)($s->passport_number ?? ''),
            'current_status' => (string)($s->current_status ?? ''),
            'dob' => (string)($s->dob ?? ''),
        ];
    }
}
$all = array_values($srv);
$deptList = array_values(array_unique(array_map(fn($r)=>$r['department'], $all)));
@endphp

<div class="space-y-4" x-data='studentsPage({ dataset: @json($all), departments: @json($deptList) })' x-init="init()">
    <!-- عنوان الصفحة وأزرار -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">الطلاب</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('students.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">إضافة طالب</a>
        </div>
    </div>

    <!-- الفلاتر -->
    <div class="bg-white p-4 rounded-lg shadow-sm">
        <div class="flex items-center gap-3 mb-4 flex-wrap">
            <select x-model="filters.department" @change="applyFilters" class="border rounded p-2">
                <option value="">كل الأقسام</option>
                <template x-for="d in departments" :key="d">
                    <option :value="d" x-text="d"></option>
                </template>
            </select>
            <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث بالاسم أو رقم القيد" class="border rounded p-2 flex-1 min-w-[240px]" />
            <select x-model="filters.status" @change="applyFilters" class="border rounded p-2">
                <option value="all">الكل</option>
                <option value="active">نشط</option>
                <option value="graduated">خريج</option>
            </select>
        </div>

        <!-- جدول الطلاب -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-right">
                        <th class="p-2">رقم القيد</th>
                        <th class="p-2">الاسم</th>
                        <th class="p-2">القسم</th>
                        <th class="p-2">الجنسية</th>
                        <th class="p-2">الجنس</th>
                        <th class="p-2">رقم جواز السفر</th>
                                                <th class="p-2">الحالة</th>
                        <th class="p-2">تاريخ الميلاد</th>
                        <th class="p-2">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="!records.length">
                        <tr><td colspan="10" class="p-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td></tr>
                    </template>
                    <template x-for="s in records" :key="s.id">
                        <tr class="text-right odd:bg-gray-50">
<td class="p-2" x-text="s.student_number ? s.student_number : (s.manual_number ? s.manual_number : '-')"></td>
                            <td class="p-2" x-text="s.full_name"></td>
                            <td class="p-2" x-text="s.department"></td>
                            <td class="p-2" x-text="s.nationality"></td>
                            <td class="p-2" x-text="s.gender"></td>
                            <td class="p-2" x-text="s.passport"></td>
                            <td class="p-2" x-text="s.current_status"></td>
                            <td class="p-2" x-text="s.dob"></td>
                            <td class="p-2">
                                <span class="px-2 py-1 rounded text-xs" :class="s.status==='graduated' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'" x-text="s.status==='graduated' ? 'خريج' : 'نشط'"></span>
                            </td>
                            <td class="p-2 space-x-2 rtl:space-x-reverse">
                                <a :href="studentShowUrl(s)" class="text-blue-600">عرض</a>
                                <a :href="studentEditUrl(s)" class="px-2 py-1 bg-yellow-100 rounded">تعديل</a>
                                <form :action="'/students/' + s.id" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-red-100 rounded">حذف</button>
                                </form>
                                  <a :href="`/students/specialization/${s.id}`"
       class="px-2 py-1 bg-blue-100 rounded text-blue-600">
       تغيير التخصص
    </a>
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
    Alpine.data('studentsPage', (initial) => ({
        all: [],
        records: [],
        departments: initial.departments || [],
        filters: { department: '', status: 'all', search: '' },

        init() {
            this.all = Array.isArray(initial.dataset) ? initial.dataset : [];
            if (!this.departments.length) {
                this.departments = Array.from(new Set(this.all.map(r => r.department))).filter(Boolean);
            }
            this.applyFilters();
        },

        applyFilters() {
            const term = this.filters.search.trim().toLowerCase();
            this.records = this.all.filter(r => {
                const okDept = !this.filters.department || r.department === this.filters.department;
                const okStatus = this.filters.status === 'all' || r.status === this.filters.status;
                const okTerm = !term || (
                    String(r.full_name||'').toLowerCase().includes(term) ||
                    String(r.student_number||'').toLowerCase().includes(term) ||
                                        String(r.manual_number||'').toLowerCase().includes(term)

                );
                return okDept && okStatus && okTerm;
            });
        },

        studentShowUrl(s){
            return '/students/' + s.id;
        },
        studentEditUrl(s){
            return '/students/' + s.id + '/edit';
        }
    }));
});
</script>
@endsection
