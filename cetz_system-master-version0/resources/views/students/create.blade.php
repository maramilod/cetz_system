@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">إضافة طالب جديد</h2>

  @if(session('success'))
      <div class="bg-green-200 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
  @endif

  <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label>الاسم الكامل</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label>الجنسية</label>
            <input type="text" name="nationality" value="{{ old('nationality') }}" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label>الجنس</label>
            <select name="gender" class="w-full border p-2 rounded" required>
                <option value="ذكر" {{ old('gender')=='ذكر' ? 'selected' : '' }}>ذكر</option>
                <option value="انثى" {{ old('gender')=='انثى' ? 'selected' : '' }}>أنثى</option>
            </select>
        </div>

<div>
    <label>القسم</label>
    <select name="department_id" id="department" class="w-full border p-2 rounded" required>
    <option value="">-- اختر القسم --</option>
   @foreach($departments as $d)
    <option value="{{ $d->id }}" data-is-general="{{ $d->is_general }}">
        {{ $d->name }}
    </option>
@endforeach

</select>

</div>


<div id="section-wrapper" style="display:none;">
    <label>الشعبة</label>
    <select name="section_id" id="section" class="w-full border p-2 rounded">
        <option value="">-- اختر الشعبة --</option>
        @foreach($sections as $s)
            <option value="{{ $s->id }}" data-department="{{ $s->department_id }}">
                {{ $s->name }}
            </option>
        @endforeach
    </select>
</div>

<script>
document.getElementById('department').addEventListener('change', function () {
    const hasSections = this.selectedOptions[0].dataset.hasSections;
    document.getElementById('section-box').style.display =
        hasSections == 1 ? 'block' : 'none';
});
</script>
<script>
document.getElementById('department').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const isGeneral = selectedOption.getAttribute('data-is-general');

    const sectionWrapper = document.getElementById('section-wrapper');
    const sectionSelect  = document.getElementById('section');

    // إذا كان القسم عام ➜ أخفِ الشعبة
    if (isGeneral == 1) {
        sectionWrapper.style.display = 'none';
        sectionSelect.value = '';
    } else {
        sectionWrapper.style.display = 'block';

        // فلترة الشعب حسب القسم
        const departmentId = this.value;
        Array.from(sectionSelect.options).forEach(option => {
            if (!option.value) return;

            option.style.display =
                option.getAttribute('data-department') == departmentId
                ? 'block'
                : 'none';
        });
    }
});
</script>


       

        <div>
            <label>سنة التسجيل</label>
            <input type="number" name="registration_year" value="{{ old('registration_year') }}" class="w-full border p-2 rounded" required>
        </div>

           <!-- الفصل الدراسي -->
      <div>
  <label class="block text-sm">الفصل الدراسي</label>
  <select name="academic_term" class="w-full border rounded p-2" required>
    <option value="">-- اختر الفصل --</option>
    <option value="fall">خريفي</option>
    <option value="spring">ربيعي</option>
  </select>
</div>


        <div>
            <label>الرقم الجامعي</label>
            <input type="text" name="student_number" value="{{ old('student_number') }}" class="w-full border p-2 rounded" >
        </div>

        <div>
          <label class="block text-sm">الرقم اليدوي</label>
          <input name="manual_number" class="w-full border rounded p-2" placeholder="أدخل الرقم اليدوي">
        </div>

        <div>
            <label>الرقم الوطني</label>
            <input type="text" name="national_id" value="{{ old('national_id') }}" class="w-full border p-2 rounded" >
        </div>

         <div>
          <label class="block text-sm">رقم جواز السفر</label>
          <input name="passport_number" class="w-full border rounded p-2" placeholder="أدخل رقم الجواز">
        </div>

        <div>
            <label>تاريخ الميلاد</label>
            <input type="date" name="dob" value="{{ old('dob') }}" class="w-full border p-2 rounded" required>
        </div>

         <div>
          <label class="block text-sm">اسم المصرف</label>
          <input name="bank_name" class="w-full border rounded p-2" placeholder="مثلاً مصرف الجمهورية">
        </div>

        <!-- رقم الحساب -->
        <div>
          <label class="block text-sm">رقم الحساب المصرفي</label>
          <input name="account_number" class="w-full border rounded p-2" placeholder="أدخل رقم الحساب">
        </div>

        <!-- اسم الأم -->
        <div>
          <label class="block text-sm">اسم الأم</label>
          <input name="mother_name" class="w-full border rounded p-2" placeholder="ادخل اسم الأم">
        </div>

        <!-- قيد الكتيب -->
        <div>
          <label class="block text-sm">قيد الكتيب</label>
          <input name="family_record" class="w-full border rounded p-2" placeholder="مثلاً 123456">
        </div>

        <div class="md:col-span-2">
            <label>الصورة الشخصية</label>
            <input type="file" name="photo" class="w-full border p-2 rounded">
        </div>

    </div>

    <div class="mt-4 flex justify-end gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ الطالب</button>
    </div>
  </form>
</div>
@endsection
