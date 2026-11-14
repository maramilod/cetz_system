@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">تعديل القسم</h1>

    <form action="{{ route('departments.update', $department) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium">اسم القسم</label>
            <input type="text" name="name" class="border rounded w-full px-3 py-2" value="{{ $department->name }}" required>
        </div>



        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">تعديل</button>
            <a href="{{ route('departments.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">رجوع</a>
        </div>
    </form>
</div>
@endsection
