@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto p-6" 
     x-data="graduationProjectEditForm()">

    <h1 class="text-2xl font-bold mb-6">تعديل مشروع التخرج</h1>

    <form @submit.prevent="submitForm"
          class="bg-white rounded-xl shadow p-6 space-y-6">

        {{-- اسم المشروع --}}
        <div>
            <label class="block text-sm font-medium">اسم المشروع</label>
            <input type="text" x-model="form.title"
                   class="border rounded w-full px-3 py-2" required>
        </div>

        {{-- المشرف --}}
        <div>
            <label class="block text-sm font-medium">المشرف</label>
            <select x-model="form.supervisor_id"
                    class="border rounded w-full px-3 py-2" required>
                <option value="">اختر المشرف</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                        {{ $teacher->id == $project->supervisor ? 'selected' : '' }}>
                        {{ $teacher->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- الطلاب --}}
        <div>
            <label class="block text-sm font-medium">الطلاب المشاركون</label>
            <template x-for="student in eligibleStudents" :key="student.id">
                <div class="flex items-center mb-1">
                    <input type="checkbox"
                           :value="student.id"
                           x-model="form.student_ids">
                    <span class="ml-2" x-text="student.full_name"></span>
                </div>
            </template>
        </div>

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded">
            حفظ التعديلات
        </button>

    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('graduationProjectEditForm', () => ({
        eligibleStudents: @json($eligibleStudents),
        form: {
            title: @json($project->title),
            supervisor_id: @json($project->supervisor),
            student_ids: @json($project->students->pluck('id')),
        },

        submitForm() {
            fetch("{{ route('graduation-projects.update', $project->id) }}", {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name=csrf-token]')
                        .getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                
                                            window.location.href = "{{ route('graduation-projects.index') }}";
                
            })
            .catch(err => {
                console.error(err);
                alert('فشل الاتصال بالسيرفر');
            });
        }
    }))
})
</script>

@endsection
