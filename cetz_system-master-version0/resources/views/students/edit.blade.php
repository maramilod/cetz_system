@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">تعديل بيانات الطالب</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('students.update', $student->id) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4">
        @csrf
        @method('PUT')

        {{-- صورة الطالب --}}
        <div class="mb-4 text-center">
    <img id="photoPreview"
         src="{{ $student->photo 
                ? asset('storage/students/' . $student->photo) 
                : asset('images/default-user.png') }}"
         alt="صورة الطالب"
         class="w-32 h-32 object-cover rounded-full mx-auto border">
</div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- الاسم --}}
            <div>
                <label>الاسم الكامل</label>
                <input type="text" name="full_name"
                       value="{{ old('full_name', $student->full_name) }}"
                       class="w-full border p-2 rounded" required>
            </div>
            <div>
    <label>اسم الأم</label>
    <input type="text"
           name="mother_name"
           value="{{ old('mother_name', $student->mother_name) }}"
           class="w-full border p-2 rounded">
</div>


            {{-- الجنسية --}}
            <div>
                <label>الجنسية</label>
                <input type="text" name="nationality"
                       value="{{ old('nationality', $student->nationality) }}"
                       class="w-full border p-2 rounded">
            </div>

            {{-- الجنس --}}
            <div>
                <label>الجنس</label>
                <select name="gender" class="w-full border p-2 rounded" required>
                    <option value="ذكر"
                        {{ old('gender', $student->gender) == 'ذكر' ? 'selected' : '' }}>
                        ذكر
                    </option>
                    <option value="انثى"
                        {{ old('gender', $student->gender) == 'انثى' ? 'selected' : '' }}>
                        أنثى
                    </option>
                </select>
            </div>

            {{-- القسم --}}
            <div>
                <label>القسم</label>
                <select name="department_id" id="department"
                        class="w-full border p-2 rounded" required>
                    <option value="">-- اختر القسم --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}"
                                data-is-general="{{ $d->is_general }}"
                                {{ old('department_id', $student->department_id) == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- الشعبة --}}
            <div id="section-wrapper">
                <label>الشعبة</label>
                <select name="section_id" id="section"
                        class="w-full border p-2 rounded">
                    <option value="">-- اختر الشعبة --</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}"
                                data-department="{{ $s->department_id }}"
                                {{ old('section_id', $student->section_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
    <label>تاريخ الميلاد</label>
    <input type="date" name="dob"
           value="{{ old('dob', $student->dob) }}"
           class="w-full border p-2 rounded">
</div>

            {{-- سنة التسجيل --}}
            <div>
                <label>سنة التسجيل</label>
                <input type="number" name="registration_year"
                       value="{{ old('registration_year', $student->registration_year) }}"
                       class="w-full border p-2 rounded">
            </div>

            {{-- الفصل --}}
            <div>
                <label>الفصل الدراسي</label>
                <select name="academic_term" class="w-full border p-2 rounded">
                    <option value="fall"
                        {{ old('academic_term', $student->academic_term) == 'fall' ? 'selected' : '' }}>
                        خريفي
                    </option>
                    <option value="spring"
                        {{ old('academic_term', $student->academic_term) == 'spring' ? 'selected' : '' }}>
                        ربيعي
                    </option>
                </select>
            </div>
<div>
    <label>الرقم اليدوي</label>
    <input type="text" name="manual_number"
           value="{{ old('manual_number', $student->manual_number) }}"
           class="w-full border p-2 rounded">
</div>

            {{-- الرقم الجامعي --}}
            <div>
                <label>الرقم الجامعي</label>
                <input type="text" name="student_number"
                       value="{{ old('student_number', $student->student_number) }}"
                       class="w-full border p-2 rounded">
            </div>
<div>
    <label>رقم جواز السفر</label>
    <input type="text" name="passport_number"
           value="{{ old('passport_number', $student->passport_number) }}"
           class="w-full border p-2 rounded">
</div>

            {{-- الرقم الوطني --}}
            <div>
                <label>الرقم الوطني</label>
                <input type="text" name="national_id"
                       value="{{ old('national_id', $student->national_id) }}"
                       class="w-full border p-2 rounded">
            </div>
<div>
    <label>كتيب القيد </label>
    <input type="text" name="family_record"
           value="{{ old('family_record', $student->family_record) }}"
           class="w-full border p-2 rounded">
</div>
<div>
    <label>اسم المصرف</label>
    <input type="text"
           name="bank_name"
           value="{{ old('bank_name', $student->bank_name) }}"
           class="w-full border p-2 rounded">
</div>
<div>
    <label>رقم الحساب المصرفي</label>
    <input type="text"
           name="account_number"
           value="{{ old('account_number', $student->account_number) }}"
           class="w-full border p-2 rounded">
</div>


            {{-- الصورة --}}
            <div class="md:col-span-2">
                <label>تغيير الصورة الشخصية</label>
                <input type="file" name="photo"
                       class="w-full border p-2 rounded"
                       accept="image/*"
                       onchange="previewImage(event)">
            </div>

        </div>

        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('students.index') }}"
               class="px-4 py-2 bg-gray-200 rounded">
                عودة
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>

{{-- JavaScript --}}
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('photoPreview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

// إظهار / إخفاء الشعبة حسب القسم
document.getElementById('department').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const isGeneral = selected.getAttribute('data-is-general');
    const wrapper = document.getElementById('section-wrapper');

    wrapper.style.display = isGeneral == 1 ? 'none' : 'block';
});
</script>
@endsection
