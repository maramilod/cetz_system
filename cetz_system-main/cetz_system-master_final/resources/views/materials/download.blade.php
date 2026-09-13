@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6"
     x-data="materialsAssign()"
     x-init="init()">
   
    <!-- معلومات الطالب -->
    <div class="bg-white rounded-lg shadow p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

        <!-- إدخال رقم الطالب -->
       <div class="relative">
    <input type="text"
           x-model="studentSearch"
           @input.debounce.300="searchStudents()"
           @focus="openDropdown = true"
           @click.outside="openDropdown = false"
           placeholder="ابحث بالاسم أو الرقم"
           class="border rounded px-3 py-2 w-full">

    <!-- القائمة المنسدلة -->
    <div x-show="openDropdown && searchResults.length"
         class="absolute z-50 bg-white border w-full mt-1 max-h-60 overflow-auto rounded shadow">

        <template x-for="s in searchResults" :key="s.id">
            <div @click="selectStudent(s)"
                 class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                <span x-text="s.full_name"></span>
                -
                <span x-text="s.manual_number || s.student_number"></span> 
            </div>
        </template>
    </div>
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
        <button
    class="px-2 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
    @click="autoRegister()">
    تنزيل المواد تلقائياً
</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- المواد المتاحة -->
        <div class="bg-white rounded-lg shadow p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-lg">المواد المتاحة</h2>
                <input type="text" class="border rounded px-3 py-1"
                       placeholder="بحث"
                       x-model.trim="searchAvailable">
            
<button
    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
    @click="assignAll()">
    إضافة جميع المواد
</button></div>
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

<!-- المواد الناجح بها الطالب -->
<div class="bg-white rounded-lg shadow p-4 mt-4 space-y-3 lg:col-span-2">
    <h2 class="font-semibold text-green-600">المواد الناجح بها الطالب</h2>

    <!-- أزرار التنقل -->
    <div class="flex justify-between items-center mb-3">
        <button @click="prevSemester" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">السابق</button>
        <h3 class="font-bold text-green-700" x-text="currentSemester?.semester || 'لا يوجد'"></h3>
        <button @click="nextSemester" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">التالي</button>
    </div>

    <!-- الجدول -->
    <table class="border w-full text-right text-sm">
        <thead>
            <tr class="bg-green-100">
                <th class="border px-2 py-1">رمز المادة</th>
                <th class="border px-2 py-1">اسم المادة</th>
                <th class="border px-2 py-1 text-center w-48">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="c in currentSemester?.courses" :key="c.id">
                <tr class="bg-green-50">
                    <td class="border px-2 py-1" x-text="c.course.code"></td>
                    <td class="border px-2 py-1" x-text="c.course.name"></td>
                    <td class="border px-2 py-1 text-center space-x-1 space-x-reverse">
                        <!-- زر تعديل البيانات -->
                        <button class="px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600"
                                @click="editPassedCourse(c)">
                            تعديل
                        </button>

                        <!-- زر تبديل المادة -->
                        <button class="px-2 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700"
                                @click="swapPassedCourse(c)">
                            تبديل
                        </button>

                        <!-- زر الحذف -->
                        <button class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                @click="deletePassedCourse(c)">
                            حذف
                        </button>
                    </td>
                </tr>
            </template>
            <tr x-show="!currentSemester || !currentSemester.courses || currentSemester.courses.length === 0">
                <td class="border px-2 py-1 text-center text-gray-500" colspan="3">لا توجد مواد ناجح بها لهذا الفصل</td>
            </tr>
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
        studentSearch: '',
searchResults: [],
openDropdown: false,
        studentNumberInput: '',
        selectedStudent: null,
        selectedSemester: '',
        searchAvailable: '',
        materials: [],
        availableSemesters: [],

        currentSemesterIndex: 0,

    prefilledStudent: @json($prefilledStudent ?? null),

init() {
    if (this.prefilledStudent) {
        this.studentSearch =
            this.prefilledStudent.full_name + ' - ' + this.prefilledStudent.student_number;

        this.onStudentNumberInput(this.prefilledStudent.student_number);
    }
},
get passedBySemester() {
    const groups = {};

    this.passedEnrollments.forEach(e => {
        const key = e.semester_name || 'غير محدد';

        if (!groups[key]) {
            groups[key] = [];
        }

        groups[key].push(e);
    });

    return Object.entries(groups).map(([semester, courses]) => ({
        semester,
        courses
    }));
},
get currentSemester() {
    return this.passedBySemester[this.currentSemesterIndex] || null;
},

nextSemester() {
    if (this.currentSemesterIndex < this.passedBySemester.length - 1) {
        this.currentSemesterIndex++;
    }
},

prevSemester() {
    if (this.currentSemesterIndex > 0) {
        this.currentSemesterIndex--;
    }
},
get passedEnrollments() {
    if (!this.currentStudent || !this.currentStudent.enrollments) return [];
    return this.currentStudent.enrollments.filter(e => e.status === 'passed');
},
searchStudents() {
    const val = this.studentSearch.trim();

   if (val.length < 2) {
    this.searchResults = [];
    return;
}

    fetch(`/download-materials/search-autocomplete?query=${encodeURIComponent(val)}`)
        .then(res => res.json())
        .then(data => {
            this.searchResults = data;
            this.openDropdown = true;
        })
        .catch(err => console.error(err));
},
selectStudent(student) {
               const number = student.manual_number || student.student_number;

    this.studentSearch = `${student.full_name} - ${number}`;
    this.openDropdown = false;
    this.searchResults = [];

    this.onStudentNumberInput(student.student_number);
},
    onStudentNumberInput(number = null) {
    const val = (number ?? this.studentNumberInput).trim();
    
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
            return this.currentStudent.enrollments.filter(e => e.status === 'in_progress' || e.status === 'equivalent');
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
        autoRegister() {
    if (!this.currentStudent) {
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    if (!confirm('هل تريد تنزيل الخطة الدراسية تلقائياً؟')) return;

    fetch('/enrollments/auto-register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            student_id: this.currentStudent.id
        })
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'حدث خطأ');
            return;
        }

        alert(data.message || 'تم تنزيل المواد بنجاح');

        // 🔥 اعادة تحميل بيانات الطالب بعد التحديث
        this.onStudentNumberInput(this.currentStudent.student_number);
    })
    .catch(err => {
        console.error(err);
        alert('خطأ في الاتصال بالسيرفر');
    });
},
// 1. تعديل بيانات المادة (الاسم والرمز)
editPassedCourse(enrollment) {
    const newCode = prompt('أدخل رمز المادة الجديد:', enrollment.course.code);
    if (newCode === null) return; // إلغاء

    const newName = prompt('أدخل اسم المادة الجديد:', enrollment.course.name);
    if (newName === null) return; // إلغاء

    if (!newCode.trim() || !newName.trim()) {
        alert('رمز واسم المادة مطلوبان');
        return;
    }

    fetch(`/enrollments/${enrollment.id}/update-course`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            code: newCode.trim(),
            name: newName.trim()
        })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'حدث خطأ أثناء التعديل');
        
        // تحديث البيانات محلياً في واجهة Alpine
        enrollment.course.code = newCode.trim();
        enrollment.course.name = newName.trim();
        alert('تم تعديل المادة بنجاح');
    })
    .catch(err => {
        console.error(err);
        alert(err.message || 'خطأ في الاتصال بالسيرفر');
    });
},

// 2. تبديل المادة بمادة أخرى من نفس القسم
swapPassedCourse(enrollment) {
    // 1. استخدام المواد المتاحة المفلترة حسب الفصل المحدد بدلاً من القائمة العامة
    const availableList = this.filteredAvailable();

    if (!availableList || availableList.length === 0) {
        alert('لا توجد مواد متاحة للتبديل في هذا الفصل');
        return;
    }

    // 2. عرض الخيارات المتاحة
    let optionsText = "اختر رقم المادة البديلة من القائمة التالية:\n\n";
    availableList.forEach((m, index) => {
        optionsText += `${index + 1}. [${m.code}] ${m.name}\n`;
    });

    const choice = prompt(optionsText);
    if (!choice) return;

    const selectedIndex = parseInt(choice) - 1;
    const selectedCourse = availableList[selectedIndex];

    if (!selectedCourse) {
        alert('خيار غير صحيح');
        return;
    }

    if (!confirm(`هل أنت متأكد من تبديل المادة "${enrollment.course.name}" بالمادة "${selectedCourse.name}"؟`)) return;

    fetch(`/enrollments/${enrollment.id}/swap`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            new_course_offering_id: selectedCourse.id
        })
    })
    .then(async res => {
        const isJson = res.headers.get('content-type')?.includes('application/json');
        const data = isJson ? await res.json() : null;

        if (!res.ok) {
            throw new Error((data && data.message) ? data.message : `خطأ في السيرفر (${res.status})`);
        }

        // إعادة تحميل بيانات الطالب لتحديث القوائم بعد التبديل
        this.onStudentNumberInput(this.currentStudent.student_number);
        alert('تم تبديل المادة بنجاح');
    })
    .catch(err => {
        console.error(err);
        alert(err.message || 'خطأ في الاتصال بالسيرفر');
    });
},

// 3. حذف مادة ناجح بها
deletePassedCourse(enrollment) {
    if (!confirm(`هل أنت متأكد من حذف المادة الناجح بها "${enrollment.course.name}"؟`)) return;

    fetch(`/enrollments/${enrollment.id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        }
    })
    .then(async res => {
        if (!res.ok) {
            const data = await res.json();
            throw new Error(data.message || 'خطأ في الحذف');
        }

        // حذف المادة من مصفوفة الطالب المحلية
        const index = this.currentStudent.enrollments.findIndex(e => e.id === enrollment.id);
        if (index !== -1) {
            this.currentStudent.enrollments.splice(index, 1);
        }
        alert('تم حذف المادة بنجاح');
    })
    .catch(err => {
        console.error(err);
        alert(err.message || 'خطأ في الاتصال بالسيرفر');
    });
},
        assignAll() {
    if (!this.currentStudent) return;

    const materials = this.filteredAvailable();

    if (materials.length === 0) {
        alert('لا توجد مواد لإضافتها');
        return;
    }

    if (!confirm(`هل تريد إضافة ${materials.length} مادة للطالب؟`)) return;

    materials.forEach(m => {
        this.assign(m);
    });
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
    const studentNumber = this.currentStudent.manual_number ?? this.currentStudent.student_number;
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

    // إنشاء جدول HTML
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
            <td style="text-align:center; font-weight:bold;">${totalUnits}</td>
            <td style="text-align:center; font-weight:bold;">${totalHours}</td>
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
                @page {
                    size: A4;
                    margin: 15mm;
                }
                html, body { font-family: Arial, sans-serif; direction: rtl; margin:0; padding:15px; }
                h1,h2 { text-align:center; margin:3px 0; }
                table { width:100%; border-collapse:collapse; margin-bottom:20px; page-break-inside: auto; }
                tr { page-break-inside: avoid; }
                th, td { border:1px solid #000; padding:6px; text-align:center; }
                thead { background:#f0f0f0; display: table-header-group; }
                
                .info-box { text-align:right; margin-bottom:20px; }
                .info-row { margin-bottom:5px; }
                .info-row span:first-child { font-weight:bold; }

                /* ===== أسفل الصفحة المتوافق مع طول الجدول ===== */
                .page-footer {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                    font-weight: bold;
                    page-break-inside: avoid; /* يمنع انقسام التوقيعات بين صفحتين */
                }
                .footer-right {
                    text-align: right;
                }
                .footer-left {
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
                <div class="footer-right">
                    <div>
                        توقيع الطالب
                        <div class="signature"></div>
                    </div>
                    <div style="margin-top:30px;">
                        قسم التسجيل
                        <div class="signature"></div>
                    </div>
                </div>
                <div class="footer-left">
                    ${departmentName}
                </div>
            </div>
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    setTimeout(() => {
        win.print();
        win.close();
    }, 250);
}
    }));
});

</script>
@endsection
