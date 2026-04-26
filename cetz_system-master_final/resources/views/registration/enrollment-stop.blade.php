@extends('layouts.app')

@section('content')
<div class="space-y-6">
        <div class="flex flex-wrap gap-3">
    @foreach(['جاهز للتجديد', 'تم التجديد', 'موقوف', 'متخرج', 'إعادة'] as $status)
        <div class="flex-1 min-w-[160px] bg-white border rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-500">{{ $status }}</div>
            <div class="text-2xl font-bold">
                {{ $stats[$status] ?? 0 }}
            </div>
        </div>
    @endforeach
</div>

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
        <option value="تم التجديد">تم التجديد</option>
        <option value="جاهز للتجديد">جاهز للتجديد</option>
        <option value="موقوف">موقوف</option>
        <option value="متخرج">متخرج</option>
        <option value="إعادة">إعادة</option>
    </select>

    <button class="px-4 py-2 bg-blue-600 text-white rounded">بحث</button>

</form>
                </div>
          
                
         
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="exportTableToExcel()" 
class="h-10 px-4 bg-green-600 text-white rounded">
⬇️ تصدير Excel
</button>
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
@foreach($students as $student)
<tr class="hover:bg-gray-50">

    <td class="border px-3 py-2">
        {{ $student->student_number ?? $student->manual_number ?? '-' }}
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

      <form onsubmit="return updateStatus(event, {{ $student->id }}, this)">
    @csrf
    <input type="hidden" name="status" value="تم التجديد">

    <button class="px-2 py-1 bg-green-600 text-white rounded text-sm">
        ✅ تجديد
    </button>
</form>

<form onsubmit="return updateStatus(event, {{ $student->id }}, this)">
    @csrf
    <input type="hidden" name="status" value="جاهز للتجديد">

    <button class="px-2 py-1 bg-yellow-500 text-white rounded text-sm">
        ✖ إلغاء
    </button>
</form>

      <form onsubmit="return updateStatus(event, {{ $student->id }}, this)">
    @csrf
    <input type="hidden" name="status" value="موقوف">

    <button class="px-2 py-1 bg-red-600 text-white rounded text-sm">
        ⛔ رفض
    </button>
</form>

    </td>

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
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>

<script>
function updateStatus(e, id, form) {
    e.preventDefault();

    fetch(`/enrollments/${id}/update-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: form.querySelector('input[name="status"]').value
        })
    })
    .then(() => {
        // ما نظهر شيء نهائيًا
        location.reload(); // تحديث بسيط فقط
    })
    .catch(() => {
        alert('حدث خطأ');
    });

    return false;
}

function exportTableToExcel() {

    // ناخذ كل صفوف الجدول من الصفحة (مش pagination فقط)
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

    // دعم العربية + يمين
    ws['!rtl'] = true;

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Students");

    XLSX.writeFile(wb, "students.xlsx");
}
</script>
@endsection
