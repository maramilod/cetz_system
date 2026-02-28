@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="enrollmentStop()" x-init="applyFilters()">
    <div class="flex flex-wrap gap-3">
        <template x-for="status in statusOptions" :key="status">
            <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500" x-text="status"></div>
                <div class="text-2xl font-bold" x-text="statusCount(status)"></div>
            </div>
        </template>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-wrap gap-3 items-end flex-1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-600 mb-1">الاسم</label>
                    <input type="text" x-model.trim="nameFilter" @input.debounce.300="applyFilters" placeholder="ابحث باسم الطالب او رقم القيد او اليدوي" class="border rounded px-3 py-2 w-full">
                </div>
          
                
              <div class="min-w-[160px]">
                    <label class="block text-sm text-gray-600 mb-1">حالة الطلب</label>
                    <select x-model="statusFilter" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">الكل</option>
                        <template x-for="status in statusOptions" :key="'filter-' + status">
                            <option x-text="status" :value="status"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="printTable" class="h-10 px-4 bg-gray-200 rounded">🖨️ طباعة</button>
                <button type="button" @click="exportExcel" class="h-10 px-4 bg-green-600 text-white rounded">⬇️ تصدير excel</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رقم الطلب</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">الحالة</th>
                                <th class="border px-3 py-2 text-right">الإجراءات</th>

                    </tr>
                </thead>
              <tbody>
    <template x-if="!requests.length">
        <tr>
            <td colspan="3" class="border px-3 py-4 text-center text-gray-500">
                لا توجد طلبات مطابقة للبحث الحالي.
            </td>
        </tr>
    </template>

    <template x-for="student in requests" :key="student.id">
        <tr class="hover:bg-gray-50">
            <td class="border px-3 py-2" x-text="student.student_number ?? student.manual_number ?? '-'"></td>
            <td class="border px-3 py-2" x-text="student.full_name ?? '-'"></td>
            <td class="border px-3 py-2">
                <span 
                    :class="{
                        'bg-green-100 text-green-700': student.current_status === 'تم التجديد',
                        'bg-red-100 text-red-700': student.current_status === 'موقوف',
                        'bg-yellow-100 text-yellow-700': student.current_status === 'قيد المراجعة',
                        'bg-blue-100 text-blue-700': student.current_status === 'جاهز للتجديد',
                        'bg-gray-200 text-gray-700': student.current_status === 'متخرج',
                        'bg-gray-100 text-gray-700': !student.current_status
                    }"
                    class="px-2 py-1 rounded"
                    x-text="student.current_status ?? 'غير محدد'">
                </span>
            </td>
                <td class="border px-3 py-2 flex gap-2">
            <button 
                class="px-2 py-1 bg-green-600 text-white rounded text-sm"
                @click="updateStatus(student.id, 'تم التجديد')">
                ✅ تجديد
            </button>
            <button 
                class="px-2 py-1 bg-yellow-500 text-white rounded text-sm"
                @click="updateStatus(student.id, 'جاهز للتجديد')">
                ✖ إلغاء التجديد
            </button>
            <button 
                class="px-2 py-1 bg-red-600 text-white rounded text-sm"
                @click="updateStatus(student.id, 'موقوف')">
                ⛔ رفض
            </button>
        </td>
        </tr>
    </template>
</tbody>

            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/xlsx.full.min.js') }}"></script><script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('enrollmentStop', () => ({
            requestsSeed: @json($students), // جميع الطلاب هنا
            requests: @json($students),     // نسخة للفلترة
            nameFilter: '',
            statusFilter: '',
            semesterFilter: '',

            statusOptions: ['جاهز للتجديد', 'تم التجديد', 'موقوف', 'متخرج'],

            applyFilters() {
    const query = this.nameFilter.trim().toLowerCase(); // تحويل البحث إلى lowercase لتسهيل المطابقة
    const status = this.statusFilter;
    const sem = this.semesterFilter;

    this.requests = this.requestsSeed.filter(row => {
        const matchesNameOrNumber = !query || 
            (row.full_name && row.full_name.toLowerCase().includes(query)) ||
            (row.student_number && row.student_number.toString().includes(query)) ||
            (row.manual_number && row.manual_number.toString().includes(query));

        const matchesSem = !sem || row.semester === sem;
        const matchesStatus = !status || row.current_status === status;

        return matchesNameOrNumber && matchesSem && matchesStatus;
    });
},

            statusCount(status) {
                return this.requests.filter(row => row.current_status === status).length;
            },
            updateStatus(studentId, newStatus) {
    const student = this.requests.find(s => s.id === studentId);
    if (!student) return;

    // تحديث الحالة محليًا
    student.current_status = newStatus;

    // إرسال التحديث للسيرفر (AJAX)
    fetch(`/enrollments/${studentId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
        } else {
            alert('حدث خطأ أثناء التحديث');
        }
    })
    .catch(err => {
        console.error(err);
        alert('خطأ في الاتصال بالسيرفر');
    });
},

            exportExcel() {
                if (!this.requests.length) return alert('لا توجد بيانات لتصديرها.');
                const data = this.requests.map(row => ({
                    'رقم الطالب': row.student_number ?? row.manual_number ?? '-',
                    'اسم الطالب': row.full_name ?? '-',
                    'الحالة': row.current_status ?? 'غير محدد',
                }));
                const ws = XLSX.utils.json_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Students");
                XLSX.writeFile(wb, "students-list.xlsx");
            },
            

            printTable() {
                const tableHtml = document.querySelector('table').outerHTML;
                const newWin = window.open('', '_blank', 'width=800,height=600');
                newWin.document.write(`
                    <html>
                        <head>
                            <title>طباعة الجدول</title>
                            <style>
                                table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; direction: rtl; }
                                th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
                                th { background-color: #f0f0f0; }
                            </style>
                        </head>
                        <body>${tableHtml}</body>
                    </html>
                `);
                newWin.document.close();
                newWin.focus();
                newWin.print();
                newWin.close();
            },
        }));
    });
</script>

@endsection
