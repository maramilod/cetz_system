
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">إضافة طالب جديد</h2>

  <div class="bg-white p-6 rounded-lg shadow-sm">
    <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
      {{-- @csrf --}}

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- الاسم -->
        <div>
          <label class="block text-sm">الاسم الكامل</label>
          <input name="name" class="w-full border rounded p-2" placeholder="ادخل اسم الطالب">
        </div>

        <!-- الجنسية -->
        <div>
          <label class="block text-sm font-medium text-gray-700">الجنسية</label>
          <select name="nationality" class="w-full border rounded p-2 mt-1">
            <option value="ليبيا" selected>ليبيا</option>
            <option value="مصر">مصر</option>
            <option value="تونس">تونس</option>
            <option value="الجزائر">الجزائر</option>
            <option value="المغرب">المغرب</option>
            <option value="موريتانيا">موريتانيا</option>
            <option value="السودان">السودان</option>
            <option value="فلسطين">فلسطين</option>
            <option value="الأردن">الأردن</option>
            <option value="سوريا">سوريا</option>
            <option value="لبنان">لبنان</option>
            <option value="العراق">العراق</option>
            <option value="اليمن">اليمن</option>
            <option value="السعودية">السعودية</option>
            <option value="الإمارات">الإمارات</option>
            <option value="قطر">قطر</option>
            <option value="الكويت">الكويت</option>
            <option value="البحرين">البحرين</option>
            <option value="عُمان">عُمان</option>
            <option value="جيبوتي">جيبوتي</option>
            <option value="جزر القمر">جزر القمر</option>
          </select>
        </div>


        <!-- الجنس -->
        <div>
          <label class="block text-sm">الجنس</label>
          <select name="gender" class="w-full border rounded p-2">
            <option value="male">ذكر</option>
            <option value="female">أنثى</option>
          </select>
        </div>

        <!-- القسم -->
        <div>
          <label class="block text-sm">القسم</label>
          <select name="department_id" class="w-full border rounded p-2">
            @foreach($departments as $d)
              <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- سنة التسجيل -->
        <div>
          <label class="block text-sm">سنة التسجيل</label>
          <input name="registration_year" type="number" class="w-full border rounded p-2" placeholder="مثلاً 2025">
        </div>

        <!-- الفصل الدراسي -->
        <div>
          <label class="block text-sm">الفصل الدراسي</label>
          <input name="semester" class="w-full border rounded p-2" placeholder="الفصل الأول / الثاني">
        </div>

        <!-- الرقم الجامعي -->
        <div>
          <label class="block text-sm">الرقم الجامعي</label>
          <input name="student_number" class="w-full border rounded p-2" placeholder="2025-001">
        </div>

        <!-- الرقم اليدوي -->
        <div>
          <label class="block text-sm">الرقم اليدوي</label>
          <input name="manual_number" class="w-full border rounded p-2" placeholder="أدخل الرقم اليدوي">
        </div>

        <!-- الرقم الوطني -->
        <div>
          <label class="block text-sm">الرقم الوطني</label>
          <input name="national_id" class="w-full border rounded p-2" placeholder="أدخل الرقم الوطني">
        </div>

        <!-- جواز السفر -->
        <div>
          <label class="block text-sm">رقم جواز السفر</label>
          <input name="passport_number" class="w-full border rounded p-2" placeholder="أدخل رقم الجواز">
        </div>

        <!-- تاريخ الميلاد -->
        <div>
          <label class="block text-sm">تاريخ الميلاد</label>
          <input name="dob" type="date" class="w-full border rounded p-2">
        </div>

        <!-- المصرف -->
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

        <!-- الصورة الشخصية -->
        <div class="md:col-span-2">
          <label class="block text-sm">الصورة الشخصية</label>
          <input name="photo" type="file" class="w-full">
        </div>

      </div>

      <div class="flex items-center gap-3 justify-end">
        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-100 rounded">إلغاء</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
      </div>
    </form>
  </div>
</div>
@endsection
