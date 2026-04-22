@extends('layouts.app')

@section('content')

<div class="space-y-6" x-data="certificateGenerator(@js($students), @js($institute))">

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">إنشاء تعريف طالب</h1>
        <p class="text-gray-600">اختر الطالب وأدخل سبب إصدار التعريف ثم اطبع الوثيقة.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- الطالب -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">الطالب</label>

                <div class="relative">
                    <input
                        type="text"
                        x-model="studentSearch"
                        @input="filterStudents"
                        placeholder="اكتب اسم الطالب..."
                        class="border rounded px-3 py-2 w-full"
                    >

                    <div class="absolute z-50 bg-white border w-full max-h-48 overflow-y-auto mt-1"
                         x-show="studentSearch.length > 0 && filteredStudents.length">

                        <template x-for="student in filteredStudents" :key="student.number">
                            <div
                                @click="selectStudent(student)"
                                class="p-2 hover:bg-gray-100 cursor-pointer"
                                x-text="student.name"
                            ></div>
                        </template>

                    </div>
                </div>
            </div>

            <!-- الرقم الجامعي -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">الرقم الجامعي</label>

                <div class="relative">
                    <input
                        type="text"
                        x-model="numberSearch"
                        @input="filterNumbers"
                        placeholder="اكتب الرقم الجامعي..."
                        class="border rounded px-3 py-2 w-full"
                    >

                    <div class="absolute z-50 bg-white border w-full max-h-48 overflow-y-auto mt-1"
                         x-show="numberSearch.length > 0 && filteredNumbers.length">

                        <template x-for="student in filteredNumbers" :key="student.number">
                            <div
                                @click="selectNumber(student)"
                                class="p-2 hover:bg-gray-100 cursor-pointer"
                                x-text="student.number"
                            ></div>
                        </template>

                    </div>
                </div>
            </div>

            <!-- التاريخ -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">تاريخ الإصدار</label>
                <input type="date" x-model="issueDate" class="border rounded px-3 py-2 w-full">
            </div>

            <!-- السبب -->
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">سبب إصدار التعريف</label>
                <input type="text" x-model="purpose" placeholder="مثال: تقديم للمنحة الدراسية"
                       class="border rounded px-3 py-2 w-full">
            </div>

            <!-- ملاحظات -->
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">ملاحظات إضافية (اختياري)</label>
                <textarea x-model="notes" rows="2" class="border rounded px-3 py-2 w-full"></textarea>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                    @click="generateCertificate"
                    :disabled="!selectedNumber || !issueDate || !purpose">
                معاينة الشهادة
            </button>

            <button type="button"
                    class="px-4 py-2 bg-gray-200 rounded"
                    @click="resetForm">
                إعادة الضبط
            </button>
        </div>

        <template x-if="alertMessage">
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded" x-text="alertMessage"></div>
        </template>
    </div>

    <!-- المعاينة -->
    <div x-show="certificateHtml" class="bg-white rounded-lg shadow p-6 space-y-4" x-cloak>

        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">المعاينة</h2>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-gray-200 rounded" @click="downloadHtml">⬇️ تنزيل</button>
                <button class="px-4 py-2 bg-gray-100 border rounded" @click="printCertificate">🖨️ طباعة</button>
            </div>
        </div>

        <div class="border rounded-xl p-6 space-y-4" x-html="certificateHtml"></div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
Alpine.data('certificateGenerator', (studentsFromDb, instituteData) => ({

    students: studentsFromDb,
    institute: instituteData,

    selectedNumber: '',
    studentSearch: '',
    numberSearch: '',

    filteredStudents: [],
    filteredNumbers: [],

    issueDate: new Date().toISOString().slice(0, 10),
    purpose: '',
    notes: '',
    alertMessage: '',
    certificateHtml: '',

    /* ===================== البحث ===================== */

    filterStudents() {
        this.filteredStudents = this.students.filter(s =>
            s.name.toLowerCase().includes(this.studentSearch.toLowerCase())
        );
    },

    selectStudent(student) {
        this.selectedNumber = student.number;
        this.studentSearch = student.name;
        this.numberSearch = student.number;
        this.filteredStudents = [];
        this.filteredNumbers = [];
    },

    filterNumbers() {
        this.filteredNumbers = this.students.filter(s =>
            String(s.number).includes(this.numberSearch)
        );
    },

    selectNumber(student) {
        this.selectedNumber = student.number;
        this.numberSearch = student.number;
        this.studentSearch = student.name;
        this.filteredNumbers = [];
        this.filteredStudents = [];
    },

    /* ===================== الشهادة ===================== */

    generateCertificate() {

        const student = this.students.find(item =>
            String(item.number) === String(this.selectedNumber)
        );

        if (!student) {
            this.alertMessage = 'يرجى اختيار الطالب أولاً.';
            return;
        }

        const issueDateFormatted = new Date(this.issueDate).toLocaleDateString('ar-LY', {
            year: 'numeric', month: 'long', day: 'numeric'
        });

        let parts = [];

        parts.push('<div class="text-center space-y-2">');
        parts.push('<h1 class="text-2xl font-bold">' + this.escapeHtml(this.institute?.name ?? '') + '</h1>');
        parts.push('<p class="text-sm text-gray-600">' +
            this.escapeHtml(this.institute?.address ?? '') +
            ' — ' +
            this.escapeHtml(this.institute?.phone ?? '') +
            '</p>');
        parts.push('<hr class="my-4">');
        parts.push('<h2 class="text-xl font-semibold">تعريف طالب</h2>');
        parts.push('</div>');

        parts.push('<div class="space-y-2 text-right leading-8">');
        parts.push('<p>تشهد إدارة <strong>' + this.escapeHtml(this.institute?.name ?? '') +
            '</strong> بأن الطالب/ة <strong>' + this.escapeHtml(student.name) +
            '</strong> والرقم الجامعي <strong>' + this.escapeHtml(student.number) +
            '</strong> مسجل/ة بقسم <strong>' + this.escapeHtml(student.department) +
            '</strong>.</p>');

        parts.push('<p>تم إصدار التعريف بتاريخ <strong>' + this.escapeHtml(issueDateFormatted) +
            '</strong> لغرض <strong>' + this.escapeHtml(this.purpose) + '</strong>.</p>');

        parts.push('<p>الرقم الوطني: <strong>' + this.escapeHtml(student.nationalId) + '</strong></p>');

        if (this.notes.trim()) {
            parts.push('<p class="mt-4">ملاحظات: ' + this.escapeHtml(this.notes) + '</p>');
        }

        parts.push('</div>');

        parts.push('<div class="mt-8 flex justify-between text-sm">');
        parts.push('<div><p>التوقيع:</p><p class="mt-6">__________</p></div>');
        parts.push('<div><p>الختم</p><p class="mt-6">__________</p></div>');
        parts.push('</div>');

        this.certificateHtml = parts.join('');
        this.alertMessage = 'تم تجهيز التعريف بنجاح';
    },

    resetForm() {
        this.selectedNumber = '';
        this.studentSearch = '';
        this.numberSearch = '';
        this.issueDate = new Date().toISOString().slice(0, 10);
        this.purpose = '';
        this.notes = '';
        this.certificateHtml = '';
        this.alertMessage = '';
    },

    printCertificate() {
        const html = '<html><body>' + this.certificateHtml + '</body></html>';
        const w = window.open();
        w.document.write(html);
        w.print();
    },

    downloadHtml() {
        const blob = new Blob([this.certificateHtml], { type: 'text/html' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'certificate.html';
        a.click();
    },

    escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, m =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
        );
    }

}));
});
</script>

@endsection