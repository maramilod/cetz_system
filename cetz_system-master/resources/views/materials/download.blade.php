@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6" x-data="materialsAssign()" x-init="">
    <!-- معلومات الطالب -->
    <div class="bg-white rounded-lg shadow p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

        <!-- إدخال رقم الطالب -->
        <div>
            <label class="block text-sm text-gray-600 mb-1">رقم الطالب (جامعي / يدوي)</label>
            <input type="text"
                   x-model="studentNumberInput"
                   @input.debounce.300="onStudentNumberInput()"
                   placeholder="أدخل الرقم"
                   class="border rounded px-3 py-2 w-full">
        </div>

        <!-- الشعبة -->
        <div>
            <label class="block text-sm text-gray-600 mb-1">الشعبة</label>
            <input type="text" readonly
                   class="border rounded px-3 py-2 w-full bg-gray-100"
                   :value="currentStudent?.section_name">
        </div>

        <!-- الفصل -->
        <div>
            <label class="block text-sm text-gray-600 mb-1">الفصل</label>
            <select x-model="selectedSemester" class="border rounded px-3 py-2 w-full">
                <template x-for="s in availableSemesters" :key="s.id">
                    <option :value="s.id" x-text="s.label"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- المواد المتاحة -->
        <div class="bg-white rounded-lg shadow p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg">المواد المتاحة</h2>
                <input type="text" class="border rounded px-3 py-1"
                       placeholder="بحث"
                       x-model.trim="searchAvailable">
            </div>

            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-left">#</th>
                    <th class="border px-2 py-1 text-left">رمز المادة</th>
                    <th class="border px-2 py-1 text-left">اسم المادة</th>
                    <th class="border px-2 py-1 text-left">الوحدات</th>
                    <th class="border px-2 py-1 text-left">الساعات</th>
                    <th class="border px-2 py-1 text-left">إجراء</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="(m, index) in filteredAvailable()" :key="m.id">
                    <tr>
                        <td class="border px-2 py-1" x-text="index + 1"></td>
                        <td class="border px-2 py-1" x-text="m.code"></td>
                        <td class="border px-2 py-1" x-text="m.name"></td>
                        <td class="border px-2 py-1" x-text="m.units"></td>
                        <td class="border px-2 py-1" x-text="m.hours"></td>
                        <td class="border px-2 py-1">
                            <button class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    @click="assign(m)">
                                إضافة
                            </button>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredAvailable().length === 0">
                    <td class="border px-2 py-1 text-center" colspan="6">لا توجد مواد متاحة</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- مواد الطالب -->
        <div class="bg-white rounded-lg shadow p-4 space-y-3">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold">مواد الطالب</h2>

                <button
                    @click="printStudentCourses()"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    طباعة
                </button>
            </div>

            <table class="border border-gray-300 w-full text-left">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">رمز المادة</th>
                    <th class="border px-2 py-1">اسم المادة</th>
                    <th class="border px-2 py-1">الحالة</th>
                    <th class="border px-2 py-1">الوحدات</th>
                    <th class="border px-2 py-1">الساعات</th>
                    <th class="border px-2 py-1">إزالة</th>
                </tr>
                </thead>
                <tbody>
                <template x-if="currentStudent && currentStudent.enrollments">
                    <template x-for="enroll in visibleEnrollments" :key="enroll.id">
                        <tr>
                            <td class="border px-2 py-1" x-text="enroll.course.code"></td>
                            <td class="border px-2 py-1" x-text="enroll.course.name"></td>
                            <td class="border px-2 py-1" x-text="enroll.status"></td>
                            <td class="border px-2 py-1" x-text="enroll.course.units"></td>
                            <td class="border px-2 py-1" x-text="enroll.course.hours"></td>
                            <td class="border px-2">
                                <button class="px-2 py-1 bg-red-100 text-red-700 rounded"
                                        @click="unassign(enroll)">
                                    إزالة
                                </button>
                            </td>
                        </tr>
                    </template>
                </template>
                <template x-if="!currentStudent || !currentStudent.enrollments || currentStudent.enrollments.length === 0">
                    <tr>
                        <td class="border px-2 py-1 text-center" colspan="6">لم يتم الاختيار</td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function csrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

document.addEventListener('alpine:init', () => {
    Alpine.data('materialsAssign', () => ({
        studentNumberInput: '',
        selectedStudent: null,
        selectedSemester: '',
        searchAvailable: '',
        materials: [],
        availableSemesters: [],

        onStudentNumberInput() {
            const val = this.studentNumberInput.trim();
            if (!val) {
                this.selectedStudent = null;
                this.selectedSemester = '';
                this.materials = [];
                this.availableSemesters = [];
                return;
            }

            fetch(`/download-materials/students/search?query=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.student) {
                        this.selectedStudent = data.student;
                        this.materials = data.available_materials;
                        this.availableSemesters = data.available_semesters;
                        // اختر أول فصل تلقائيًا إذا موجود
                        this.selectedSemester = this.availableSemesters[0]?.id || '';
                    } else {
                        this.selectedStudent = null;
                        this.selectedSemester = '';
                        this.materials = [];
                        this.availableSemesters = [];
                    }
                })
                .catch(err => console.error(err));
        },

        get currentStudent() {
            return this.selectedStudent;
        },

        filteredAvailable() {
            if (!this.currentStudent || !this.selectedSemester) return [];
            const semId = String(this.selectedSemester);
            const search = this.searchAvailable.toLowerCase();
            return this.materials.filter(m =>
                String(m.semester_id) === semId &&
                (!search || [m.code, m.name].some(v => v.toLowerCase().includes(search)))
            );
        },

        get visibleEnrollments() {
            if (!this.currentStudent || !this.currentStudent.enrollments) return [];
            return this.currentStudent.enrollments.filter(e => e.status === 'in_progress');
        },

        assign(m) {
            if (!this.currentStudent) return;
            fetch('/enrollments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    student_id: this.currentStudent.id,
                    course_offering_id: m.id
                })
            }).then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    alert(Object.values(data.errors ?? {error:[data.message]}).flat().join('\n'));
                    return;
                }
                // إضافة المادة
                this.currentStudent.enrollments.push({
                    ...data.enrollment,
                    course: {
                        code: m.code,
                        name: m.name,
                        units: m.units,
                        hours: m.hours
                    },
                    status: 'in_progress'
                });
            }).catch(err => { console.error(err); alert('خطأ في الاتصال بالسيرفر'); });
        },

        unassign(enroll) {
            if (!confirm(`هل أنت متأكد من حذف المادة "${enroll.course.name}"؟`)) return;
            fetch(`/enrollments/${enroll.id}`, {
                method: 'DELETE',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf() }
            }).then(async res => {
                if(!res.ok){ const data = await res.json(); alert(data.message||'خطأ في الحذف'); return;}
                const index = this.currentStudent.enrollments.findIndex(e => e.id===enroll.id);
                if(index!==-1) this.currentStudent.enrollments.splice(index,1);
            }).catch(err => { console.error(err); alert('خطأ في الاتصال بالسيرفر'); });
        },

printStudentCourses() {
    if (!this.currentStudent) {
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    if (!this.selectedSemester) {
        alert('يرجى اختيار الفصل الدراسي');
        return;
    }

    const studentName   = this.currentStudent.full_name;
    const studentNumber = this.currentStudent.student_number;
    const sectionName   = this.currentStudent.section_name ?? '-';
const departmentName = this.currentStudent.department_name ?? '-';


    let semesterLabel = '-';
    let semesterNumber = '-';
    const semesterObj = this.availableSemesters.find(s => String(s.id) === String(this.selectedSemester));
    if (semesterObj) {
        const year = new Date(semesterObj.start_date).getFullYear();
        semesterLabel = semesterObj.label ?? '-';
        semesterNumber = `${year} ${semesterObj.term_type}`;
    }

    // إنشاء جدول HTML من enrollments مباشرة
    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>رمز المادة</th>
                <th>اسم المادة</th>
                <th>الوحدات</th>
                <th>الساعات</th>
            </tr>
        </thead>
        <tbody>
            ${this.visibleEnrollments.map(e => `
                <tr>
                    <td>${e.course.code}</td>
                    <td>${e.course.name}</td>
                    <td>${e.course.units}</td>
                    <td>${e.course.hours}</td>
                </tr>
            `).join('')}
        </tbody>
    `;

    // حساب المجموع
    let totalUnits = 0;
    let totalHours = 0;
    this.visibleEnrollments.forEach(e => {
        totalUnits += parseFloat(e.course.units) || 0;
        totalHours += parseFloat(e.course.hours) || 0;
    });

    const tfoot = document.createElement('tfoot');
    tfoot.innerHTML = `
        <tr>
            <td colspan="2" style="text-align:right; font-weight:bold;">المجموع</td>
            <td style="text-align:right; font-weight:bold;">${totalUnits}</td>
            <td style="text-align:right; font-weight:bold;">${totalHours}</td>
        </tr>
    `;
    table.appendChild(tfoot);

    // نافذة الطباعة
    const win = window.open('', '_blank', 'width=900,height=1200');
    win.document.write(`
        <html>
        <head>
            <title>تنزيل مواد الطالب</title>
            <style>
                html, body { font-family: Arial; direction: rtl; margin:0; padding:30px; }
                h1,h2 { text-align:center; margin:3px 0; }
                table { width:100%; border-collapse:collapse; margin-bottom:20px; }
                th, td { border:1px solid #000; padding:6px; text-align:center; }
                thead { background:#f0f0f0; }
                .info-box { text-align:right; margin-bottom:20px; }
                .info-row { margin-bottom:5px; }
                .info-row span:first-child { font-weight:bold; }

                /* ===== أسفل الصفحة ===== */
                .page-footer {
                    position: fixed;
                    bottom: 40px;
                    right: 30px;
                    left: 30px;
                    display: flex;
                    justify-content: space-between;
                    font-weight: bold;
                }
                .footer-left {
                    text-align: right;
                }
                .footer-right {
                    text-align: left;
                }
                .signature {
                    margin-top: 40px;
                    border-top: 1px solid #000;
                    width: 180px;
                }
            </style>
        </head>
        <body>
            <h1>تنزيل مواد الطالب</h1>
            <h2>${studentName}</h2>

            <div class="info-box">
                <div class="info-row"><span>رقم الطالب:</span> ${studentNumber}</div>
                <div class="info-row"><span>القسم:</span> ${departmentName}</div>
                <div class="info-row"><span>الشعبة:</span> ${sectionName}</div>
                <div class="info-row"><span>الفصل الدراسي:</span> ${semesterNumber}</div>
                <div class="info-row"><span>رقم الفصل:</span> ${semesterLabel}</div>
            </div>

            ${table.outerHTML}

            <div class="page-footer">
                <div class="footer-left">
                    <div>
                        توقيع الطالب
                        <div class="signature"></div>
                    </div>
                    <div style="margin-top:40px;">
                        قسم التسجيل
                        <div class="signature"></div>
                    </div>
                </div>
                <div class="footer-right">${departmentName}</div>
            </div>
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    win.print();
    win.close();
}

    }));
});
</script>
@endsection
