@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">إضافة توزيع مادة جديد</h2>

  <div class="bg-white p-6 rounded-lg shadow-sm"
       x-data="teachingForm()"
       x-init="init()">

    <form action="{{ route('teaching-assignments.store') }}" method="POST" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- الشعبة -->
        <div>
          <label class="block text-sm">الشعبة</label>
          <select x-model="selectedSection" @change="updateCourses()"
                  class="w-full border rounded p-2">
            <option value="">اختر شعبة</option>
            @php $sectionsAdded = []; @endphp
            @foreach($courseOfferings as $co)
              @if(!in_array($co->section->id, $sectionsAdded))
                <option value="{{ $co->section->id }}">{{ $co->section->name }}</option>
                @php $sectionsAdded[] = $co->section->id; @endphp
              @endif
            @endforeach
          </select>
        </div>

        <!-- المادة + السيمستر -->
        <div>
          <label class="block text-sm">المادة / السيمستر</label>

          <div class="relative">

            <input type="text"
                   x-model="search"
                   @input="filterCourses()"
                   placeholder="ابحث عن المادة..."
                   class="w-full border rounded p-2">

            <div class="absolute z-50 bg-white border w-full max-h-48 overflow-y-auto mt-1"
                 x-show="search.length > 0 && filteredCourses.length > 0">
              <template x-for="co in filteredCourses" :key="co.id">
                <div @click="selectCourse(co)"
                     class="p-2 hover:bg-gray-100 cursor-pointer"
                     x-text="co.name + ' - سيمستر ' + co.semester">
                </div>
              </template>
            </div>

            <input type="hidden" name="course_offering_id"
                   :value="selectedCourseOffering">

          </div>
        </div>

        <!-- الأستاذ -->
        <div>
          <label class="block text-sm">أستاذ المادة</label>

          <div class="relative">

            <input type="text"
                   x-model="teacherSearch"
                   @input="filterTeachers()"
                   placeholder="ابحث عن الأستاذ..."
                   class="w-full border rounded p-2">

            <div class="absolute z-50 bg-white border w-full max-h-48 overflow-y-auto mt-1"
                 x-show="teacherSearch.length > 0 && filteredTeachers.length > 0">
              <template x-for="t in filteredTeachers" :key="t.id">
                <div @click="selectTeacher(t)"
                     class="p-2 hover:bg-gray-100 cursor-pointer"
                     x-text="t.name">
                </div>
              </template>
            </div>

            <input type="hidden" name="teacher_id"
                   :value="selectedTeacher">

          </div>
        </div>

        <!-- الدور -->
        <div>
          <label class="block text-sm">دور الأستاذ</label>
          <select name="role" class="w-full border rounded p-2">
            <option value="نظري">نظري</option>
            <option value="عملي">عملي</option>
            <option value="مساعد">مساعد</option>
          </select>
        </div>

      </div>

      <div class="flex items-center gap-3 justify-end">
        <a href="{{ route('teaching-assignments.index') }}"
           class="px-4 py-2 bg-gray-100 rounded">إلغاء</a>

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded">
          حفظ
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function teachingForm() {
    return {
        selectedSection: '',
        selectedCourseOffering: '',

        search: '',
        filteredCourses: [],

        teacherSearch: '',
        selectedTeacher: '',
        filteredTeachers: [],

        allCourses: [
            @foreach($courseOfferings as $co)
            {
                id: {{ $co->id }},
                name: '{{ $co->course->name }} ({{ $co->course->course_code }})',
                section_id: {{ $co->section->id }},
                semester: {{ $co->semester->semester_number }},
            },
            @endforeach
        ],

        allTeachers: [
            @foreach($teachers as $t)
                @if($t->active)
                {
                    id: {{ $t->id }},
                    name: '{{ $t->full_name }}'
                },
                @endif
            @endforeach
        ],

        init() {
            this.filteredTeachers = this.allTeachers;
        },

        updateCourses() {
            this.search = '';
            this.selectedCourseOffering = '';
            this.filteredCourses = this.allCourses.filter(c =>
                c.section_id == this.selectedSection
            );
        },

        filterCourses() {
            if (!this.selectedSection) {
                this.filteredCourses = [];
                return;
            }

            this.filteredCourses = this.allCourses.filter(c =>
                c.section_id == this.selectedSection &&
                c.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        selectCourse(co) {
            this.selectedCourseOffering = co.id;
            this.search = co.name + ' - سيمستر ' + co.semester;
            this.filteredCourses = [];
        },

        filterTeachers() {
            this.filteredTeachers = this.allTeachers.filter(t =>
                t.name.toLowerCase().includes(this.teacherSearch.toLowerCase())
            );
        },

        selectTeacher(t) {
            this.selectedTeacher = t.id;
            this.teacherSearch = t.name;
            this.filteredTeachers = [];
        }
    }
}
</script>

@endsection