@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">تعديل الفصل</h1>

    <form action="{{ route('classrooms.update', $classroom) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium">اسم الفصل</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-2" value="{{ $classroom->name }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium">القسم</label>
            <select name="department_id" class="border rounded w-full px-3 py-2">
                <option value="">اختيار القسم</option>
                @foreach(\App\Models\Department::all() as $dept)
                    <option value="{{ $dept->id }}" @selected($classroom->department_id == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">تعديل</button>
            <a href="{{ route('classrooms.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">رجوع</a>
        </div>
    </form>
</div>
@endsection
