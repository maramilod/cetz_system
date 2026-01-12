@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-md mx-auto space-y-6" x-data="instituteNumberForm()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">تحديث بيانات المؤسسة</h1>
        <p class="text-gray-600">استخدم هذا النموذج لحفظ بيانات المعهد أو الكلية ورقم الجهة الرسمي.</p>

        <form @submit.prevent="save" class="space-y-3">
            <!-- نوع المؤسسة -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">نوع المؤسسة</label>
                <select x-model="type" class="border rounded px-3 py-2 w-full" required>
                    <option value="" disabled>اختر نوع المؤسسة</option>
                    <option value="معهد">معهد</option>
                    <option value="كلية">كلية</option>
                </select>
            </div>

            <!-- رقم المعهد -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم المعهد</label>
                <input type="text" x-model="number" class="border rounded px-3 py-2 w-full" placeholder="مثال: 12345" required>
            </div>

            <!-- رمز الجهة -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">رمز الجهة</label>
                <input type="text" x-model="code" class="border rounded px-3 py-2 w-full" placeholder="مثال: EDU-01" required>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
        </form>

        <template x-if="message">
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded" x-text="message"></div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('instituteNumberForm', () => ({
        number: '{{ $institute->official_number ?? '' }}',
        code: '{{ $institute->authority_code ?? '' }}',
        type: '{{ $institute->type ?? '' }}',
        message: '',

        save() {
            fetch("{{ route('data.save-institute-number') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    number: this.number,
                    code: this.code,
                    type: this.type
                })
            })
            .then(res => res.json())
            .then(data => {
                this.message = data.message;
                setTimeout(() => this.message = '', 2500);
            })
            .catch(err => {
                console.error(err);
                this.message = 'حدث خطأ أثناء الحفظ';
            });
        }
    }));
});
</script>
@endsection
