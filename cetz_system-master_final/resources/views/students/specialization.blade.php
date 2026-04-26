@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">بيانات الطالب</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {{-- صورة الطالب --}}
        <div class="text-center">
            <img src="{{ $student->photo ? asset('storage/students/'.$student->photo) : asset('images/default-user.png') }}" 
                 alt="صورة الطالب" 
                 class="w-32 h-32 object-cover rounded-full mx-auto border mb-2">
        </div>

        {{-- بيانات الطالب --}}
        <div class="space-y-2">
            <p><strong>الاسم الكامل:</strong> {{ $student->full_name }}</p>
            <p><strong>الرقم الجامعي:</strong> {{ $student->student_number ?? $student->manual_number }}</p>
            <p><strong>القسم الحالي:</strong> <span id="studentDepartment">{{ $student->department->name ?? '-' }}</span></p>
        </div>
    </div>

    {{-- جدول تاريخ الطالب --}}
    <div class="bg-white shadow rounded p-4 mb-6">
        <h2 class="text-xl font-semibold mb-3">تاريخ الطالب</h2>
        <table class="w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">الفصل</th>
                    <th class="border px-4 py-2">المادة</th>
                    <th class="border px-4 py-2">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                    <tr>
                        <td class="border px-4 py-2">{{ $h['semester_name'] }}</td>
                        <td class="border px-4 py-2">{{ $h['course_name'] }} ({{ $h['course_code'] }})</td>
                        <td class="border px-4 py-2">{{ $h['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="border px-4 py-2 text-center" colspan="4">لا توجد مواد مأخوذة للطالب</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- جدول مواد الشعبة الحالية --}}
    <div class="bg-white shadow rounded p-4 mb-6">
        <h2 class="text-xl font-semibold mb-3">مواد الشعبة الحالية</h2>
        <table class="w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">المادة</th>
                    <th class="border px-4 py-2">الكود</th>
                    <th class="border px-4 py-2">الوحدات</th>
                    <th class="border px-4 py-2">الساعات</th>
                    <th class="border px-4 py-2">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $c)
                    <tr>
                        <td class="border px-4 py-2">{{ $c['course_name'] }}</td>
                        <td class="border px-4 py-2">{{ $c['course_code'] }}</td>
                        <td class="border px-4 py-2">{{ $c['units'] }}</td>
                        <td class="border px-4 py-2">{{ $c['hours'] }}</td>
                        <td class="border px-4 py-2">{{ $c['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="border px-4 py-2 text-center" colspan="5">لا توجد مواد للشعبة الحالية</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- زر الرجوع --}}
    <div class="mt-6">
        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-200 rounded">عودة</a>
    </div>

    {{-- نموذج تغيير الشعبة --}}
<h3 class="text-lg font-semibold mt-6 mb-2">تغيير الشعبة</h3>
<form id="updateSectionForm" method="POST" action="{{ route('students.updateSection', $student->id) }}">
    @csrf
    @method('PUT')
    <div class="flex items-center gap-4">
        <div>
            <label>القسم</label>
            <select name="department_id" id="departmentSection" class="border p-2 rounded">
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" data-is-general="{{ $d->is_general }}" {{ $student->department_id == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div id="sectionSectionWrapper">
            <label>الشعبة</label>
            <select name="section_id" id="sectionSection" class="border p-2 rounded">
                <option value="">-- اختر الشعبة --</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}" data-department="{{ $s->department_id }}" {{ $student->section_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ التغيير</button>
    </div>
</form>

<script>
document.getElementById('departmentSection').addEventListener('change', function () {
    const selectedDept = this.value;
    const sectionSelect = document.getElementById('sectionSection');
    const options = sectionSelect.querySelectorAll('option');

    options.forEach(option => {
        if(option.value === "") return; // الاحتفاظ بالاختيار الافتراضي
        option.style.display = option.dataset.department == selectedDept ? 'block' : 'none';
    });

    sectionSelect.value = "";


});
// اختياري: إرسال الفورم باستخدام AJAX
document.getElementById('updateSectionForm').addEventListener('submit', function(e){
    e.preventDefault();
    const form = this;
    const data = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
        // تحديث اسم القسم والشعبة المعروض
        document.querySelector('#studentDepartment').textContent = res.department;
        document.querySelector('#studentSection').textContent = res.section;
    })
    .catch(err => console.error(err));
});
</script>
@endsection


