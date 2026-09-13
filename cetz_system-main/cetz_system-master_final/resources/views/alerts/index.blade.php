@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6">

    <!-- إشعارات نجاح -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- البحث / الفلتر -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-lg mb-2">فلتر التنبيهات</h2>
        <input type="text" id="filterInput" placeholder="ابحث باستخدام اي حقل     "
               class="border rounded px-3 py-2 w-full" onkeyup="filterAlerts()">
    </div>

    <!-- جدول التنبيهات -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold text-lg mb-2">قائمة التنبيهات</h2>
        <table class="min-w-full border text-sm" id="alertsTable">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">#</th>
                    <th class="border px-2 py-1">عنوان التنبيه</th>
                    <th class="border px-2 py-1">نص التنبيه</th>
                    <th class="border px-2 py-1">الطالب</th>
                    <th class="border px-2 py-1">رقم القيد</th>
                    <th class="border px-2 py-1">مرسل التنبيه</th>
                    <th class="border px-2 py-1">تاريخ الإنشاء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $i => $alert)
                <tr>
                    <td class="border px-2 py-1">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1">{{ $alert->title }}</td>
                    <td class="border px-2 py-1">{{ $alert->body }}</td>
                    <td class="border px-2 py-1 student-name">{{ $alert->student->full_name ?? '-' }}</td>
                    <td class="border px-2 py-1 student-number">{{ $alert->student_id ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ $alert->sender_name }}</td>
                    <td class="border px-2 py-1">{{ $alert->created_at->format('Y-m-d H:i') }}</td>

                </tr>
                @empty
                <tr>
                    <td colspan="8" class="border px-2 py-1 text-center">لا توجد تنبيهات حتى الآن</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
function filterAlerts() {
    const input = document.getElementById('filterInput').value.toLowerCase();
    const table = document.getElementById('alertsTable');
    const trs = table.querySelectorAll('tbody tr');

    trs.forEach(tr => {
        // اجمع كل نصوص الخلايا في الصف
        const rowText = Array.from(tr.querySelectorAll('td'))
                             .map(td => td.textContent.toLowerCase())
                             .join(' ');

        // إذا كان النص موجودًا في أي خلية أظهر الصف وإلا أخفِه
        if (rowText.includes(input)) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}
</script>


@endsection
