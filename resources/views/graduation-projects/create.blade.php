@extends('layouts.app')

@section('content')


<div class="max-w-7xl mx-auto p-6" x-data="graduationProjectForm()">

    <h1 class="text-2xl font-bold mb-6">إنشاء مشروع تخرج جديد</h1>

    <form @submit.prevent="submitForm" class="bg-white rounded-xl shadow p-6 space-y-6">

        {{-- اسم المشروع --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">اسم المشروع</label>
            <input type="text" x-model="form.title" class="border rounded w-full px-3 py-2" required>
        </div>

        {{-- اختيار المشرف --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">المشرف</label>
            <select x-model="form.supervisor_id" class="border rounded w-full px-3 py-2" required>
                <option value="">اختر المشرف</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- اختيار الطلاب --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">الطلاب المشاركون</label>
            <template x-for="student in eligibleStudents" :key="student.id">
                <div class="flex items-center mb-1">
                    <input type="checkbox" :value="student.id" x-model="form.student_ids" class="form-checkbox">
                    <span class="ml-2" x-text="student.full_name"></span>
                </div>
            </template>
            <p class="text-sm text-gray-500 mt-1">يمكنك اختيار أكثر من طالب</p>
        </div>

        {{-- زر الحفظ --}}
        <div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                حفظ المشروع
            </button>
        </div>

    </form>
</div>

<script>
function csrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

document.addEventListener('alpine:init', () => {
    Alpine.data('graduationProjectForm', () => ({
        form: {
            title: '',
            supervisor_id: '',
            student_ids: [],
        },
        eligibleStudents: @json($eligibleStudents),

        submitForm() {
            alert(JSON.stringify(this.form));
            fetch("{{ route('graduation-projects.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ route('graduation-projects.index') }}";
                } else {
                    alert(data.message || 'حدث خطأ أثناء الحفظ');
                }
            })
            .catch(err => {
                console.error(err);
                alert('فشل الاتصال بالسيرفر');
            });
        }
    }));
});
</script>
@endsection
