@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6" x-data="materialsAssign()" x-init="init()">
    <!--  
<pre>
@foreach($materials as $material)
{{ print_r($material, true) }}
@endforeach
</pre>-->


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
            <select x-model="selectedStudent" @change="onStudentChange()"
                    class="border rounded px-3 py-2 w-full">
<template x-for="s in students.filter(student => (student.current_status ?? '') === 'تم التجديد')" :key="s.number">
                    <option :value="s.number" x-text="s.name + ' — ' + s.number"></option>
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
        <select x-model="selectedSemester"
        class="border rounded px-3 py-2 w-full">
    <template x-for="s in semesters" :key="s.id">
        <option :value="s.id" x-text="s.label"></option>
    </template>
</select>
        </div>
       <!-- أزرار -->
        <div class="col-span-full flex gap-2">
            <button class="px-4 py-2 bg-gray-200 rounded" @click="printResult">🖨️ طباعة</button>
            <button class="px-4 py-2 bg-green-600 text-white rounded" @click="exportExcel">⬇️ Excel</button>
        </div>
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
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">مواد الطالب</h2>
            </div>


<table class="border border-gray-300 w-full text-left">
    <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 px-2 py-1">اسم المادة</th>
            <th class="border border-gray-300 px-2 py-1">كود المادة</th>
            <th class="border border-gray-300 px-2 py-1">الحالة</th>
            <th class="border border-gray-300 px-2 py-1">الوحدات</th>
            <th class="border border-gray-300 px-2 py-1">الساعات</th>
                                <th class="border border-gray-300 px-2 py-1">إزالة</th>

        </tr>
    </thead>
    <tbody>
        <template x-if="currentStudent && currentStudent.enrollments">
            <template x-for="enroll in currentStudent.enrollments" :key="enroll.id">
                <tr>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.name"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.code"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.status"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.units"></td>
                    <td class="border border-gray-300 px-2 py-1" x-text="enroll.course.hours"></td>
                    <td class="border border-gray-300 px-2">
    <button class="px-2 py-1 bg-red-100 text-red-700 rounded" 
            @click="unassign(enroll)">
        حذف
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
            if (this.students.length) {
                this.selectedStudent = this.students[0].number;
                this.selectedSemester = this.semesters[0]?.id;
                this.studentNumberInput = this.selectedStudent;
            }
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
            }
        },

        // عند اختيار الطالب من القائمة
        onStudentChange() {
            const student = this.students.find(s => s.number === this.selectedStudent);
            if(student){
                this.studentNumberInput = student.number;
            }
        },

        // المواد المتاحة للطالب الحالي والفصل الحالي
        available() {

            if (!this.currentStudent || !this.selectedSemester) return [];


            const sectionId = this.currentStudent.section_id;
            const semId = this.selectedSemester;
            const used = new Set(this.assignedList().map(x => x.code));

            const filteredMaterials = this.materials
    .filter(m => Number(m.section_id) === Number(sectionId) &&
                Number(m.semester_id) === Number(semId))
    .filter(m => !used.has(m.code));

// تحويل المصفوفة إلى نص
//alert('المواد المتاحة:\n' + JSON.stringify(filteredMaterials, null, 2));


            return filteredMaterials;
        },
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
   assign(m) {
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
    })
    .then(async res => {
        const data = await res.json();
        console.log('Server response:', data);

        if (!res.ok) {
            alert(Object.values(data.errors ?? { error: [data.message] })
                .flat()
                .join('\n'));
            return;
        }

        // ✅ success
        const list = this.assignedList().slice();
        list.push({...m});
        this.assignments[this.key()] = list;
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('خطأ في الاتصال بالسيرفر');
    });
},

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
        


        // الطباعة
        printResult() {
            window.print();
        },

        // تصدير Excel (قيد التطوير)
        exportExcel() {
            alert('جاهز للربط مع التخزين لاحقًا');
        }

    }));
});
</script>

@endsection
