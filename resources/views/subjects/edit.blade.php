@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">تعديل المادة</h1>

    <form action="{{ route('subjects.update', $subject) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        <input type="hidden" name="id" value="{{ $subject->id }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">رقم المادة</label>
                <input type="number" name="number" class="border rounded w-full px-3 py-2" value="{{ $subject->number }}">
            </div>
            <div>
                <label class="block text-sm font-medium">رمز المادة</label>
                <input type="text" name="code" class="border rounded w-full px-3 py-2" value="{{ $subject->code }}">
            </div>
            <div>
                <label class="block text-sm font-medium">اسم المادة</label>
                <input type="text" name="name" class="border rounded w-full px-3 py-2" value="{{ $subject->name }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium">الوحدات</label>
                <input type="number" name="units" class="border rounded w-full px-3 py-2" value="{{ $subject->units }}" min="0" max="10">
            </div>
            <div>
                <label class="block text-sm font-medium">الساعات</label>
                <input type="number" name="hours" class="border rounded w-full px-3 py-2" value="{{ $subject->hours }}" min="0" max="20">
            </div>
            <div>
                <label class="block text-sm font-medium">تعتمد على</label>
                <input type="text" name="depends_on" class="border rounded w-full px-3 py-2" value="{{ $subject->depends_on }}">
            </div>
            <div>
                <label class="block text-sm font-medium">بديلة عن</label>
                <input type="text" name="alternative_for" class="border rounded w-full px-3 py-2" value="{{ $subject->alternative_for }}">
            </div>
            <div>
                <label class="block text-sm font-medium">المستخدم</label>
                <input type="text" name="user_name" class="border rounded w-full px-3 py-2" value="{{ $subject->user_name }}">
            </div>
            <div>
                <label class="block text-sm font-medium">القسم</label>
                <select name="department_id" class="border rounded w-full px-3 py-2">
                    <option value="">اختيار القسم</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected($subject->department_id == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">تعديل</button>
            <a href="{{ route('subjects.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">رجوع</a>
        </div>
    </form>
</div>
@endsection
