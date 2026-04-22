@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">الطلبة المتخرجين</h1>

    {{-- نموذج البحث --}}
    <form action="{{ route('students.graduated') }}" method="GET" class="mb-4 flex gap-2 items-center">
        <input type="text" name="search" placeholder="رقم القيد أو الاسم" 
               value="{{ request('search') }}" 
               class="border rounded px-3 py-2 w-64">
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            بحث
        </button>
    </form>

    {{-- جدول الطلاب --}}
    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full border border-gray-300 text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-2 py-1">رقم القيد</th>
            <th class="border px-2 py-1">اسم الطالب</th>
            <th class="border px-2 py-1">الشعبة</th>
            <th class="border px-2 py-1">القسم</th>
            <th class="border px-2 py-1">الإفادة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr class="text-center">
            <td class="border px-2 py-1">{{ $student->student_number ?? $student->manual_number }}</td>
            <td class="border px-2 py-1 text-right">{{ $student->full_name }}</td>
            <td class="border px-2 py-1">{{ $student->section->name ?? '-' }}</td>
            <td class="border px-2 py-1">{{ $student->section->department->name ?? '-' }}</td>
            <td class="border px-2 py-1">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('students.graduated.certificate', $student) }}"
                       class="inline-flex items-center rounded bg-slate-700 px-3 py-1.5 text-white hover:bg-slate-800">
                        عرض الإفادة
                    </a>
                    <a href="{{ route('students.graduated.certificate', $student) }}?print=1"
                       target="_blank"
                       class="inline-flex items-center rounded bg-blue-700 px-3 py-1.5 text-white hover:bg-blue-800">
                        طباعة
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- روابط الصفحات --}}
<div class="mt-4">
    {{ $students->links() }}
</div>

    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $students->withQueryString()->links() }}
    </div>

</div>
@endsection
