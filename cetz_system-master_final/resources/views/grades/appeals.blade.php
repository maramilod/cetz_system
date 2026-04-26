@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="gradesTable()">

    <h1 class="text-2xl font-bold mb-4">نتائج الطلاب</h1>

    <!-- فلاتر -->
 <form method="GET" action="{{ route('grades.appeals') }}" class="flex space-x-4 mb-4">

    <input type="text"
           name="student_name"
           value="{{ request('student_name') }}"
           placeholder="ابحث باسم الطالب..."
           class="border rounded px-3 py-2 w-1/3">

    <input type="text"
           name="course_name"
           value="{{ request('course_name') }}"
           placeholder="ابحث باسم المادة..."
           class="border rounded px-3 py-2 w-1/3">

    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        بحث
    </button>

</form>
<!-- الجدول -->
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-center">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2" rowspan="2">#</th>
                <th class="border p-2" rowspan="2">الطالب</th>
                <th class="border p-2" rowspan="2">رقم الطالب</th>
                <th class="border p-2" rowspan="2">المادة</th>
                <th class="border p-2" colspan="2">الجزء النظري</th>
                <th class="border p-2" colspan="2">الجزء العملي</th>
                <th class="border p-2" rowspan="2">المجموع</th>
                                <th class="border p-2" rowspan="2">محاولة رقم  </th>

                <th class="border p-2" rowspan="2">الحالة</th>

                <th class="border p-2" rowspan="2">إجراءات</th>
            </tr>
            <tr>
                <!-- الجزء النظري -->
                <th class="border p-2">أعمال</th>
                <th class="border p-2">نهائي</th>
                <!-- الجزء العملي -->
                <th class="border p-2">أعمال</th>
                <th class="border p-2">نهائي</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(grade, index) in filteredGrades" :key="grade.enrollment_id">
                <tr class="hover:bg-gray-50">
                    <td class="border p-2" x-text="index + 1"></td>
                    <td class="border p-2" x-text="grade.student_name"></td>
                    <td class="border p-2" x-text="grade.student_number"></td>
                    <td class="border p-2" x-text="grade.course_name"></td>

                    <!-- الجزء النظري editable -->
                      <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.theory_work"   ">
                    </td>

                    <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.theory_final">
                    </td>

                    <!-- الجزء العملي editable -->
                    <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.practical_work"
                               :disabled="!grade.has_practical">
                    </td>

                    <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.practical_final"
                               :disabled="!grade.has_practical ">
                    </td>

<td class="border p-2" x-text="grade.total + '%'"></td>
                    <td class="border p-2" x-text="grade.attempt "></td>
                    <td class="border p-2 font-bold"
    :class="grade.status === 'passed' ? 'text-green-600' : 'text-red-600'"
    x-text="grade.status === 'passed' ? 'ناجح' : 'راسب'">
</td>

                      <td class="border p-2">
                        <button class="px-3 py-1 bg-green-600 text-white rounded"
                                @click="saveGrade(grade)">💾 تعديل</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <div class="mt-4">
    {{ $pagination->links() }}
</div>
</div>


<!-- بيانات JSON منفصلة -->
<script type="application/json" id="grades-data">
{!! json_encode($grades->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<script>
function gradesTable() {
    return {
        grades: [],
        filters: { student_name: '', course_name: '' },
        init() {
            const dataEl = document.getElementById('grades-data');
            if (dataEl) this.grades = JSON.parse(dataEl.textContent);
        },
        get filteredGrades() {
            return this.grades.filter(g => {
                const matchName = g.student_name.toLowerCase().includes(this.filters.student_name.toLowerCase());
                const matchCourse = g.course_name.toLowerCase().includes(this.filters.course_name.toLowerCase());
                return matchName && matchCourse;
            });
        },
        async saveGrade(grade) {
            try {
                const response = await fetch('{{ route("grades.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        student_id: grade.student_id,
                        course_id: grade.course_id,
                        enrollment_id: grade.enrollment_id,
                        theory_work: grade.theory_work ?? null,
                theory_midterm: grade.theory_midterm ?? null,
                theory_final: grade.theory_final ?? null,

                practical_work: grade.practical_work ?? null,
                practical_midterm: grade.practical_midterm ?? null,
                practical_final: grade.practical_final ?? null,
                                        })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('تم حفظ درجة الطالب بنجاح!');
                } else {
                    alert('حدث خطأ: ' + (result.message || ''));
                }
            } catch (err) {
                console.error(err);
                alert('حدث خطأ أثناء الاتصال بالخادم.');
            }
        }
    }
}
</script>
@endsection
