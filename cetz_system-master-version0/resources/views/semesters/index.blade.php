@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="text-2xl font-bold mb-4">إدارة الفصول الدراسية</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- إضافة حزمة -->
    <form action="{{ route('semesters.store') }}" method="POST" class="bg-white p-4 rounded shadow space-y-3">
        @csrf
        <div>
            <label class="block mb-1 text-sm">نوع البرنامج</label>
            <select name="degree_type" class="border rounded px-3 py-2 w-full" required>
                <option value="بكالوريوس">بكالوريوس</option>
                <option value="دبلوم">دبلوم</option>
            </select>
        </div>
        <div>
    <label class="block mb-1 text-sm">نوع الفصل</label>
    <select name="term_type" class="border rounded px-3 py-2 w-full" required>
        <option value="">اختر الفصل</option>
        <option value="خريفي">خريفي</option>
        <option value="ربيعي">ربيعي</option>
    </select>
</div>

        <div>
            <label class="block mb-1 text-sm">تاريخ البداية</label>
            <input type="date" name="start_at" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block mb-1 text-sm">تاريخ النهاية</label>
            <input type="date" name="end_at" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">إضافة الحزمة</button>
        </div>
    </form>

    <!-- قائمة الحزم الحالية -->
    <div class="bg-white p-4 rounded shadow mt-6">
        <h2 class="text-lg font-semibold mb-2">الفصل الحالي</h2>

     @foreach($packages as $package)
    <form action="{{ route('semesters.updatePackage') }}" method="POST" class="inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="degree_type" value="{{ $package->first()->degree_type }}">
        <input type="hidden" name="start_date" value="{{ $package->first()->start_date }}">
        <input type="hidden" name="end_date" value="{{ $package->first()->end_date }}">
        
        <input type="date" name="new_start_date" value="{{ $package->first()->start_date }}" required>
        <input type="date" name="new_end_date" value="{{ $package->first()->end_date }}" required>
        <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded">تعديل</button>
    </form>

    <form action="{{ route('semesters.destroyPackage') }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <input type="hidden" name="degree_type" value="{{ $package->first()->degree_type }}">
        <input type="hidden" name="start_date" value="{{ $package->first()->start_date }}">
        <input type="hidden" name="end_date" value="{{ $package->first()->end_date }}">
        <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded" onclick="return confirm('هل تريد حذف الفصل الحالي؟')">حذف</button>
    </form>

    <ul class="mt-2">
        @foreach($package as $semester)
            <li>{{ $semester->semester_number }} - {{ $semester->name }}</li>
        @endforeach
    </ul>
@endforeach

    </div>

</div>
@endsection
