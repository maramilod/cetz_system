@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto space-y-6" x-data="materialsAssign()" x-init="init()">
     

    {{-- العنوان --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">إدارة المواد</h1>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm"
                    @click="printResult">
                🖨️ طباعة
            </button>
            <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm"
                    @click="exportExcel">
                ⬇️ Excel
            </button>
        </div>
    </div>

    {{-- الفلاتر --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- القسم --}}
      <div>
    <label class="block text-sm font-medium text-gray-600 mb-1">القسم</label>
    <select x-model="selectedDepartment"
            class="border rounded-lg px-3 py-2 w-full">
        <template x-for="d in departments" :key="d.id">
            <option :value="d.id" x-text="d.name"></option>
        </template>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-gray-600 mb-1">الشعبة</label>
    <select x-model="selectedSections"
            class="border rounded-lg px-3 py-2 w-full"
            :disabled="!selectedDepartment">
        <template x-for="s in filteredSections()" :key="s.id">
            <option :value="s.id" x-text="s.name"></option>
        </template>
    </select>
</div>


        
{{-- السيمستر + التواريخ بدون تكرار --}}
@php
$seen = [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- خانة الفصل الدراسي --}}
    <div>
            <label class="block text-sm text-gray-600 mb-1">الفصل</label>
        <select x-model="selectedSemester"
        class="border rounded px-3 py-2 w-full">
    <template x-for="s in semesters" :key="s.id">
        <option :value="s.id" x-text="s.label"></option>
    </template>
</select>
        </div>
  

    </div>    </div>


    {{-- جدول المواد المتاحة --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 space-y-4">

        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg text-gray-800">📚 المواد المتاحة</h2>

         
        </div>

<div class="mt-6 overflow-x-auto bg-white rounded-xl shadow border">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="border px-3 py-2 text-center">#</th>
                <th class="border px-3 py-2 text-right">رمز المادة</th>
                <th class="border px-3 py-2 text-right">اسم المادة</th>
                <th class="border px-3 py-2 text-center">الوحدات</th>
                <th class="border px-3 py-2 text-center">الساعات</th>
                <th class="border px-3 py-2 text-center">الفصل</th>
                <th class="border px-3 py-2 text-center">الإجراءات</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            <template x-for="(m, index) in filteredM()" :key="m.id">
                <tr class="hover:bg-gray-50 transition">
                    <td class="border px-3 py-2 text-center" x-text="index + 1"></td>

                    <td class="border px-3 py-2 font-mono" x-text="m.code"></td>

                    <td class="border px-3 py-2" x-text="m.name"></td>

                    <td class="border px-3 py-2 text-center" x-text="m.units"></td>

                    <td class="border px-3 py-2 text-center" x-text="m.hours"></td>

                    <td class="border px-3 py-2 text-center text-gray-600"
                        x-text="m.semester_name"></td>

                    <td class="border px-3 py-2 text-center space-x-1 space-x-reverse">
                        <!-- تعديل -->
                       <button
    class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs"
   @click="editeMaterial(m)">
    ✏️ تعديل
</button>


                        <!-- إسقاط -->
                        <button
                            x-show="m.status === 'active'"

                            class="px-2 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs"
                            @click="dropMaterial(m)">
                            ⛔ إسقاط
                        </button>

                         <button
                             x-show="m.status === 'dropped'"

                            class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs"
                            @click="restoreMaterial(m)">
                           ♻️ إلغاء الإسقاط
                        </button>



                        <!-- حذف -->
                        <button
                            class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                            @click="deleteMaterial(m)">
                            🗑 حذف
                        </button>
                    </td>
                </tr>
            </template>

            <!-- لا توجد مواد -->
            <tr x-show="filteredM().length === 0">
                <td colspan="7" class="border px-4 py-6 text-center text-gray-500">
                    لا توجد مواد لهذا الفصل
                </td>
            </tr>
        </tbody>
    </table>
</div>



  



<!-- Modal نقل التسجيلات -->
<div x-show="showTransferModal"
     x-transition
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">

        <h2 class="text-lg font-bold mb-4 text-gray-800">
            نقل تسجيلات الطلاب
        </h2>

        <p class="text-sm text-gray-600 mb-3">
            يوجد طلاب مسجلين في هذه المادة.
            اختر عرضاً بديلاً لنقل التسجيلات إليه.
        </p>

        <select x-model="selectedNewOffering"
                class="w-full border rounded-lg px-3 py-2 mb-4">
            <option value="">اختر عرض بديل</option>
            <template x-for="o in alternativeOfferings" :key="o.id">
              <option :value="o.id"
        x-text="o.course_code + ' - ' + o.course_name + ' | ' + o.section_name + ' - ' + o.semester_name">
</option>

            </template>
        </select>

        <div class="flex justify-end gap-2">
            <button class="px-4 py-2 bg-gray-300 rounded"
                    @click="showTransferModal = false">
                إلغاء
            </button>

            <button class="px-4 py-2 bg-red-600 text-white rounded"
                    @click="confirmTransfer()">
                نقل وحذف
            </button>
        </div>

    </div>
</div>
</div>
 <script>
function csrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}


document.addEventListener('alpine:init', () => {
    Alpine.data('materialsAssign', () => ({
        showTransferModal: false,
selectedNewOffering: '',
alternativeOfferings: [],
currentDeletingMaterial: null,

      departments: @json($departments),
sections: @json($sections),
materials: @json($materials),
semesters: @json($semesters),

selectedDepartment: '',
selectedSections: '',
selectedSemester: '',

dropMaterial(m) {
    if (!confirm(`هل تريد إسقاط المادة "${m.name}"؟`)) return;

    fetch(`/courses/${m.id}/drop`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            m.status = 'dropped'; 
        }
    });
},
editeMaterial(m) {
 window.location.href = `/courses/${m.course_id}/edit`;
},

restoreMaterial(m) {
    if (!confirm(`إلغاء إسقاط المادة "${m.name}"؟`)) return;

    fetch(`/courses/${m.id}/restore`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            m.status = 'active';
        }
    });
},
deleteMaterial(m) {
    if (!confirm(`هل أنت متأكد من حذف المادة "${m.name}"؟`)) return;

    // 1️⃣ تحقق إذا المادة مرتبطة بطلاب
    fetch(`/course-offerings/${m.id}/has-enrollments`)
        .then(res => res.json())
        .then(hasEnrollments => {

            if (!hasEnrollments) {
                // لا يوجد طلاب → حذف مباشر
                this.forceDelete(m, null, true);
            } else {
                // يوجد طلاب → جلب العروض البديلة
                fetch(`/course-offerings/${m.id}/alternatives`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            // فتح المودال
                            this.alternativeOfferings = data;
                            this.currentDeletingMaterial = m;
                            this.showTransferModal = true;
                        } else {
                            alert("يوجد طلاب مسجلين، لكن لا توجد عروض بديلة لنقلهم!");
                        }
                    });
            }
        });
}
,
forceDelete(m, newOfferingId) {

    fetch(`/courses/${m.id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            new_offering_id: newOfferingId
        })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            this.materials = this.materials.filter(x => x.id !== m.id);
            this.showTransferModal = false;
            alert(data.message);
        } else {
            alert(data.message || 'فشل العملية');
        }
    });
},

confirmTransfer() {

    if (!this.selectedNewOffering) {
        alert('يجب اختيار عرض بديل');
        return;
    }

    this.forceDelete(this.currentDeletingMaterial, this.selectedNewOffering);
},

filteredBySemester() {
    if (!this.selectedSemester) return [];

    return this.materials.filter(m =>
        Number(m.semester_id) === Number(this.selectedSemester)
    );
},

init() {
    // تعيين القيم الافتراضية
    if (this.departments.length > 0) {
        this.selectedDepartment = this.departments[0].id;
        const firstSection = this.sections.find(s => s.department_id == this.selectedDepartment);
        if (firstSection) this.selectedSections = firstSection.id;
    }
    if (this.semesters.length > 0) {
        this.selectedSemester = this.semesters[0].id;
    }

    // مراقبة التغيرات
    this.$watch('selectedDepartment', value => {
     
        // إعادة تهيئة الشعبة بعد تغيير القسم
        const firstSection = this.sections.find(s => s.department_id == value);
        this.selectedSections = firstSection ? firstSection.id : '';
    });

    this.$watch('selectedSections', value => {
    
    });

    this.$watch('selectedSemester', value => {
         });
},

get currentSem() {
    // إذا لم يتم اختيار شعبة بعد
    if (!this.selectedSections) return null;

    // إرجاع قيمة الشعبة المختارة مباشرة (ID)
    return this.selectedSections;
},

        // مفتاح التعيينات
        key() { 
            return this.currentSem + '|' + this.selectedSemester; 
        },

filteredSections() {
    if (!this.selectedDepartment) return [];
    return this.sections.filter(
        s => Number(s.department_id) === Number(this.selectedDepartment)
    );
},
filteredAvailable() {
    if (!this.selectedSections || !this.selectedSemester) return [];

    const sectionId = this.selectedSections;
    const semId = this.selectedSemester;
    const used = new Set(this.assignedList().map(x => x.code));
    let result = this.materials
        .filter(m => Number(m.section_id) === Number(sectionId) &&
                     Number(m.semester_id) === Number(semId))
        .filter(m => !used.has(m.code));

    alert('المواد المتاحة:\n' + JSON.stringify(result, null, 2));

    // تطبيق البحث النصي إذا كان هناك نص
    if (this.searchAvailable && this.searchAvailable.trim() !== '') {
        const search = this.searchAvailable.trim().toLowerCase();
        result = result.filter(m =>
            [m.code, m.name].some(v => v?.toString().toLowerCase().includes(search))
        );
    }

    return result;
},

filteredM() {
    if (!this.selectedSections || !this.selectedSemester) return [];

    return this.materials.filter(m =>
        Number(m.section_id) === Number(this.selectedSections) &&
        Number(m.semester_id) === Number(this.selectedSemester)
    );

    
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
