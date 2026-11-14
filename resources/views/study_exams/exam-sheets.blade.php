@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-2xl font-bold">كشوفات المواد</h1>
                <p class="text-sm text-gray-600 mt-1">عرض طلبة المواد الحالية حسب الأستاذ والمادة مع تمييز الطالب المرحل.</p>
            </div>

            @if($sheets->isNotEmpty())
                <a href="{{ route('exam-sheets.print', request()->query()) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded text-center hover:bg-gray-900">
                    طباعة الكشف
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('exam-sheets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select name="department_id" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($filters['department_id'] === (string) $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">اسم الأستاذ</label>
                <select name="teacher_id" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأساتذة</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($filters['teacher_id'] === (string) $teacher->id)>
                            {{ $teacher->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">اسم المادة</label>
                <select name="course_id" class="border rounded px-3 py-2 w-full">
                    <option value="">كل المواد</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($filters['course_id'] === (string) $course->id)>
                            {{ $course->name }} @if($course->course_code) - {{ $course->course_code }} @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-full">بحث</button>
                <a href="{{ route('exam-sheets.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 border rounded hover:bg-gray-200 w-full text-center">إلغاء</a>
            </div>
        </form>
    </div>

    @if($activeSemesterCount === 0)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3">
            لا يوجد سيمستر مفعل حاليا.
        </div>
    @endif

    <div class="space-y-6">
        @forelse($sheets as $sheet)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $sheet['course_name'] }}</h2>
                        <div class="text-sm text-gray-600 mt-1">
                            السيمستر: {{ $sheet['semester_label'] }}
                            <span class="mx-2">|</span>
                            الأستاذ: {{ $sheet['teacher_label'] }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            القسم: {{ $sheet['department_name'] }}
                            <span class="mx-2">|</span>
                            الشعبة: {{ $sheet['section_name'] }}
                        </div>
                    </div>

                    <div class="text-sm">
                        <span class="inline-flex items-center px-3 py-1 rounded bg-red-50 text-red-700 border border-red-200">طالب مرحل</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-300 text-center">
                        <thead class="bg-gray-100">
                            <tr>
                                <th rowspan="2" class="border border-gray-300 px-3 py-2">ر.ت</th>
                                <th rowspan="2" class="border border-gray-300 px-3 py-2">اسم الطالب</th>
                                <th rowspan="2" class="border border-gray-300 px-3 py-2">رقم القيد</th>
                                <th colspan="3" class="border border-gray-300 px-3 py-2">أعمال السنة</th>
                                <th colspan="3" class="border border-gray-300 px-3 py-2">النهائي</th>
                                <th rowspan="2" class="border border-gray-300 px-3 py-2">المجموع</th>
                                <th rowspan="2" class="border border-gray-300 px-3 py-2">الحالة</th>
                            </tr>
                            <tr>
                                <th class="border border-gray-300 px-3 py-2">عملي</th>
                                <th class="border border-gray-300 px-3 py-2">نظري</th>
                                <th class="border border-gray-300 px-3 py-2">المجموع</th>
                                <th class="border border-gray-300 px-3 py-2">عملي</th>
                                <th class="border border-gray-300 px-3 py-2">نظري</th>
                                <th class="border border-gray-300 px-3 py-2">المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sheet['rows'] as $index => $row)
                                <tr class="{{ $row['is_carry'] ? 'bg-red-50 text-red-800 font-semibold' : 'odd:bg-gray-50' }}">
                                    <td class="border border-gray-300 px-3 py-2">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-right">{{ $row['student_name'] }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $row['student_number'] }}</td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2"></td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $row['is_carry'] ? 'مرحل' : 'حالي' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="border border-gray-300 px-3 py-6 text-gray-500">لا يوجد طلبة يدرسون هذه المادة حاليا.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                لا توجد مواد مطابقة للبحث في السيمستر الحالي.
            </div>
        @endforelse
    </div>
</div>
@endsection
