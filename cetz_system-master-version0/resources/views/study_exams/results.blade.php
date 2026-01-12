@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="gradesTable()">

    <h1 class="text-2xl font-bold mb-4">نتائج الطلاب</h1>

    <!-- فلاتر -->
    <div class="flex space-x-4 mb-4">
        <input type="text" placeholder="ابحث باسم الطالب..." x-model="filters.student_name"
               class="border rounded px-3 py-2 w-1/3">
        <input type="text" placeholder="ابحث باسم المادة..." x-model="filters.course_name"
               class="border rounded px-3 py-2 w-1/3">
    </div>
<!-- الجدول -->
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-center">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2" rowspan="2">#</th>
                <th class="border p-2" rowspan="2">الطالب</th>
                <th class="border p-2" rowspan="2">رقم الطالب</th>
                <th class="border p-2" rowspan="2">المادة</th>
                <th class="border p-2" colspan="3">الجزء النظري</th>
                <th class="border p-2" colspan="3">الجزء العملي</th>
                <th class="border p-2" rowspan="2">المجموع</th>
                <th class="border p-2" rowspan="2">دور ثاني </th>
                <th class="border p-2" rowspan="2">إجراءات</th>
            </tr>
            <tr>
                <!-- الجزء النظري -->
                <th class="border p-2">أعمال</th>
                <th class="border p-2">نصفي</th>
                <th class="border p-2">نهائي</th>
                <!-- الجزء العملي -->
                <th class="border p-2">أعمال</th>
                <th class="border p-2">نصفي</th>
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
                               x-model.number="grade.theory_work">
                    </td>
                    <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.theory_midterm">
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
                               x-model.number="grade.practical_midterm"
                               :disabled="!grade.has_practical">
                    </td>
                    <td class="border p-2">
                        <input type="number" min="0" max="100" class="w-16 text-center border rounded"
                               x-model.number="grade.practical_final"
                               :disabled="!grade.has_practical">
                    </td>

<td class="border p-2" x-text="grade.total + '%'"></td>
                    <td class="border p-2" x-text="grade.is_second_chance ? 'نعم' : 'لا'"></td>
                      <td class="border p-2">
                        <button class="px-3 py-1 bg-green-600 text-white rounded"
                                @click="saveGrade(grade)">💾 حفظ</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>


<!-- بيانات JSON منفصلة -->
<script type="application/json" id="grades-data">
{!! json_encode($grades->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<script>
function gradesTable() {
    return {
        grades: [],
        filters: { student_name: '', course_name: '', student_type: '' },
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
                        theory_work: grade.theory_work,
                        theory_midterm: grade.theory_midterm,
                        theory_final: grade.theory_final,
                        practical_work: grade.practical_work,
                        practical_midterm: grade.practical_midterm,
                        practical_final: grade.practical_final,
                        student_type: grade.student_type,
                        is_second_chance: grade.is_second_chance
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
