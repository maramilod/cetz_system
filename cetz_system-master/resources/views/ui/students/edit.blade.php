@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
  <h1 class="text-2xl font-bold mb-4">تعديل بيانات الطالب</h1>

  @if(session('error'))
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
  @endif

  <form action="{{ route('students.update', $id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- الاسم -->
      <div>
        <label class="block text-sm">الاسم الكامل</label>
        <input name="name" class="w-full border rounded p-2" value="{{ old('name', $student->name) }}" required>
      </div>

      <!-- الجنسية -->
      <div>
        <label class="block text-sm font-medium text-gray-700">الجنسية</label>
        <input name="nationality" class="w-full border rounded p-2 mt-1" value="{{ old('nationality', $student->nationality) }}">
      </div>

      <!-- الجنس -->
      <div>
        <label class="block text-sm">الجنس</label>
        <select name="gender" class="w-full border rounded p-2">
          @foreach($genders as $g)
            <option value="{{ $g }}" @selected(old('gender', $student->gender) == $g)>{{ $g }}</option>
          @endforeach
        </select>
      </div>

      <!-- القسم -->
      <div>
        <label class="block text-sm">القسم</label>
        <select name="department" class="w-full border rounded p-2">
          @foreach($departments as $d)
            <option value="{{ $d }}" @selected(old('department', $student->department) == $d)>{{ $d }}</option>
          @endforeach
        </select>
      </div>

      <!-- سنة التسجيل -->
      <div>
        <label class="block text-sm">سنة التسجيل</label>
        <input name="registration_year" type="number" class="w-full border rounded p-2" value="{{ old('registration_year', $student->registration_year) }}" placeholder="مثلاً 2025">
      </div>

      <!-- الفصل الدراسي -->
      <div>
        <label class="block text-sm">الفصل الدراسي</label>
        <input name="semester" class="w-full border rounded p-2" value="{{ old('semester', $student->semester) }}" placeholder="الفصل الأول / الثاني">
      </div>

      <!-- الرقم الجامعي -->
      <div>
        <label class="block text-sm">الرقم الجامعي</label>
        <input name="student_number" class="w-full border rounded p-2" value="{{ old('student_number', $student->student_number) }}" placeholder="2025-001">
      </div>

      <!-- الرقم اليدوي -->
      <div>
        <label class="block text-sm">الرقم اليدوي</label>
        <input name="manual_number" class="w-full border rounded p-2" value="{{ old('manual_number', $student->manual_number) }}" placeholder="أدخل الرقم اليدوي">
      </div>

      <!-- الرقم الوطني -->
      <div>
        <label class="block text-sm">الرقم الوطني</label>
        <input name="national_id" class="w-full border rounded p-2" value="{{ old('national_id', $student->national_id) }}" placeholder="أدخل الرقم الوطني">
      </div>

      <!-- جواز السفر -->
      <div>
        <label class="block text-sm">رقم جواز السفر</label>
        <input name="passport_number" class="w-full border rounded p-2" value="{{ old('passport_number', $student->passport_number ?? $student->passport) }}" placeholder="أدخل رقم الجواز">
      </div>

      <!-- تاريخ الميلاد -->
      <div>
        <label class="block text-sm">تاريخ الميلاد</label>
        <input name="dob" type="date" class="w-full border rounded p-2" value="{{ old('dob', $student->dob) }}">
      </div>

      <!-- المصرف -->
      <div>
        <label class="block text-sm">اسم المصرف</label>
        <input name="bank_name" class="w-full border rounded p-2" value="{{ old('bank_name', $student->bank_name) }}" placeholder="مثلاً مصرف الجمهورية">
      </div>

      <!-- رقم الحساب -->
      <div>
        <label class="block text-sm">رقم الحساب المصرفي</label>
        <input name="account_number" class="w-full border rounded p-2" value="{{ old('account_number', $student->account_number) }}" placeholder="أدخل رقم الحساب">
      </div>

      <!-- اسم الأم -->
      <div>
        <label class="block text-sm">اسم الأم</label>
        <input name="mother_name" class="w-full border rounded p-2" value="{{ old('mother_name', $student->mother_name) }}" placeholder="ادخل اسم الأم">
      </div>

      <!-- قيد الكتيب -->
      <div>
        <label class="block text-sm">قيد الكتيب</label>
        <input name="family_record" class="w-full border rounded p-2" value="{{ old('family_record', $student->family_record) }}" placeholder="مثلاً 123456">
      </div>

      <!-- الصورة الشخصية (اختياري، لا تُحفظ فعلياً هنا) -->
      <div class="md:col-span-2">
        <label class="block text-sm">الصورة الشخصية</label>
        <input name="photo" type="file" class="w-full">
      </div>

      <!-- الحالة -->
      <div>
        <label class="block text-sm">الحالة</label>
        <select name="status" class="w-full border rounded p-2">
          <option value="active" @selected(old('status', $student->status) == 'active')>نشط</option>
          <option value="graduated" @selected(old('status', $student->status) == 'graduated')>خريج</option>
        </select>
      </div>
    </div>

    <div class="flex items-center gap-3 justify-end">
      <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-100 rounded">إلغاء</a>
      <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">حفظ</button>
    </div>
  </form>
</div>
@endsection
