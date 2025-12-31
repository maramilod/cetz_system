@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">تعديل توزيع مادة</h2>

  <div class="bg-white p-6 rounded-lg shadow-sm" x-data="teachingForm()">

    <form action="{{ route('teaching-assignments.update', $assignment->id) }}" method="POST" class="space-y-4">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- الشعبة -->
        <div>
          <label class="block text-sm">الشعبة</label>
          <select x-model="selectedSection" @change="updateCourses()" class="w-full border rounded p-2">
            <option value="">اختر شعبة</option>
            @php $sectionsAdded = []; @endphp
            @foreach($courseOfferings as $co)
              @if(!in_array($co->section->id, $sectionsAdded))
                <option value="{{ $co->section->id }}" @if($assignment->courseOffering->section->id == $co->section->id) selected @endif>{{ $co->section->name }}</option>
                @php $sectionsAdded[] = $co->section->id; @endphp
              @endif
            @endforeach
          </select>
        </div>

        <!-- المادة + السيمستر في فلتر واحد -->
        <div>
          <label class="block text-sm">المادة / السيمستر</label>
          <select name="course_offering_id" x-model="selectedCourseOffering" class="w-full border rounded p-2">
            <option value="">اختر المادة والسيمستر</option>
            <template x-for="co in filteredCourses" :key="co.id">
              <option :value="co.id" :selected="co.id == {{ $assignment->course_offering_id }}" x-text="co.name + ' - سيمستر ' + co.semester"></option>
            </template>
          </select>
        </div>

        <!-- الأستاذ -->
        <div>
          <label class="block text-sm">أستاذ المادة</label>
          <select name="teacher_id" class="w-full border rounded p-2">
            @foreach($teachers as $t)
              <option value="{{ $t->id }}" @if($assignment->teacher_id == $t->id) selected @endif>{{ $t->full_name }}</option>
            @endforeach
          </select>
        </div>

        <!-- الدور -->
        <div>
          <label class="block text-sm">دور الأستاذ</label>
          <select name="role" class="w-full border rounded p-2">
            @php
              $hasPractical = $assignment->courseOffering->course->has_practical ?? 0;
            @endphp
            <option value="نظري" @if($assignment->role == 'نظري') selected @endif>نظري</option>
            @if($hasPractical)
              <option value="عملي" @if($assignment->role == 'عملي') selected @endif>عملي</option>
            @endif
            <option value="مساعد" @if($assignment->role == 'مساعد') selected @endif>مساعد</option>
          </select>
        </div>

      </div>

      <div class="flex items-center gap-3 justify-end mt-4">
        <a href="{{ route('teaching-assignments.index') }}" class="px-4 py-2 bg-gray-100 rounded">إلغاء</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ التعديلات</button>
      </div>
    </form>

  </div>
</div>

<script>
function teachingForm() {
    return {
        selectedSection: '{{ $assignment->courseOffering->section->id }}',
        selectedCourseOffering: '{{ $assignment->course_offering_id }}', // المادة + السيمستر المختار
        filteredCourses: [],
        allCourses: [
            @foreach($courseOfferings as $co)
                {id: {{ $co->id }}, course_id: {{ $co->course->id }}, name: '{{ $co->course->name }} ({{ $co->course->course_code }})', section_id: {{ $co->section->id }}, semester: {{ $co->semester->semester_number }} },
            @endforeach
        ],

        updateCourses() {
            this.filteredCourses = this.allCourses.filter(c => c.section_id == this.selectedSection);
            // إذا المادة الحالية موجودة في الفلتر، اتركها مختارة
            if (!this.filteredCourses.find(c => c.id == this.selectedCourseOffering)) {
                this.selectedCourseOffering = '';
            }
        },

        init() {
            this.updateCourses();
        }
    }
}
</script>

@endsection
