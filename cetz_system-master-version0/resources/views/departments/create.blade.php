@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">إضافة قسم جديد</h1>

    <form action="{{ route('departments.store') }}" method="POST"
          class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label class="block text-sm font-medium">اسم القسم</label>
            <input type="text" name="name"
                   class="border rounded w-full px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">نوع القسم</label>
            <select name="is_general" class="border rounded w-full px-3 py-2" required>
    <option value="">-- اختر النوع --</option>
    <option value="1">عام</option>
    <option value="0">تخصصي</option>
</select>

        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg">
            حفظ
        </button>

        <a href="{{ route('departments.index') }}"
           class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
            رجوع
        </a>
    </form>
</div>
@endsection
