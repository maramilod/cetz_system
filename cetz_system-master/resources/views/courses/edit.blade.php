@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto space-y-6" x-data="materialForm()" x-init="init()">

    {{-- العنوان --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800" x-text="course.id ? 'تعديل المادة' : 'إضافة مادة جديدة'"></h1>
    </div>

    {{-- الفورم --}}
    <form @submit.prevent="submitForm" class="bg-white rounded-xl shadow-sm border p-4 space-y-6">

        {{-- اسم المادة --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">اسم المادة</label>
            <input type="text" x-model="form.name" class="border rounded w-full px-3 py-2" required>
        </div>

        {{-- رمز المادة --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">رمز المادة</label>
            <input type="text" x-model="form.course_code" class="border rounded w-full px-3 py-2" required>
        </div>

        {{-- الساعات والوحدات --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">الساعات</label>
                <input type="number" x-model="form.hours" class="border rounded w-full px-3 py-2" min="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">الوحدات</label>
                <input type="number" x-model="form.units" class="border rounded w-full px-3 py-2" min="0" required>
            </div>
        </div>

        {{-- المادة السابقة --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">المادة السابقة (اختياري)</label>
            <select x-model="form.prerequisite_course_id" class="border rounded px-3 py-2 w-full">
                <option value="">لا توجد مادة سابقة</option>
                @foreach($allCourses as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->course_code }})</option>
                @endforeach
            </select>
        </div>

        {{-- المادة العملية --}}
       <div>
    <label class="inline-flex items-center">
        <input type="checkbox" x-model="form.has_practical" class="form-checkbox">
        <span class="ml-2">تحتوي على جزء عملي</span>
    </label>
</div>



    

        {{-- زر الحفظ --}}
        <div class="mt-4">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                حفظ المادة
            </button>
        </div>

    </form>
</div>

<script>
function csrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

document.addEventListener('alpine:init', () => {
    Alpine.data('materialForm', () => ({
        course: @json($course),
        allCourses: @json($allCourses),

        form: {
            course_id: @json($course->id ?? null),
            name: @json($course->name ?? ''),
            course_code: @json($course->course_code ?? ''),
            hours: @json($course->hours ?? 0),
            units: @json($course->units ?? 0),
            has_practical: @json($course->has_practical ?? 0),
            prerequisite_course_id: @json($course->prerequisite_course_id ?? ''),

        },

        init() {
            // أي إعدادات إضافية هنا
        },
submitForm() {
    // تحويل القيم الرقمية إلى أرقام
    const payload = {
        ...this.form,
                has_practical: this.form.has_practical ? 1 : 0, // تحويل Boolean إلى 0/1


        hours: Number(this.form.hours),
        units: Number(this.form.units),
        has_practical: this.form.has_practical ? 1 : 0,
        prerequisite_course_id: this.form.prerequisite_course_id || null
    };

    fetch(`{{ route('courses.update', $course->id) }}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => {
        if (!res.ok) return res.json().then(err => { throw err });
        return res.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        }
    })
    .catch(err => {
        console.error(err);
        alert('خطأ في الحفظ: تحقق من البيانات.');
    });
}

    }));
});
</script>

@endsection
