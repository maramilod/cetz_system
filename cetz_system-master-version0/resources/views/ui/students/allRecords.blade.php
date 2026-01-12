@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">سجل جميع الطلاب</h1>

    <!-- فلترة البحث -->
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="q" placeholder="بحث بالاسم أو الرقم" value="{{ $q ?? '' }}" class="border rounded px-3 py-2">
        <select name="department" class="border rounded px-3 py-2">
            <option value="">كل الأقسام</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" @selected(($departmentFilter ?? '') == $dept)>{{ $dept }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded">بحث</button>
    </form>

    <!-- زر طباعة -->
    <div class="mb-4 flex justify-end">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded">طباعة</button>
    </div>

    <!-- جدول الطلاب -->
    <table class="w-full text-sm border">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="p-2 text-right">رقم القيد</th>
                <th class="p-2 text-right">الاسم</th>
                <th class="p-2 text-right">القسم</th>
                <th class="p-2 text-right">سنة التسجيل</th>
                <th class="p-2 text-right">الفصل</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2">{{ $student->student_number }}</td>
                <td class="p-2">{{ $student->name }}</td>
                <td class="p-2">{{ $student->department }}</td>
                <td class="p-2">{{ $student->enrollment_year }}</td>
                <td class="p-2">{{ $student->semester }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center p-2">لا يوجد نتائج</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
