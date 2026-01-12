@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">إضافة شعبة جديدة</h1>

    <form action="{{ route('sections.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="department_id" value="{{ $departmentId ?? '' }}">

        <div>
            <label class="block text-sm font-medium">اسم الشعبة</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-2" required>
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">حفظ</button>
            <a href="{{ route('departments.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">رجوع</a>
        </div>
    </form>
</div>
@endsection
