@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="gradesTable()">

    <h1 class="text-2xl font-bold mb-4">نتائج الطلاب</h1>

    <form method="GET" action="{{ route('grades.appeals') }}" class="flex space-x-4 mb-6">
        <input type="text" name="student_name" value="{{ request('student_name') }}"
               placeholder="ابحث باسم الطالب..." class="border rounded px-3 py-2 w-1/4">

        <input type="text" name="course_name" value="{{ request('course_name') }}"
               placeholder="ابحث باسم المادة..." class="border rounded px-3 py-2 w-1/4">

        <select name="department_id" class="border rounded px-3 py-2 w-1/4">
            <option value="">كل الأقسام</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">بحث</button>
    </form>

    <div class="mb-4 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            عدد الطلاب المختارين: <span class="font-bold text-blue-600" x-text="selectedIds.length"></span>
        </div>
        <button @click="printSelectedGrades()" 
                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
                :disabled="selectedIds.length === 0">
            🖨️ طباعة كشف الطعون (للمختارين)
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">
                        <input type="checkbox" @change="toggleAll($event)" :checked="selectedIds.length === grades.length && grades.length > 0">
                    </th>
                    <th class="border p-2" rowspan="2">#</th>
                    <th class="border p-2" rowspan="2">الطالب</th>
                    <th class="border p-2" rowspan="2">رقم الطالب</th>
                    <th class="border p-2" rowspan="2">المادة</th>
                    <th class="border p-2">أعمال</th>
                    <th class="border p-2">نهائي</th> 
                    <th class="border p-2" rowspan="2">المجموع</th>
                    <th class="border p-2" rowspan="2">محاولة رقم</th>
                    <th class="border p-2" rowspan="2">الحالة</th>
                    <th class="border p-2" rowspan="2">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(grade, index) in filteredGrades" :key="grade.enrollment_id">
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2">
                            <input type="checkbox" :value="grade.enrollment_id" x-model="selectedIds">
                        </td>
                        <td class="border p-2" x-text="index + 1"></td>
                        <td class="border p-2" x-text="grade.student_name"></td>
                        <td class="border p-2" x-text="grade.student_number"></td>
                        <td class="border p-2" x-text="grade.course_name"></td>

                        <td class="border p-2">
                            <input type="number" min="0" max="100" class="w-16 text-center border rounded focus:bg-blue-50"
                                   :id="'input-work-' + index"
                                   @keydown="navigate($event, index, 'work')"
                                   x-model.number="grade.theory_work">
                        </td>

                        <td class="border p-2">
                            <input type="number" min="0" max="100" class="w-16 text-center border rounded focus:bg-blue-50"
                                   :id="'input-final-' + index"
                                   @keydown="navigate($event, index, 'final')"
                                   x-model.number="grade.theory_final">
                        </td>

                        <td class="border p-2 font-bold" 
                            :class="grade.theory_final < 30 ? 'text-orange-600' : 'text-blue-600'"
                            x-text="grade.theory_final < 30 ? (grade.theory_work || 0) : (Number(grade.theory_work || 0) + Number(grade.theory_final || 0))">
                        </td> 
                        <td class="border p-2" x-text="grade.attempt"></td>
                        <td class="border p-2 font-bold"
                            :class="calculateStatus(grade) === 'passed' ? 'text-green-600' : 'text-red-600'"
                            x-text="calculateStatus(grade) === 'passed' ? 'ناجح' : 'راسب'">
                        </td>

                        <td class="border p-2">
                            <button class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
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

    <script type="application/json" id="grades-data">
    {!! json_encode($grades->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
    function gradesTable() {
        return {
            grades: [],
            selectedIds: [],
            filters: { student_name: '', course_name: '' },

            init() {
                const dataEl = document.getElementById('grades-data');
                if (dataEl) this.grades = JSON.parse(dataEl.textContent);
            },

            // دالة التنقل السريع من لوحة المفاتيح
            navigate(event, currentIndex, currentField) {
                const totalRows = this.filteredGrades.length;
                let targetId = null;

                switch (event.key) {
                    case 'ArrowUp':
                        if (currentIndex > 0) {
                            targetId = `input-${currentField}-${currentIndex - 1}`;
                        }
                        event.preventDefault();
                        break;
                        
                    case 'ArrowDown':
                    case 'Enter':
                        if (currentIndex < totalRows - 1) {
                            targetId = `input-${currentField}-${currentIndex + 1}`;
                        }
                        event.preventDefault();
                        break;

                    case 'ArrowLeft':
                        // يتنقل إلى حقل النهائي إذا كان في حقل الأعمال (حسب اتجاه RTL المعتمد عربيًا)
                        if (currentField === 'work') {
                            targetId = `input-final-${currentIndex}`;
                        }
                        break;

                    case 'ArrowRight':
                        // يعود لحقل الأعمال إذا كان واقفاً على حقل النهائي
                        if (currentField === 'final') {
                            targetId = `input-work-${currentIndex}`;
                        }
                        break;
                    default:
                        return;
                }

                if (targetId) {
                    this.$nextTick(() => {
                        const targetInput = document.getElementById(targetId);
                        if (targetInput) {
                            targetInput.focus();
                            targetInput.select(); // تظليل النص القديم لسرعة استبداله وعمل التعديل مباشرة
                        }
                    });
                }
            },

            toggleAll(event) {
                if (event.target.checked) {
                    this.selectedIds = this.grades.map(g => g.enrollment_id);
                } else {
                    this.selectedIds = [];
                }
            },

            printSelectedGrades() {
                if (this.selectedIds.length === 0) {
                    alert('يرجى اختيار طالب واحد على الأقل للطباعة');
                    return;
                }

                const studentsToPrint = this.grades.filter(g => 
                    this.selectedIds.map(Number).includes(Number(g.enrollment_id))
                );

                if (studentsToPrint.length === 0) return;

                let html = `
                    <table style="width:100%; border-collapse: collapse; direction: rtl; font-family: 'Arial', sans-serif;" border="1">
                        <thead>
                            <tr style="background-color: #f2f2f2;">
                                <th style="padding:10px;" rowspan="2">#</th>
                                <th style="padding:10px;" rowspan="2">اسم الطالب</th>
                                <th style="padding:10px;" rowspan="2">رقم القيد</th>
                                <th style="padding:10px;" rowspan="2">المادة</th>
                                <th style="padding:10px;" colspan="2">الدرجة السابقة</th>
                                <th style="padding:10px;" rowspan="2">الدرجة الحالية (بعد الطعن)</th>
                            </tr>
                            <tr style="background-color: #f9f9f9;">
                                <th style="padding:5px;">أعمال</th>
                                <th style="padding:5px;">نهائي</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                studentsToPrint.forEach((g, index) => {
                    const work = Number(g.theory_work || 0);
                    const final = Number(g.theory_final || 0);
                    const prevWork = Number(g.prev_theory_work || 0);
                    const prevFinal = Number(g.prev_theory_final || 0);
                    const total = (final < 30) ? final : (work + final); // تعديل للتوافق مع منطق دالة calculateTotal الحقيقية لديك

                    html += `
                        <tr>
                            <td style="padding:8px; text-align:center;">${index + 1}</td>
                            <td style="padding:8px;">${g.student_name}</td>
                            <td style="padding:8px; text-align:center;">${g.student_number}</td>
                            <td style="padding:8px; text-align:center;">${g.course_name}</td>
                            <td style="padding:8px; text-align:center; color: #666;">${prevWork}</td>
                            <td style="padding:8px; text-align:center; color: #666;">${prevFinal}</td>
                            <td style="padding:8px; text-align:center; font-weight:bold; background-color: #fdfdfd;">${total}</td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;

                const win = window.open('', '_blank');
                if (!win) {
                    alert('يرجى السماح بالنوافذ المنبثقة (Pop-ups) للمتصفح لتتمكن من الطباعة');
                    return;
                }

                win.document.write(`
                    <!DOCTYPE html>
                    <html dir="rtl">
                    <head>
                        <title>كشف الطعون</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
                            h2 { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
                            .header-info { margin-bottom: 20px; }
                            .footer { margin-top: 50px; display: flex; justify-content: space-between; }
                            table { width: 100%; border-collapse: collapse; }
                        </style>
                    </head>
                    <body>
                        <h2>كشف مراجعة درجات (طعون)</h2>
                        <div class="header-info">
                            <p>التاريخ: ${new Date().toLocaleDateString('ar-LY')}</p>
                        </div>
                        ${html}
                        <div class="footer">
                            <p>توقيع المسجل: ....................</p>
                            <p>توقيع لجنة الرصد: ....................</p>
                        </div>
                        <script>
                            setTimeout(() => {
                                window.print();
                            }, 500);
                        <\/script>
                    </body>
                    </html>
                `);
                win.document.close();
            },

            calculateTotal(grade) {
                const work = Number(grade.theory_work || 0);
                const final = Number(grade.theory_final || 0);
                
                if (final < 30) {
                    grade.total = final; 
                } else {
                    grade.total = work + final;
                }
                return grade.total;
            },

            calculateStatus(grade) {
                const final = Number(grade.theory_final || 0);
                const total = this.calculateTotal(grade);

                if (final >= 30 && total >= 50) {
                    grade.status = 'passed';
                } else {
                    grade.status = 'failed';
                }
                return grade.status;
            },

            get filteredGrades() {
                if (!this.filters.student_name.trim() && !this.filters.course_name.trim()) {
                    return this.grades;
                }

                return this.grades.filter(g => {
                    const studentSearch = this.filters.student_name.toLowerCase().trim();
                    const courseSearch = this.filters.course_name.toLowerCase().trim();
                    
                    const matchName = g.student_name ? g.student_name.toLowerCase().includes(studentSearch) : true;
                    const matchCourse = g.course_name ? g.course_name.toLowerCase().includes(courseSearch) : true;
                    
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
                            theory_final: grade.theory_final ?? null,
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
</div>
@endsection