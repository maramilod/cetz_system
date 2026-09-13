@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">إضافة فصل جديد</h1>

    <form action="{{ route('classrooms.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <!-- الفصل الدراسي -->
        <div>
            <label class="block text-sm font-medium mb-1">الفصل الدراسي</label>
            <select name="semester" class="border rounded w-full px-3 py-2" required>
                <option value="">اختر الفصل</option>
                <option value="ربيعي">ربيعي</option>
                <option value="خريفي">خريفي</option>
            </select>
        </div>

        <!-- السنة الدراسية -->
        <div>
            <label class="block text-sm font-medium mb-1">السنة الدراسية</label>
            <select name="year" class="border rounded w-full px-3 py-2" required>
                <option value="">اختر السنة</option>
                @for ($year = 2020; $year <= date('Y') + 5; $year++)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>

        <!-- القسم -->
        <div>
            <label class="block text-sm font-medium mb-1">القسم</label>
            <select name="department_id" class="border rounded w-full px-3 py-2" required>
                <option value="">اختيار القسم</option>
                @foreach(\App\Models\Department::all() as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- الأزرار -->
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">💾 حفظ</button>
            <a href="{{ route('classrooms.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">⬅️ رجوع</a>
        </div>
    </form>
</div>
@endsection
