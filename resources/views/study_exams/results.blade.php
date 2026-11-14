@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="gradesTable()">

    <h1 class="text-2xl font-bold mb-4">نتائج الطلاب (الرصد)</h1>

    <!-- 1. نموذج البحث المعتمد على GET مع الحفاظ على القيم المرسلة -->
    <form method="GET" action="{{ route('results.index') }}" class="flex space-x-4 mb-4">
        <input type="text" name="student_name" value="{{ request('student_name') }}"
               placeholder="ابحث باسم الطالب..." class="border rounded px-3 py-2 w-1/3">
        
        <input type="text" name="course_name" value="{{ request('course_name') }}"
               placeholder="ابحث باسم المادة..." class="border rounded px-3 py-2 w-1/3">

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">بحث</button>
        
        @if(request()->hasAny(['student_name', 'course_name']))
            <a href="{{ route('results.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center">إعادة ضبط</a>
        @endif
    </form>

    <div class="flex space-x-2 mb-4">
        <button @click="printGradesTable()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            🖨️ طباعة
        </button>
        
        <button @click="saveAllModified()" 
                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 flex items-center"
                :disabled="modifiedGradesCount === 0 || isSavingAll">
            <span x-show="!isSavingAll">💾 حفظ الكل (<span x-text="modifiedGradesCount"></span>)</span>
            <span x-show="isSavingAll" class="flex items-center">
                ⏳ جاري الحفظ...
            </span>
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">#</th>
                    <th class="border p-2">الطالب</th>
                    <th class="border p-2">رقم الطالب</th>
                    <th class="border p-2">المادة</th>
                    <th class="border p-2">أعمال</th>
                    <th class="border p-2">نهائي</th>
                    <th class="border p-2">المجموع</th>
                    <th class="border p-2">محاولة رقم</th>
                    <th class="border p-2">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <!-- استخدام grades مباشرة بدلاً من filteredGrades لأن التصفية تتم من السيرفر -->
                <template x-for="(grade, index) in grades" :key="grade.enrollment_id">
                    <tr class="hover:bg-gray-50" :class="isRowModified(grade) ? 'bg-yellow-50' : ''">
                        <td class="border p-2" x-text="index + 1"></td>
                        <td class="border p-2" x-text="grade.student_name"></td>
                        <td class="border p-2" x-text="grade.student_number"></td>
                        <td class="border p-2" x-text="grade.course_name"></td>

                        <td class="border p-2">
                            <input type="number" min="0" max="100" step="0.01" class="w-16 text-center border rounded focus:bg-blue-50"
                                   :id="'input-work-' + index"
                                   @keydown="navigate($event, index, 'work')"
                                   x-model="grade.theory_work">
                        </td>
                        <td class="border p-2">
                            <input type="number" min="0" max="100" step="0.01" class="w-16 text-center border rounded focus:bg-blue-50"
                                   :id="'input-final-' + index"
                                   @keydown="navigate($event, index, 'final')"
                                   x-model="grade.theory_final">
                        </td>

                        <td class="border p-2 font-bold" 
                            :class="grade.theory_final < 30 ? 'text-orange-600' : 'text-blue-600'"
                            x-text="grade.theory_final < 30 ? (grade.theory_work || 0) : (Number(grade.theory_work || 0) + Number(grade.theory_final || 0))">
                        </td>
                        <td class="border p-2" x-text="grade.attempt"></td>
                        <td class="border p-2">
                            <button class="px-3 py-1 text-white rounded disabled:opacity-50"
                                    :class="isRowModified(grade) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400'"
                                    @click="saveGrade(grade)">
                                💾 حفظ
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <script type="application/json" id="grades-data">
    {!! json_encode($grades, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
</div>

<!-- أزرار التنقل بين الصفحات مع الحفاظ على معلمات البحث (Query String) -->
<div class="mt-4 max-w-7xl mx-auto">
    {{ $gradesPaginator->appends(request()->query())->links() }}
</div>

<script>
function gradesTable() {
    return {
        grades: [],
        isSavingAll: false,

        init() {
            const dataEl = document.getElementById('grades-data');
            if (dataEl) {
                const rawData = JSON.parse(dataEl.textContent);
                this.grades = rawData.map(g => ({
                    ...g,
                    original_theory_work: g.theory_work,
                    original_theory_final: g.theory_final
                }));
            }
        },

        // تم إلغاء filteredGrades والاعتماد على مصفوفة grades مباشرة لأن السيرفر قام بالتصفية مسبقاً
        navigate(event, currentIndex, currentField) {
            const totalRows = this.grades.length;
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
                    if (currentField === 'work') {
                        targetId = `input-final-${currentIndex}`;
                    }
                    break;
                case 'ArrowRight':
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
                        targetInput.select();
                    }
                });
            }
        },

        isRowModified(grade) {
            return grade.theory_work !== grade.original_theory_work || 
                   grade.theory_final !== grade.original_theory_final;
        },

        get modifiedGradesCount() {
            return this.grades.filter(g => this.isRowModified(g)).length;
        },

        markAsSaved(grade) {
            grade.original_theory_work = grade.theory_work;
            grade.original_theory_final = grade.theory_final;
        },

        async saveGrade(grade) {
            const work = parseFloat(grade.theory_work) || 0;
            const final = parseFloat(grade.theory_final) || 0;
            if ((work + final) > 100) {
                alert(`خطأ للطالب ${grade.student_name}: لا يمكن أن يتجاوز مجموع الأعمال والنهائي 100 درجة.`);
                return false;
            }

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
                    this.markAsSaved(grade);
                    return true;
                } else {
                    alert(`حدث خطأ للطالب ${grade.student_name}: ` + (result.error || result.message || ''));
                    return false;
                }
            } catch (err) {
                console.error(err);
                alert(`حدث خطأ أثناء الاتصال بالخادم لحفظ درجة الطالب ${grade.student_name}.`);
                return false;
            }
        },

        async saveAllModified() {
            const modifiedRows = this.grades.filter(g => this.isRowModified(g));
            
            if (modifiedRows.length === 0) {
                alert('لا توجد صفوف معدلة لحفظها.');
                return;
            }

            this.isSavingAll = true;
            const promises = modifiedRows.map(grade => this.saveGrade(grade));
            const results = await Promise.all(promises);
            this.isSavingAll = false;

            const successCount = results.filter(r => r === true).length;
            if (successCount > 0) {
                alert(`تم حفظ ${successCount} من أصل ${modifiedRows.length} سجلات بنجاح.`);
            }
        },

        printGradesTable() {
            if (this.grades.length === 0) {
                alert('لا توجد بيانات للطباعة');
                return;
            }

            const courseName = this.grades[0].course_name ?? '';
            const display = (v) => (v === null || v === undefined || Number(v) === 0) ? '' : v;

            let html = `<table style="width:100%; border-collapse: collapse; direction: rtl; font-family: Arial, sans-serif;">
                        <thead><tr>
                            <th style="border:1px solid #ccc; padding:6px;">#</th>
                            <th style="border:1px solid #ccc; padding:6px;">اسم الطالب</th>
                            <th style="border:1px solid #ccc; padding:6px;">أعمال نظري</th>
                            <th style="border:1px solid #ccc; padding:6px;">نهائي نظري</th>
                        </tr></thead><tbody>`;

            this.grades.forEach((g, index) => {
                html += `<tr>
                            <td style="border:1px solid #ccc; padding:6px;">${index + 1}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${g.student_name}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${display(g.theory_work)}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${display(g.theory_final)}</td>
                         </tr>`;
            });

            html += `</tbody></table>`;

            const newWin = window.open('', '_blank', 'width=900,height=700');
            newWin.document.write(`<html><head><title>طباعة درجات الطلاب</title><style>body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; } th, td { border:1px solid #ccc; padding:6px; text-align:center; } th { background-color:#f0f0f0; }</style></head><body><h2 style="text-align:center;">كشف درجات الطلاب</h2><p style="text-align:center; font-weight:bold;">المادة: ${courseName}</p>${html}</body></html>`);
            newWin.document.close();
            newWin.focus();
            newWin.print();
            newWin.close();
        }
    }
}
</script>
@endsection