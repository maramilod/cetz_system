@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">إضافة توزيع مادة جديد</h2>

  <div class="bg-white p-6 rounded-lg shadow-sm">
    <form action="{{ route('subject-distributions.store') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm">القسم</label>
          <select name="department_id" class="w-full border rounded p-2">
            @foreach($departments as $d)
              <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm">اسم المادة</label>
          <input name="subject_name" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="block text-sm">رمز المادة</label>
          <input name="subject_code" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="block text-sm">أستاذ المادة</label>
          <select name="teacher" class="w-full border rounded p-2">
            @foreach($teachers as $t)
              <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
          </select>
        </div>


        <div>
    <label class="block text-sm">دور الأستاذ</label>
    <select name="role" class="w-full border rounded p-2">
        <option value="نظري">نظري</option>
        <option value="عملي">عملي</option>
        <option value="مساعد">مساعد</option>
    </select>
</div>

        <div>
          <label class="block text-sm">رقم الفصل</label>
          <input type="number" name="semester" class="w-full border rounded p-2" />
        </div>
      </div>

      <div class="flex items-center gap-3 justify-end">
        <a href="{{ route('subject-distributions.index') }}" class="px-4 py-2 bg-gray-100 rounded">إلغاء</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
      </div>
    </form>
  </div>
</div>
@endsection
