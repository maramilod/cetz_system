@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6">

    <!-- إشعارات -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- الفلاتر -->
    <div class="bg-white rounded-lg shadow p-4 space-y-4">
        <h2 class="font-semibold text-lg mb-2">فلترة الطلاب</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="text" id="filterStudent" placeholder="اسم الطالب أو رقم القيد"
                class="border rounded px-3 py-2 w-full">
            <input type="text" id="filterCourse" placeholder="المادة"
                class="border rounded px-3 py-2 w-full">
        </div>
    </div>

    <!-- جدول الطلاب لإضافة الحرمان -->
    <div class="bg-white rounded-lg shadow p-4 space-y-4">
        <h2 class="font-semibold text-lg mb-2">إضافة محروم</h2>

        <table id="studentsTable" class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">#</th>
                    <th class="border px-2 py-1">الطالب</th>
                    <th class="border px-2 py-1">رقم القيد</th>
                    <th class="border px-2 py-1">المقرر</th>
                    <th class="border px-2 py-1">رمز المقرر</th>
                    <th class="border px-2 py-1">سبب الحرمان</th>
                    <th class="border px-2 py-1">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentsEnrollments as $i => $enrollment)
                    <tr>
                        <td class="border px-2 py-1">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1 student-name">{{ $enrollment->student->full_name }}</td>
                       <td class="border px-2 py-1 student-number" 
    data-number="{{ $enrollment->student->student_number ?? $enrollment->student->manual_number }}">
    {{ $enrollment->student->student_number ?? $enrollment->student->manual_number }}
</td>

                        <td class="border px-2 py-1 course-name">{{ $enrollment->courseOffering->course->name }}</td>
                        <td class="border px-2 py-1">{{ $enrollment->courseOffering->course->course_code }}</td>
                        <td class="border px-2 py-1">
                            <input type="text" form="deprivationForm{{ $enrollment->id }}" name="reason" placeholder="أدخل السبب"
                                class="border rounded px-2 py-1 w-full" required>
                        </td>
                        <td class="border px-2 py-1">
                            <form id="deprivationForm{{ $enrollment->id }}" method="POST" action="{{ route('deprivations.store') }}">
                                @csrf
                                <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                <button type="submit" class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    إضافة
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>




    <!-- قائمة المحرومين -->
    <div class="bg-white rounded-lg shadow p-4 space-y-3">
        <h2 class="font-semibold text-lg mb-2">قائمة المحرومين</h2>

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">#</th>
                    <th class="border px-2 py-1">الطالب</th>
                    <th class="border px-2 py-1">المقرر</th>
                    <th class="border px-2 py-1">رمز المقرر</th>
                    <th class="border px-2 py-1">سبب الحرمان</th>
                    <th class="border px-2 py-1">تم التحديث بواسطة</th>
                    <th class="border px-2 py-1">تاريخ الإضافة</th>
                    <th class="border px-2 py-1">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deprivations as $i => $d)
                    <tr>
                        <td class="border px-2 py-1">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $d->enrollment->student->full_name }}</td>
                        <td class="border px-2 py-1">{{ $d->enrollment->courseOffering->course->name }}</td>
                        <td class="border px-2 py-1">{{ $d->enrollment->courseOffering->course->course_code }}</td>
                        <td class="border px-2 py-1">{{ $d->reason }}</td>
                        <td class="border px-2 py-1">{{ optional($d->updatedBy)->full_name ?? 'N/A' }}</td>
                        <td class="border px-2 py-1">{{ $d->created_at->format('Y-m-d H:i') }}</td>
                        <td class="border px-2 py-1">
                            <form action="{{ route('deprivations.destroy', $d->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إزالة المحروم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border px-2 py-1 text-center">لا يوجد محرومين حتى الآن</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
<script>
    const studentFilter = document.getElementById('filterStudent');
    const courseFilter  = document.getElementById('filterCourse');
    const tableRows     = document.querySelectorAll('#studentsTable tbody tr');

    function filterTable() {
        const studentValue = studentFilter.value.toLowerCase();
        const courseValue  = courseFilter.value.toLowerCase();

        tableRows.forEach(row => {
            const name  = row.querySelector('.student-name').textContent.toLowerCase();
        
            const number = (row.querySelector('.student-number')?.textContent || row.querySelector('.student-manual-number')?.textContent || '').toLowerCase();

            const course = row.querySelector('.course-name').textContent.toLowerCase();

            const matchStudent = name.includes(studentValue) || number.includes(studentValue);
            const matchCourse  = course.includes(courseValue);

            if(matchStudent && matchCourse) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    studentFilter.addEventListener('input', filterTable);
    courseFilter.addEventListener('input', filterTable);
</script>
@endsection
