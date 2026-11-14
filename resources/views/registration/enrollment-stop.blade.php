@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- كروت الإحصائيات --}}
    <div class="flex flex-wrap gap-3">
        @foreach(['جاهز للتجديد', 'تم التجديد', 'موقوف', 'متخرج', 'اعادة'] as $status)
            <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-sm text-gray-500">{{ $status }}</div>
                <div class="text-2xl font-bold">
                    {{ $stats[$status] ?? 0 }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- قسم البحث والفلاتر --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-wrap gap-3 items-end flex-1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-600 mb-1">الاسم</label>
                    <form method="GET" class="flex gap-3 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="ابحث باسم الطالب او رقم القيد"
                            class="border rounded px-3 py-2">

                        <select name="status" class="border rounded px-3 py-2">
                            <option value="">الكل</option>
                            <option value="تم التجديد" {{ request('status') == 'تم التجديد' ? 'selected' : '' }}>تم التجديد</option>
                            <option value="جاهز للتجديد" {{ request('status') == 'جاهز للتجديد' ? 'selected' : '' }}>جاهز للتجديد</option>
                            <option value="موقوف" {{ request('status') == 'موقوف' ? 'selected' : '' }}>موقوف</option>
                            <option value="متخرج" {{ request('status') == 'متخرج' ? 'selected' : '' }}>متخرج</option>
                            <option value="اعادة" {{ request('status') == 'اعادة' ? 'selected' : '' }}>اعادة</option>
                        </select>

                        <button class="px-4 py-2 bg-blue-600 text-white rounded">بحث</button>
                    </form>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="exportTableToExcel()" class="h-10 px-4 bg-green-600 text-white rounded">
                    ⬇️ تصدير Excel
                </button>
            </div>
        </div>

        {{-- جدول عرض الطلاب --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-right">رقم الطلب</th>
                        <th class="border px-3 py-2 text-right">اسم الطالب</th>
                        <th class="border px-3 py-2 text-right">الحالة</th>
                        <th class="border px-3 py-2 text-right">اجراءات القيد</th>
                        <th class="border px-3 py-2 text-right">المواد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-3 py-2">
                            {{ $student->manual_number ?? $student->student_number ?? '-' }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ $student->full_name }}
                        </td>

                        <td class="border px-3 py-2">
                            <span class="px-2 py-1 rounded text-sm
                                {{ $student->current_status == 'تم التجديد' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $student->current_status == 'موقوف' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $student->current_status == 'جاهز للتجديد' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $student->current_status == 'متخرج' ? 'bg-gray-200 text-gray-700' : '' }}
                                {{ $student->current_status == 'اعادة' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            ">
                                {{ $student->current_status ?? 'غير محدد' }}
                            </span>
                        </td>

                        <td class="border px-3 py-2 flex gap-2">
                            {{-- تجديد --}}
                            <form onsubmit="return updateStatus(event, {{ $student->id }}, 'تم التجديد')">
                                @csrf
                                <button class="px-2 py-1 bg-green-600 text-white rounded text-sm">
                                    ✅ تجديد
                                </button>
                            </form>

                            {{-- إلغاء --}}
                            <form onsubmit="return updateStatus(event, {{ $student->id }}, 'جاهز للتجديد')">
                                @csrf
                                <button class="px-2 py-1 bg-yellow-500 text-white rounded text-sm">
                                    ✖ إلغاء
                                </button>
                            </form>

                            {{-- إيقاف (يفتح الـ Modal) --}}
                            <button type="button" onclick="openStopModal({{ $student->id }}, '{{ $student->full_name }}')" class="px-2 py-1 bg-red-600 text-white rounded text-sm">
                                ⛔ ايقاف
                            </button>
                        </td>

                        @if($student->current_status == 'تم التجديد' || $student->current_status == 'اعادة')
                        <td class="border px-3 py-2">
                            <a href="/download-materials?student={{ $student->id }}" class="px-2 py-1 bg-blue-600 text-white rounded text-sm">
                                تنزيل المواد
                            </a>
                        </td>
                        @else
                        <td class="border px-3 py-2 text-gray-400 text-sm">
                            غير متاح
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $students->links() }}
        </div>
    </div>
</div>

{{-- Modal نافذة الإيقاف المنبثقة --}}
<div id="stopModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl border space-y-4">
        <h3 class="text-lg font-bold text-gray-800">إيقاف قيد الطالب</h3>
        <p id="studentNameDisplay" class="text-sm font-semibold text-gray-600"></p>
        
        {{-- ⚠️ نص تحذيري عن حذف المواد --}}
        <div class="p-3 bg-red-50 border-r-4 border-red-500 rounded text-red-700 text-xs leading-relaxed">
            ⚠️ <strong>تنبيه هام:</strong> سيؤدي تأكيد الإيقاف إلى <strong>حذف جميع المواد المسجلة حالياً</strong> للطالب.
        </div>

        <input type="hidden" id="modalStudentId">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">سبب الإيقاف (اختياري)</label>
            <textarea id="stopReason" rows="3" class="w-full border rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="اكتب سبب الإيقاف هنا إن وجد..."></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="closeStopModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                إغلاق
            </button>
            <button type="button" onclick="confirmStop()" class="px-4 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                تأكيد الإيقاف والحذف
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/xlsx.full.min.js') }}"></script>

<script>
// فتح Modal الإيقاف
function openStopModal(studentId, studentName) {
    document.getElementById('modalStudentId').value = studentId;
    document.getElementById('studentNameDisplay').innerText = `الطالب: ${studentName}`;
    document.getElementById('stopReason').value = ''; // تفريغ حقل النص
    document.getElementById('stopModal').classList.remove('hidden');
}

// إغلاق Modal الإيقاف
function closeStopModal() {
    document.getElementById('stopModal').classList.add('hidden');
}

// تأكيد عملية الإيقاف وإرسال البيانات
function confirmStop() {
    const studentId = document.getElementById('modalStudentId').value;
    const reason = document.getElementById('stopReason').value;

    // إرسال الطلب
    sendUpdateStatusRequest(studentId, 'موقوف', reason);
    
    // إغلاق Modal بعد الإرسال
    closeStopModal();
}

// تحديث الحالات العادية مباشرة
function updateStatus(e, id, status) {
    e.preventDefault();
    sendUpdateStatusRequest(id, status, null);
    return false;
}

// دالة إرسال الطلب عبر Fetch
function sendUpdateStatusRequest(id, status, reason = null) {
    fetch(`/enrollments/${id}/update-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: status,
            reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء التحديث');
        }
    })
    .catch(() => {
        alert('حدث خطأ بالاتصال');
    });
}

function exportTableToExcel() {
    let rows = [];

    document.querySelectorAll("table tbody tr").forEach(tr => {
        const tds = tr.querySelectorAll("td");

        if (tds.length === 0) return;

        rows.push({
            'رقم الطالب': tds[0]?.innerText.trim() ?? '-',
            'اسم الطالب': tds[1]?.innerText.trim() ?? '-',
            'الحالة': tds[2]?.innerText.trim() ?? '-',
        });
    });

    const ws = XLSX.utils.json_to_sheet(rows);
    ws['!rtl'] = true;

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Students");

    XLSX.writeFile(wb, "students.xlsx");
}
</script>
@endsection