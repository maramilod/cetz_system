@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow"
     x-data="courseForm()">

    <h1 class="text-2xl font-bold mb-6">إضافة مادة جديدة</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST" class="space-y-6">
        @csrf
     <!-- اسم المادة -->
        <div>
            <label class="block text-sm mb-1">اسم المادة</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-2" required>
        </div>

      <!-- رمز المادة -->
<div>
    <label class="block text-sm mb-1">رمز المادة</label>
    <input type="text" name="course_code" class="border rounded w-full px-3 py-2" required>
</div>

<!-- عدد الساعات -->
<div>
    <label class="block text-sm mb-1">عدد الساعات</label>
    <input type="number" name="hours" class="border rounded w-full px-3 py-2" value="0" required>
</div>

<!-- عدد الوحدات -->
<div>
    <label class="block text-sm mb-1">عدد الوحدات</label>
    <input type="number" name="units" class="border rounded w-full px-3 py-2" value="0" required>
</div>

<!-- المادة السابقة -->
<div>
    <label class="block text-sm mb-1">المادة السابقة (اختياري)</label>
    <select name="prerequisite_course_id" class="border rounded w-full px-3 py-2">
        <option value="">لا توجد مادة سابقة</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->course_code }})</option>
        @endforeach
    </select>
</div>


        <!-- هل لها عملي -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="has_practical" value="1" x-model="hasPractical">
            <span class="text-sm">تحتوي على جزء عملي</span>
        </div>

                <!-- تواريخ الفصل -->

    <div>
    <label class="block text-sm mb-1">تاريخ بدء الفصل</label>
   <select name="start_date"
        x-model="startDate"
        @change="fetchSemestersByRange"
        class="border rounded w-full px-3 py-2"
        required>


        <option value="">اختر تاريخ البداية</option>
        @foreach($startDates as $date)
            <option value="{{ $date }}">
                {{ $date }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm mb-1">تاريخ نهاية الفصل</label>
   <select name="end_date"
        x-model="endDate"
        @change="fetchSemestersByRange"
        class="border rounded w-full px-3 py-2"
        required>

        <option value="">اختر تاريخ النهاية</option>
        @foreach($endDates as $date)
            <option value="{{ $date }}">
                {{ $date }}
            </option>
        @endforeach
    </select>
</div>
<div x-show="periodLabel" class="mt-4">
    <label class="block text-sm mb-1">الفترة الدراسية</label>
    <input type="text"
           x-model="periodLabel"
           readonly
           class="border rounded w-full px-3 py-2 bg-gray-100 cursor-not-allowed">
</div>




  <!-- الأقسام والشعب والسيمسترات -->
        <div>
            <h2 class="text-lg font-semibold mb-3">الأقسام والشعب والسيمسترات</h2>

            @foreach($departments as $dept)
            <div class="border rounded p-4 mb-4">

             <!-- القسم -->
<label class="flex items-center gap-2">
    <input type="checkbox"
       value="{{ $dept->id }}"
       data-section-id="{{ $dept->is_general ? $dept->sections->where('name','العام')->first()?->id : '' }}"
         @click="initSection('{{ $dept->is_general ? $dept->sections->where('name','العام')->first()?->id : '' }}')"
       x-model="selectedDepartments"
       @change="handleGeneralDept({{ $dept->id }}, {{ $dept->is_general ? 'true' : 'false' }})">

    <strong>{{ $dept->name }}</strong>

    @if($dept->is_general)
        <span class="text-xs text-green-600">(عام)</span>
    @endif
</label>

                <!-- الشعب للأقسام العادية -->
                @if(!$dept->is_general)
                <div x-show="selectedDepartments.includes('{{ $dept->id }}')" class="mt-4">
                    <label class="block text-sm mb-2">الشعب</label>

                    @foreach($sections->where('department_id', $dept->id) as $section)
                    <div class="ml-4 mb-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox"
                                  @click="initSection('{{ $section->id }}')"
x-model="selectedSections['{{ $section->id }}'].selected">

                            {{ $section->name }}
                        </label>

                        <div x-show="selectedSections['{{ $section->id }}']?.selected" class="ml-6 mt-2">
    <label class="block text-sm mb-1">السيمسترات</label>

    <template x-for="sem in allowedSemesters" :key="sem">
        <div class="flex items-center gap-2 mb-1">
            <input type="checkbox"
                 x-model="selectedSections['{{ $section->id }}'].semesters[sem]">
            سيمستر <span x-text="sem"></span>
        </div>
    </template>
</div>

                    </div>
                    @endforeach
                </div>
                @endif

            </div>
            @endforeach
        </div>
          <!-- input مخفي لإرسال الشعب المختارة -->
        <input type="hidden" name="selectedSections" :value="JSON.stringify(selectedSections)">

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">
            حفظ المادة
        </button>

    </form>
</div>

<script>
function courseForm() {
    return {
        selectedDepartments: [],
        selectedSections: {},
        startDate: '',
        endDate: '',
        semesters: [],
        allowedSemesters: [],
        degreeType: null,
        periodLabel: '',
        hasPractical: false,

        fetchSemestersByRange() {
            if (!this.startDate || !this.endDate) return;
            fetch(`/semesters/by-date-range?start_date=${this.startDate}&end_date=${this.endDate}`)
                .then(res => res.json())
                .then(data => {
                    this.semesters = data.semesters ?? [];
                    if (this.semesters.length > 0) {
                        this.degreeType = this.semesters[0].degree_type;
                        this.buildSemesterRange();
                        this.buildPeriodLabel();
                    }
                });
        },

        buildSemesterRange() {
            this.allowedSemesters = [];
            if (this.degreeType === 'بكالوريوس') {
                for (let i = 2; i <= 8; i++) this.allowedSemesters.push(i);
            }
            if (this.degreeType === 'دبلوم') {
                for (let i = 2; i <= 6; i++) this.allowedSemesters.push(i);
            }

            // تحديث الشعب العادية
            for (const sectionId in this.selectedSections) {
                if (!this.selectedSections[sectionId].semesters) this.selectedSections[sectionId].semesters = {};
                this.allowedSemesters.forEach(s => {
                    if (!(s in this.selectedSections[sectionId].semesters)) {
                        this.selectedSections[sectionId].semesters[s] = false;
                    }
                });
            }
        },

        buildPeriodLabel() {
            if (this.semesters.length === 0) {
                this.periodLabel = '';
                return;
            }
            const first = this.semesters[0];
            const last = this.semesters[this.semesters.length - 1];
            const startYear = new Date(first.start_date).getFullYear();
            const endYear = new Date(last.end_date).getFullYear();
            this.periodLabel = `${endYear} ${last.term_type} → ${startYear} ${first.term_type}`;
        },

        initSection(sectionId) {
            if (!this.selectedSections[sectionId]) {
                this.selectedSections[sectionId] = {
                    selected: true,
                    semesters: {}
                };
                this.allowedSemesters.forEach(s => {
                    this.selectedSections[sectionId].semesters[s] = false;
                });
            }
        },

        handleGeneralDept(deptId, isGeneral) {
            if (!isGeneral) return;

            const checkbox = document.querySelector(`input[value="${deptId}"]`);
            const sectionId = checkbox.dataset.sectionId;
            if (!sectionId) return;

            if (checkbox.checked) {
                // الشعبة العامة دائمًا محددة مع سيمستر 1
                this.selectedSections[sectionId] = {
                    selected: true,
                    semesters: { 1: true }
                };
            } else {
                delete this.selectedSections[sectionId];
            }
        }
    }
}

</script>


@endsection
