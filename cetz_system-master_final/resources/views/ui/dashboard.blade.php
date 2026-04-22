@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="interactiveDashboard({})" x-init="init()">
  <!-- بطاقات ملخص -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <template x-for="card in cards" :key="card.key">
      <div class="p-4 bg-white rounded-lg shadow border border-gray-100 flex flex-col gap-2">
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span x-text="card.label"></span>
          <span class="text-xs" :class="card.trendClass" x-text="card.trend"></span>
        </div>
        <div class="text-3xl font-semibold" x-text="card.value"></div>
        <div class="w-full h-2 bg-gray-100 rounded">
          <div class="h-full rounded" :class="card.progressClass" :style="'width:' + card.progress + '%'"> </div>
        </div>
      </div>
    </template>
  </div>

  <!-- أقسام وتحديثات -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">أداء الأقسام</h2>
        <span class="text-sm text-gray-500">مُحدّث: {{ now()->format('Y-m-d') }}</span>
      </div>
      <template x-for="item in departmentPerformance" :key="item.name">
        <div>
          <div class="flex justify-between text-sm text-gray-600">
            <span x-text="item.name"></span>
            <span class="font-medium" x-text="item.rate + '%' "></span>
          </div>
          <div class="h-2 bg-gray-100 rounded mt-1">
            <div class="h-full rounded" :class="item.color" :style="'width:' + item.rate + '%'"> </div>
          </div>
        </div>
      </template>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">المهام السريعة</h2>
        <button class="text-sm text-blue-600" @click="shuffleTasks">تبديل</button>
      </div>
      <template x-if="!tasks.length">
        <div class="text-sm text-gray-500">لا توجد مهام حالياً.</div>
      </template>
      <ul class="space-y-3">
        <template x-for="task in tasks" :key="task.title">
          <li class="flex items-start gap-3 border rounded-lg p-3">
            <div class="mt-1 w-2 h-2 rounded-full" :class="task.color"></div>
            <div class="flex-1">
              <div class="flex justify-between text-sm">
                <span class="font-medium" x-text="task.title"></span>
                <span class="text-gray-400" x-text="task.due"></span>
              </div>
              <p class="text-xs text-gray-500" x-text="task.note"></p>
            </div>
          </li>
        </template>
      </ul>
    </div>
  </div>

  <!-- الطلاب: إحصاءات ونشاط حديث -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">ملخص الطلاب (حسب القسم)</h2>
        <span class="text-xs text-gray-500" x-text="studentsDataset.length + ' سجل' "></span>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <template x-for="item in countsByDepartment" :key="item.name">
          <div class="rounded-lg border p-3">
            <div class="text-sm text-gray-500" x-text="item.name"></div>
            <div class="text-2xl font-bold" x-text="item.count"></div>
          </div>
        </template>
      </div>
      <div class="flex items-center gap-3 text-sm text-gray-600">
        <div>نشِط: <span class="font-semibold" x-text="statusCounts.active"></span></div>
        <div>خريجون: <span class="font-semibold" x-text="statusCounts.graduated"></span></div>
      </div>
      <div class="flex items-end gap-2">
        <label class="text-sm text-gray-600">عرض قسم:</label>
        <select x-model="namesDept" @change="applyNamesFilter" class="border rounded px-3 py-1 text-sm">
          <option value="">الكل</option>
          <template x-for="d in departments" :key="d"><option :value="d" x-text="d"></option></template>
        </select>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
          <thead class="bg-gray-50">
          <tr>
            <th class="border px-2 py-1">رقم القيد</th>
            <th class="border px-2 py-1">الاسم</th>
            <th class="border px-2 py-1">القسم</th>
            <th class="border px-2 py-1">الحالة</th>
          </tr>
          </thead>
          <tbody>
          <template x-for="s in namesList" :key="s.student_number">
            <tr class="odd:bg-gray-50">
              <td class="border px-2 py-1" x-text="s.student_number"></td>
              <td class="border px-2 py-1" x-text="s.name"></td>
              <td class="border px-2 py-1" x-text="s.department"></td>
              <td class="border px-2 py-1">
                <span class="px-2 py-0.5 rounded text-xs" :class="s.status==='graduated' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'" x-text="statusLabel(s.status)"></span>
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">النشاط الحديث</h2>
        <div class="flex items-center gap-2">
          <input type="text" class="border rounded px-3 py-1 text-sm" placeholder="بحث بالاسم/الرقم" x-model.trim="studentsSearch" @input.debounce.300="applyFilter">
          <select x-model="statusFilter" @change="applyFilter" class="border rounded px-3 py-1 text-sm">
            <option value="all">الكل</option>
            <option value="active">نشط</option>
            <option value="graduated">خريج</option>
          </select>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
          <thead class="bg-gray-50">
          <tr>
            <th class="border px-2 py-1">رقم القيد</th>
            <th class="border px-2 py-1">الاسم</th>
            <th class="border px-2 py-1">القسم</th>
            <th class="border px-2 py-1">الحالة</th>
            <th class="border px-2 py-1">التاريخ</th>
          </tr>
          </thead>
          <tbody>
          <template x-for="item in filteredLatest" :key="item.student_number">
            <tr class="odd:bg-gray-50">
              <td class="border px-2 py-1" x-text="item.student_number"></td>
              <td class="border px-2 py-1" x-text="item.name"></td>
              <td class="border px-2 py-1" x-text="item.department"></td>
              <td class="border px-2 py-1" x-text="statusLabel(item.status)"></td>
              <td class="border px-2 py-1" x-text="item.enrolled_at"></td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('interactiveDashboard', () => ({
      cards: [],
      departmentPerformance: [],
      tasks: [],
      studentsDataset: [],
      namesList: [],
      namesDept: '',
      departments: [],
      statusCounts: { active: 0, graduated: 0 },
      filteredLatest: [],
      statusFilter: 'all',
      studentsSearch: '',

      init() {
        const s = { students: 1240, graduates: 320, teachers: 82, courses: 148 };
        this.cards = [
          this.card('students','عدد الطلاب', s.students, '+4.1%', 'text-blue-600', 80),
          this.card('graduates','عدد الخريجين', s.graduates, '+2.3%', 'text-green-600', 65),
          this.card('teachers','أعضاء التدريس', s.teachers, '+1.0%', 'text-amber-600', 55),
          this.card('courses','عدد المقررات', s.courses, '+0.6%', 'text-indigo-600', 45),
        ];

        this.departmentPerformance = [
          { name:'هندسة كهربائية', rate:84, color:'bg-green-500' },
          { name:'علوم حاسوب', rate:78, color:'bg-blue-500' },
          { name:'هندسة ميكانيك', rate:71, color:'bg-amber-500' },
          { name:'تقنية معلومات', rate:66, color:'bg-purple-500' },
        ];

        this.tasks = [
          { title:'مراجعة توزيع المواد', due:'غداً', note:'تحديث الفصل القادم', color:'bg-blue-600' },
          { title:'اعتماد نتائج فصل ربيع', due:'بعد 3 أيام', note:'تحقق من المواد المتأخرة', color:'bg-amber-600' },
          { title:'تحديث بيانات الخريجين', due:'هذا الأسبوع', note:'مطابقة السجلات النهائية', color:'bg-green-600' },
        ];

        const latest = [
          { student_number:'2025-001', name:'آمنة علي',   department:'هندسة كهربائية', status:'active',    enrolled_at:'2025-01-10' },
          { student_number:'2025-010', name:'محمد عمر',   department:'علوم حاسوب',     status:'active',    enrolled_at:'2025-01-11' },
          { student_number:'2024-075', name:'سارة محمود', department:'هندسة ميكانيك',  status:'graduated', enrolled_at:'2024-12-28' },
        ];
        this.filteredLatest = latest;

        this.studentsDataset = [
          { student_number:'2025-001', name:'آمنة علي',   department:'هندسة كهربائية', status:'active' },
          { student_number:'2025-010', name:'محمد عمر',   department:'علوم حاسوب',     status:'active' },
          { student_number:'2024-075', name:'سارة محمود', department:'هندسة ميكانيك',  status:'graduated' },
          { student_number:'2025-020', name:'ليلى يوسف',  department:'علوم حاسوب',     status:'active' },
          { student_number:'2024-110', name:'هاجر أحمد',  department:'هندسة كهربائية', status:'graduated' },
        ];
        this.computeStudentStats();
      },

      card(key, label, value, trend, trendClass, progress) {
        return { key, label, value: Number(value).toLocaleString('ar-LY'), trend, trendClass, progress: progress || 0, progressClass: key==='graduates'?'bg-green-500':(key==='teachers'?'bg-amber-500':'bg-blue-500') };
      },
      shuffleTasks(){ this.tasks = this.tasks.slice().reverse(); },
      computeStudentStats(){
        const deptMap = {}; const status = { active:0, graduated:0 };
        this.studentsDataset.forEach(s => { deptMap[s.department]=(deptMap[s.department]||0)+1; status[s.status]=(status[s.status]||0)+1; });
        this.countsByDepartment = Object.keys(deptMap).map(k => ({ name:k, count:deptMap[k] }));
        this.statusCounts = status; this.departments = Object.keys(deptMap); this.applyNamesFilter();
      },
      applyNamesFilter(){ this.namesList = this.studentsDataset.filter(s => !this.namesDept || s.department === this.namesDept); },
      applyFilter(){
        const term = this.studentsSearch.trim().toLowerCase();
        this.filteredLatest = this.filteredLatest.filter(item => {
          const tMatch = !term || (String(item.name||'').toLowerCase().includes(term) || String(item.student_number||'').toLowerCase().includes(term));
          const sMatch = this.statusFilter==='all' || item.status===this.statusFilter; return tMatch && sMatch;
        });
      },
      statusLabel(s){ return s==='graduated' ? 'خريج' : 'نشط'; }
    }));
  });
</script>
@endsection
