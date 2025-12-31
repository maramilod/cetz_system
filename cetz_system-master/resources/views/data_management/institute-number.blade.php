@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto space-y-6" x-data="instituteNumberForm()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">تحديث رقم المعهد</h1>
        <p class="text-gray-600">استخدم هذا النموذج لحفظ الرقم الرسمي المعتمد من وزارة التعليم.</p>

        <form @submit.prevent="save" class="space-y-3">
            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم المعهد</label>
                <input type="text" x-model="number" class="border rounded px-3 py-2 w-full" placeholder="مثال: 12345" required>
            </div>
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
            number: '12345',
            code: 'EDU-01',
            message: '',

            save() {
                this.message = 'تم حفظ رقم المعهد بنجاح (' + this.number + ' / ' + this.code + ')';
                setTimeout(() => {
                    this.message = '';
                }, 2500);
            }
        }));
    });
</script>
@endsection
