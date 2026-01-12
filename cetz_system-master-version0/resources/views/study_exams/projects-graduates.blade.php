@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="gradSemesterStudents()" x-init="init()">
  <div class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex flex-wrap gap-3 items-end">
      <div class="min-w-[160px]">
        <label class="block text-sm text-gray-600 mb-1">القسم</label>
        <select x-model="filters.department" @change="applyFilters" class="border rounded px-3 py-2 w-full">
          <option value="">كل الأقسام</option>
          <template x-for="dept in departments" :key="dept"><option :value="dept" x-text="dept"></option></template>
        </select>
      </div>
      <div class="min-w-[160px]">
        <label class="block text-sm text-gray-600 mb-1">الفصل</label>
        <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
          <option value="">كل الفصول</option>
          <template x-for="sem in semesters" :key="sem"><option :value="sem" x-text="sem"></option></template>
        </select>
      </div>
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm text-gray-600 mb-1">بحث</label>
        <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="ابحث بالاسم أو رقم القيد" class="border rounded px-3 py-2 w-full">
      </div>
    </div>

    <div class="overflow-x-auto">
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
          <template x-if="!records.length">
            <tr><td colspan="5" class="border px-3 py-4 text-center text-gray-500">لا توجد نتائج مطابقة.</td></tr>
          </template>
          <template x-for="row in records" :key="row.id">
            <tr class="hover:bg-gray-50 odd:bg-gray-50">
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
    Alpine.data('gradSemesterStudents', () => ({
      dataset: [
        { id: 1, name: 'آمنة علي',   number: '2025-001', department: 'هندسة كهربائية', semester: 'ربيع 2025', gpa: 3.82, note: '' },
        { id: 2, name: 'محمد عمر',   number: '2025-010', department: 'علوم حاسوب',     semester: 'ربيع 2025', gpa: 3.55, note: 'مشروع ذكاء اصطناعي' },
        { id: 3, name: 'سارة محمود', number: '2024-075', department: 'هندسة ميكانيك',  semester: 'خريف 2024', gpa: 3.70, note: '' }
      ],
      records: [],
      filters: { department: '', semester: '', search: '' },
      departments: [],
      semesters: [],

      init(){
        this.departments = Array.from(new Set(this.dataset.map(r => r.department)));
        this.semesters   = Array.from(new Set(this.dataset.map(r => r.semester)));
        this.applyFilters();
      },
      applyFilters(){
        const term = this.filters.search.trim().toLowerCase();
        this.records = this.dataset.filter(r => {
          const okDept = !this.filters.department || r.department === this.filters.department;
          const okSem  = !this.filters.semester || r.semester === this.filters.semester;
          const okTerm = !term || (r.name + ' ' + r.number).toLowerCase().includes(term);
          return okDept && okSem && okTerm;
        });
      }
    }));
  });
</script>
@endsection
