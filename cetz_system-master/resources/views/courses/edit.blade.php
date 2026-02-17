@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow"
     x-data="courseForm()"  x-init="initExistingData()">

    <h1 class="text-2xl font-bold mb-4">تعديل مادة</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('courses.update', $course->id) }}"
          method="POST"
          class="space-y-6">
        @csrf
        @method('PUT')

        <!-- اسم المادة -->
        <div>
            <label class="block text-sm mb-1">اسم المادة</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $course->name) }}"
                   class="border rounded w-full px-3 py-2"
                   required>
        </div>

        <!-- رمز المادة -->
        <div>
            <label class="block text-sm mb-1">رمز المادة</label>
            <input type="text"
                   name="course_code"
                   value="{{ old('course_code', $course->course_code) }}"
                   class="border rounded w-full px-3 py-2"
                   required>
        </div>

        <!-- عدد الساعات -->
        <div>
            <label class="block text-sm mb-1">عدد الساعات</label>
            <input type="number"
                   name="hours"
                   value="{{ old('hours', $course->hours) }}"
                   min="0"
                   class="border rounded w-full px-3 py-2"
                   required>
        </div>

        <!-- عدد الوحدات -->
        <div>
            <label class="block text-sm mb-1">عدد الوحدات</label>
            <input type="number"
                   name="units"
                   value="{{ old('units', $course->units) }}"
                   min="0"
                   class="border rounded w-full px-3 py-2"
                   required>
        </div>

        <!-- المادة السابقة -->
        <div>
            <label class="block text-sm mb-1">المادة السابقة (اختياري)</label>
            <select name="prerequisite_course_id"
                    class="border rounded w-full px-3 py-2">
                <option value="">لا توجد مادة سابقة</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}"
                        @selected($course->prerequisite_course_id == $c->id)>
                        {{ $c->name }} ({{ $c->course_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- عملي -->
        <div class="flex items-center gap-2">
            <input type="checkbox"
                   name="has_practical"
                   value="1"
                   @checked($course->has_practical)>
            <span class="text-sm">تحتوي على جزء عملي</span>
        </div>
  <!-- زر حفظ البيانات الأساسية فقط -->
    <button type="submit"
            name="action"
            value="basic"
            formaction="{{ route('courses.updateBasic', $course->id) }}"
            class="bg-green-600 text-white px-6 py-2 rounded">
        حفظ البيانات الأساسية فقط
    </button>
        <!-- فترة الدراسة -->
        <div>
            <label class="block text-sm mb-1">تاريخ بدء الفصل</label>
            <select name="start_date"
                    x-model="startDate"
                    @change="fetchSemestersByRange"
                    class="border rounded w-full px-3 py-2">
                <option value="">اختر تاريخ البداية</option>
                @foreach($startDates as $date)
                    <option value="{{ $date }}">{{ $date }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm mb-1">تاريخ نهاية الفصل</label>
            <select name="end_date"
                    x-model="endDate"
                    @change="fetchSemestersByRange"
                    class="border rounded w-full px-3 py-2">
                <option value="">اختر تاريخ النهاية</option>
                @foreach($endDates as $date)
                    <option value="{{ $date }}">{{ $date }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="periodLabel" class="mt-2">
            <label class="block text-sm mb-1">الفترة الدراسية</label>
            <input type="text"
                   x-model="periodLabel"
                   readonly
                   class="border rounded w-full px-3 py-2 bg-gray-100">
        </div>

        <!-- الأقسام -->
        <div>
            <h2 class="text-lg font-semibold mb-3">
                الأقسام والشعب والسيمسترات
            </h2>

            @foreach($departments as $dept)
                <div class="border rounded p-4 mb-4">

                    <!-- اختيار القسم -->
                    <label class="flex items-center gap-2">
                        <input type="checkbox"
                               value="{{ $dept->id }}"
                               data-section-id="{{ $dept->is_general ? $dept->sections->first()->id : '' }}"
                               x-model="selectedDepartments"
                               @change="handleGeneralDept({{ $dept->id }}, {{ $dept->is_general ? 'true' : 'false' }})">

                        <strong>{{ $dept->name }}</strong>

                        @if($dept->is_general)
                            <span class="text-xs text-green-600">(عام)</span>
                        @endif
                    </label>

                    <!-- الشعب فقط إذا لم يكن عام -->
                    @if(!$dept->is_general)
                        @foreach($sections->where('department_id', $dept->id) as $section)
                            <div class="ml-4 mt-3">

                                <label class="flex items-center gap-2">
                                    <input type="checkbox"
                                           @click="initSection('{{ $section->id }}')"
                                           x-model="selectedSections['{{ $section->id }}']?.selected">
                                    {{ $section->name }}
                                </label>

                                <div x-show="selectedSections['{{ $section->id }}']?.selected"
                                     class="ml-6 mt-2">

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
                    @endif

                </div>
            @endforeach
        </div>

        <input type="hidden"
               name="selectedSections"
               :value="JSON.stringify(selectedSections)">

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded">
            حفظ جميع التعديلات
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

        /* =========================
           تحميل البيانات القديمة
        ========================== */
        initExistingData() {

            const existing = @json(
                $course->offerings->load('semester')->groupBy('section_id')
            );

            for (const sectionId in existing) {

                this.selectedSections[sectionId] = {
                    selected: true,
                    semesters: {}
                };

                existing[sectionId].forEach(off => {
                    this.selectedSections[sectionId]
                        .semesters[off.semester.semester_number] = true;
                });
            }

        },

        /* =========================
           جلب السيمسترات حسب الفترة
        ========================== */
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

        /* =========================
           تحديد مدى السيمسترات
        ========================== */
        buildSemesterRange() {

            this.allowedSemesters = [];

            if (this.degreeType === 'بكالوريوس') {
                for (let i = 2; i <= 8; i++) {
                    this.allowedSemesters.push(i);
                }
            }

            if (this.degreeType === 'دبلوم') {
                for (let i = 2; i <= 6; i++) {
                    this.allowedSemesters.push(i);
                }
            }

        },

        /* =========================
           عرض الفترة
        ========================== */
        buildPeriodLabel() {

            if (this.semesters.length === 0) {
                this.periodLabel = '';
                return;
            }

            const first = this.semesters[0];
            const last  = this.semesters[this.semesters.length - 1];

            const startYear = new Date(first.start_date).getFullYear();
            const endYear   = new Date(last.end_date).getFullYear();

            this.periodLabel =
                `${endYear} ${last.term_type} → ${startYear} ${first.term_type}`;
        },

        /* =========================
           تهيئة شعبة عادية
        ========================== */
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

        /* =========================
           التعامل مع القسم العام
        ========================== */
        handleGeneralDept(deptId, isGeneral) {

            if (!isGeneral) return;

            const checkbox = document.querySelector(`input[value="${deptId}"]`);
            const sectionId = checkbox.dataset.sectionId;

            if (!sectionId) return;

            if (checkbox.checked) {

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
