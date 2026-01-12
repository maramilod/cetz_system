@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
   <!--  
<pre>
@foreach($materials as $material)
{{ print_r($material, true) }}
@endforeach
</pre>-->
<div class="space-y-6" x-data="materialsAssign()" x-init="$watch('selectedSemester', () => { searchAvailable = '' })">

 
<div class="text-right font-semibold">
    الفصل الدراسي:
    <span x-text="academicTerm"></span>
</div>



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

        <!-- الطالب -->
        <div>
            <label class="block text-sm text-gray-600 mb-1">اختر الطالب</label>
<select x-model="selectedStudent" @change="onStudentChange()" class="border rounded px-3 py-2 w-full">
<option value="" disabled>اختر الطالب</option>
<template x-for="s in students.filter(student => (student.current_status ?? '') === 'تم التجديد')" :key="s.number">
    <option :value="s.number" x-text="s.name + ' — ' + s.number"></option>
</template>

    </template>
</select>

        </div>

        <!-- القسم -->  
        <div>
            <label class="block text-sm text-gray-600 mb-1">الشعبة</label>
            <!-- مثال في Blade -->
<input type="text" readonly
       class="border rounded px-3 py-2 w-full bg-gray-100"
       :value="currentStudent?.section_name">

        </div>

        <!-- السيمستر -->
        <div>
            <label class="block text-sm text-gray-600 mb-1">الفصل</label>
<select x-model="selectedSemester" @change="localStorage.setItem('selectedSemester', selectedSemester)" class="...">
        class="border rounded px-3 py-2 w-full">
    <template x-for="s in availableSemesters" :key="s.id">
        <option :value="s.id" x-text="s.label"></option>
    </template>
</select>
        </div>
       <!-- أزرار -->
    
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">


        <!-- جدول المواد المتاحة -->
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
                     x-show="m.status === 'active'"
                            @click="assign(m)">
                        إضافة
                    </button>
                                <span    x-show="m.status === 'dropped'"
class="text-red-500 text-xs"> (مسقطة)</span>
                </td>
            </tr>
        </template>

        <!-- حالة عدم وجود مواد -->
        <tr x-show="filteredAvailable().length === 0">
            <td class="border px-2 py-1 text-center" colspan="6">لا توجد مواد متاحة</td>
        </tr>
        </tbody>
    </table>
</div>

     <div class="bg-white rounded-lg shadow p-4 space-y-3">
           <div class="flex items-center justify-between mb-2">
    <h2 class="font-semibold">مواد الطالب</h2>

    <button
        @click="printStudentCourses()"
        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
        طباعة
    </button>
</div>



<table id="studentCoursesTable" class="border border-gray-300 w-full text-left">
    <thead>
        <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-1">رمز المادة</th>
            <th class="border border-gray-300 px-2 py-1">اسم المادة</th>
            <th class="border border-gray-300 px-2 py-1">الحالة</th>
            <th class="border border-gray-300 px-2 py-1">الوحدات</th>
            <th class="border border-gray-300 px-2 py-1">الساعات</th>
                                <th class="border border-gray-300 px-2 py-1">إزالة</th>

        </tr>
    </thead>
    <tbody>
        <template x-if="currentStudent && currentStudent.enrollments">
<template x-for="enroll in visibleEnrollments" :key="enroll.id">
                <tr>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.code"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.name"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.status"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.units"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.hours"></td>
                    <td class="border border-gray-300 px-2">
    <button class="px-2 py-1 bg-red-100 text-red-700 rounded" 
            @click="unassign(enroll)">
        ازالة
    </button>
</td>

                </tr>
            </template>
        </template>
        <template x-if="!currentStudent || !currentStudent.enrollments">
            <tr>
                <td class="border border-gray-300 px-2 py-1" colspan="5">لم يتم الاختيار</td>

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
        students: @json($students),
        departments: @json($departments),
        semesters: @json($semesters),
        materials: @json($materials), // جميع المواد من course_offerings

        studentNumberInput: '',
        selectedStudent: '',
        selectedSemester: '',
        searchAvailable: '',
        assignments: {},
        totals: { units: 0, hours: 0 },

init() {
    const savedStudent  = localStorage.getItem('selectedStudent');

    this.$nextTick(() => {

        // الطالب
        if (savedStudent && this.students.find(s => s.number === savedStudent)) {
            this.selectedStudent = savedStudent;
            this.studentNumberInput = savedStudent;
        } else {
            this.selectedStudent = '';
            this.studentNumberInput = '';
        }

        // الفصل: نأخذ أول فصل فيه مواد متاحة للطالب
        if (this.currentStudent) {
            const firstAvailableSemester = this.availableSemesters[0];
            if (firstAvailableSemester) {
                this.selectedSemester = firstAvailableSemester.id;
            } else {
                this.selectedSemester = ''; // لا يوجد فصل متاح
            }
        }
    });
}

,
get visibleEnrollments() {
    if (!this.currentStudent || !this.currentStudent.enrollments) return [];

    // نعرض فقط المواد الحالية (قيد الدراسة)
    return this.currentStudent.enrollments.filter(e =>
        e.status === 'in_progress'
    );
},
        // الطالب الحالي
        get currentStudent() {
            return this.students.find(s => s.number === this.selectedStudent);
        },

        // مفتاح التعيينات
        key() { 
            return this.selectedStudent + '|' + this.selectedSemester; 
        },

        // عند إدخال رقم الطالب
        onStudentNumberInput() {
            const val = this.studentNumberInput.trim();
            const student = this.students.find(s => s.number === val || s.manual_number === val);
            if(student){
                this.selectedStudent = student.number;
                        localStorage.setItem('selectedStudent', student.number);

                        const firstAvailableSemester = this.availableSemesters[0];
        if (firstAvailableSemester) {
            this.selectedSemester = firstAvailableSemester.id;
        } else {
            this.selectedSemester = ''; // لا يوجد فصل متاح
        }

            }
        },

        // عند اختيار الطالب من القائمة
        onStudentChange() {
            const student = this.students.find(s => s.number === this.selectedStudent);
            if(student){
                this.studentNumberInput = student.number;
                        localStorage.setItem('selectedStudent', student.number);


                          const firstAvailableSemester = this.availableSemesters[0];
        if (firstAvailableSemester) {
            this.selectedSemester = firstAvailableSemester.id;
        } else {
            this.selectedSemester = ''; // لا يوجد فصل متاح
        }

            }

        },

        // المواد المتاحة للطالب الحالي والفصل الحالي
available() {

    if (!this.currentStudent || !this.selectedSemester) return [];

    const sectionId = this.currentStudent.section_id;
    const semId = this.selectedSemester;

    // 🔹 الحالات التي تمنع ظهور المادة
    const blockedStatuses = ['in_progress', 'passed'];

    const blockedCourseCodes = new Set(
    (this.currentStudent.enrollments || [])
        .filter(e => blockedStatuses.includes(e.status))
        .map(e => e.course.code) 
);


    return this.materials
        .filter(m =>
            Number(m.section_id) === Number(sectionId) &&
            Number(m.semester_id) === Number(semId)
        )
        // 🔥 منع المادة إذا نفس كود المادة موجود عند الطالب
        .filter(m => !blockedCourseCodes.has(m.code));
}
,
        filteredAvailable() {
            const s = this.searchAvailable.trim().toLowerCase();
            return this.available().filter(m => 
                !s || [m.number, m.code, m.name].some(v => v?.toString().toLowerCase().includes(s))
            );
        },

        // قائمة المواد المعينة
        assignedList() { 
            return this.assignments[this.key()] || []; 
        },

        // تعيين مادة
assign(m, confirmPrerequisite = false) {

    fetch('/enrollments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            student_id: this.currentStudent.id,
            course_offering_id: m.id,
            confirm_prerequisite: confirmPrerequisite
        })
    })
    .then(async res => {
        const data = await res.json();

        // 🔔 تحذير المادة السابقة
        if (!res.ok && data?.type === 'prerequisite_warning') {
            if (confirm(data.message)) {
                // 🔁 إعادة الإرسال مع التأكيد
                this.assign(m, true);
            }
            return;
        }

        // ❌ أخطاء أخرى
        if (!res.ok) {
            alert(
                Object.values(data.errors ?? { error: [data.message] })
                    .flat()
                    .join('\n')
            );
            return;
        }

        // ✅ نجاح التسجيل
this.currentStudent.enrollments.push({
    ...data.enrollment,
    course: {
        code: m.code,
        name: m.name,
        units: m.units,
        hours: m.hours
    }
});

    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('خطأ في الاتصال بالسيرفر');
    });
},
get availableSemesters() {
    if (!this.currentStudent) return [];

    // نحتفظ بالفصول التي فيها مواد متاحة
    return this.semesters.filter(sem => {
        const semId = sem.id;

        // نتحقق إذا يوجد أي مادة متاحة لهذا الطالب في هذا الفصل
        return this.materials.some(m =>
            Number(m.section_id) === Number(this.currentStudent.section_id) &&
            Number(m.semester_id) === Number(semId) &&
            // 🔹 فقط إذا المادة لم يتم تسجيلها أو لم تُنجز
            !(this.currentStudent.enrollments || []).some(e =>
                ['in_progress', 'passed'].includes(e.status) &&
                e.course.code === m.code
            )
        );
    });
}
,
unassign(enroll) {
    if (!confirm(`هل أنت متأكد من حذف المادة "${enroll.course.name}"؟`)) return;

    fetch(`/enrollments/${enroll.id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf() // تأكد من وجود دالة csrf()
        }
    })
    .then(async res => {
        if (!res.ok) {
            const data = await res.json();
            alert(data.message || 'خطأ في الحذف');
            return;
        }
        const list = this.currentStudent.enrollments;
        const index = list.findIndex(e => e.id === enroll.id);
        if (index !== -1) list.splice(index, 1);

    })
    .catch(err => {
        console.error(err);
        alert('خطأ في الاتصال بالسيرفر');
    });
},
get academicTerm() {
    const sem = this.semesters.find(
        s => String(s.id) === String(this.selectedSemester)
    );
    return sem?.start_date + "  " + sem?.term_type?? '';
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

    const table = document.getElementById('studentCoursesTable'); // نفس الجدول أو جدول مواد التنزيل
    if (!table) {
        alert('الجدول غير موجود');
        return;
    }

    const studentName   = this.currentStudent.name;
    const studentNumber = this.currentStudent.number;
    const sectionName   = this.currentStudent.section_name ?? '-';
    const departmentName = this.currentStudent.department_name ?? '-';

    this.currentStudent.section?.department?.name ?? '-';
    let semesterLabel = '-';
    const semesterObj = this.semesters.find(s => String(s.id) === String(this.selectedSemester));
    let semesterNumber = '-';
    if (semesterObj) {
        const year = new Date(semesterObj.start_date).getFullYear();
        semesterLabel = semesterObj?.label ?? '-';
        semesterNumber = `${year} ${semesterObj.term_type}`;
    }

    // نسخة من الجدول
    const tableClone = table.cloneNode(true);

    // إزالة أعمدة الحالة والإزالة كما سابقاً
    const removeIndexes = [2, 5];
    tableClone.querySelectorAll('thead tr').forEach(tr => {
        removeIndexes.slice().reverse().forEach(i => tr.children[i]?.remove());
    });
    tableClone.querySelectorAll('tbody tr').forEach(tr => {
        removeIndexes.slice().reverse().forEach(i => tr.children[i]?.remove());
    });

    // حساب المجموع
    let totalUnits = 0;
    let totalHours = 0;
    tableClone.querySelectorAll('tbody tr').forEach(tr => {
        const unitsCell = tr.children[2];
        const hoursCell = tr.children[3];
        if (unitsCell) totalUnits += parseFloat(unitsCell.textContent) || 0;
        if (hoursCell) totalHours += parseFloat(hoursCell.textContent) || 0;
    });

    const tfoot = document.createElement('tfoot');
    const totalRow = document.createElement('tr');
    totalRow.innerHTML = `
        <td colspan="2" style="text-align:right; font-weight:bold;">المجموع</td>
        <td style="text-align:right; font-weight:bold;">${totalUnits}</td>
        <td style="text-align:right; font-weight:bold;">${totalHours}</td>
    `;
    tfoot.appendChild(totalRow);
    tableClone.appendChild(tfoot);

    // نافذة الطباعة
    const win = window.open('', '_blank', 'width=900,height=1200');

    win.document.write(`
    <html>
    <head>
        <title>تنزيل مواد الطالب</title>
        <style>
            html, body {
                font-family: Arial; direction: rtl; margin:0; padding:30px; height:100%;
            }
            h1, h2 { text-align:center; margin:3px 0; }
            table { width:100%; border-collapse:collapse; margin-bottom:20px; }
            th, td { border:1px solid #000; padding:6px; text-align:center; }
            thead { background:#f0f0f0; }
            .info-box { text-align:right; margin-bottom:20px; }
            .info-row { margin-bottom:5px; }
            .info-row span:first-child { font-weight:bold; }
            .footer { display:flex; justify-content:space-between; margin-top:80px; }
            .footer .department { text-align:left; font-weight:bold; }
            .footer .registration { text-align:right; }
            .footer .registration span { display:block; margin-top:50px; border-top:1px solid #000; width:150px; }
        </style>
    </head>
    <body>
        <h1>تنزيل مواد الطالب</h1>
        <h2>${studentName}</h2>

        <div class="info-box">
            <div class="info-row"><span>رقم الطالب:</span> <span>${studentNumber}</span></div>
<div class="info-row"><span>القسم:</span> <span>${departmentName}</span></div>
<div class="info-row"><span>الشعبة:</span> <span>${sectionName}</span></div>
            <div class="info-row"><span>الفصل الدراسي:</span> <span>${semesterNumber}</span></div>
            <div class="info-row"><span>رقم الفصل:</span> <span>${semesterLabel}</span></div>
        </div>

        ${tableClone.outerHTML}

       <div class="page-footer">

    <!-- يسار الصفحة: القسم العلمي -->
  

    <!-- يمين الصفحة: توقيع الطالب + قسم التسجيل -->
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
  <div class="footer-right">
        ${departmentName}
    </div>
</div>

    </body>
    </html>
    <style>
    html, body {
        font-family: Arial;
        direction: rtl;
        margin: 0;
        padding: 30px;
        height: 100%;
    }

    h1, h2 { text-align: center; margin: 3px 0; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 80px;
    }

    th, td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }

    thead { background: #f0f0f0; }

    .info-box { text-align: right; margin-bottom: 20px; }
    .info-row { margin-bottom: 5px; }
    .info-row span:first-child { font-weight: bold; }

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

    .footer-right {
     margin-top: 100px;
        text-align: left;
    }

    .footer-left {
        text-align: right;
    }

    .signature {
        margin-top: 40px;
        border-top: 1px solid #000;
        width: 180px;
    }
</style>

    `);

    win.document.close();
    win.focus();
    win.print();
    win.close();
}

,



    }));
});
</script>

@endsection
