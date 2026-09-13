@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="gradesTable()">

    <h1 class="text-2xl font-bold mb-4">نتائج الطلاب</h1>

    <div class="flex space-x-4 mb-4">
        <input type="text" placeholder="ابحث باسم الطالب..." x-model="filters.student_name"
               class="border rounded px-3 py-2 w-1/3">
        <input type="text" placeholder="ابحث باسم المادة..." x-model="filters.course_name"
               class="border rounded px-3 py-2 w-1/3">
    </div>

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
                <template x-for="(grade, index) in filteredGrades" :key="grade.enrollment_id">
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
    {!! json_encode($grades->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
    function gradesTable() {
        return {
            grades: [],
            filters: { student_name: '', course_name: '' },
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

            get filteredGrades() {
                return this.grades.filter(g => {
                    const matchName = g.student_name.toLowerCase().includes(this.filters.student_name.toLowerCase());
                    const matchCourse = g.course_name.toLowerCase().includes(this.filters.course_name.toLowerCase());
                    return matchName && matchCourse;
                });
            },

            // دالة التنقل السريع من لوحة المفاتيح
            navigate(event, currentIndex, currentField) {
                const totalRows = this.filteredGrades.length;
                let targetId = null;

                switch (event.key) {
                    case 'ArrowUp':
                        // السهم لأعلى: يذهب لنفس الخانة في الصف السابق
                        if (currentIndex > 0) {
                            targetId = `input-${currentField}-${currentIndex - 1}`;
                        }
                        event.preventDefault();
                        break;
                        
                    case 'ArrowDown':
                    case 'Enter':
                        // السهم لأسفل أو زر الإنتر: يذهب لنفس الخانة في الصف التالي
                        if (currentIndex < totalRows - 1) {
                            targetId = `input-${currentField}-${currentIndex + 1}`;
                        }
                        event.preventDefault();
                        break;

                    case 'ArrowLeft':
                        // السهم الأيسر: يتنقل بين أعمال ونهائي لنفس الطالب (حسب اتجاه الواجهة RTL)
                        if (currentField === 'work') {
                            targetId = `input-final-${currentIndex}`;
                        }
                        break;

                    case 'ArrowRight':
                        // السهم الأيمن: يتنقل بين نهائي وأعمال لنفس الطالب
                        if (currentField === 'final') {
                            targetId = `input-work-${currentIndex}`;
                        }
                        break;
                    default:
                        return; // الخروج إذا كان زراً آخر
                }

                // تفعيل الفوكس (التركيز) وتحديد النص داخل الخانة المستهدفة تلقائياً لتسهيل التعديل
                if (targetId) {
                    this.$nextTick(() => {
                        const targetInput = document.getElementById(targetId);
                        if (targetInput) {
                            targetInput.focus();
                            targetInput.select(); // تظليل النص القديم لتقوم بالكتابة فوقه مباشرة
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
                            practical_work: grade.practical_work ?? null,
                            practical_final: grade.practical_final ?? null,
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
                    alert(`تم حفظ ${successCount} من أصل ${modifiedRows.length} سجلات بنجاح. يرجى مراجعة الأخطاء في السجلات المتبقية.`);
                }
            },

            printGradesTable() {
                if (this.filteredGrades.length === 0) {
                    alert('لا توجد بيانات للطباعة');
                    return;
                }

                const courseName = this.filteredGrades[0].course_name ?? '';
                const display = (v) => {
                    if (v === null || v === undefined) return '';
                    if (Number(v) === 0) return '';
                    return v;
                };

                let html = `
                    <table style="width:100%; border-collapse: collapse; direction: rtl; font-family: Arial, sans-serif;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:6px;">#</th>
                                <th style="border:1px solid #ccc; padding:6px;">اسم الطالب</th>
                                <th style="border:1px solid #ccc; padding:6px;">أعمال نظري</th>
                                <th style="border:1px solid #ccc; padding:6px;">نهائي نظري</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                this.filteredGrades.forEach((g, index) => {
                    html += `
                        <tr>
                            <td style="border:1px solid #ccc; padding:6px;">${index + 1}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${g.student_name}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${display(g.theory_work)}</td>
                            <td style="border:1px solid #ccc; padding:6px;">${display(g.theory_final)}</td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;

                const newWin = window.open('', '_blank', 'width=900,height=700');
                newWin.document.write(`
                    <html>
                        <head>
                            <title>طباعة درجات الطلاب</title>
                            <style>
                                body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
                                th, td { border:1px solid #ccc; padding:6px; text-align:center; }
                                th { background-color:#f0f0f0; }
                            </style>
                        </head>
                        <body>
                            <h2 style="text-align:center;">كشف درجات الطلاب</h2>
                            <p style="text-align:center; font-weight:bold;">المادة: ${courseName}</p>
                            ${html}
                        </body>
                    </html>
                `);

                newWin.document.close();
                newWin.focus();
                newWin.print();
                newWin.close();
            }
        }
    }
    </script>
</div>
@endsection