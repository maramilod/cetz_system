@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto space-y-6" x-data="passwordManager()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">تغيير كلمة المرور</h1>
        <p class="text-gray-600">قم بتحديث كلمة المرور مع مراعاة استخدام أرقام وحروف ورموز.</p>

        <form @submit.prevent="changePassword" class="space-y-3">
            <div>
                <label class="block text-sm text-gray-600 mb-1">كلمة المرور الحالية</label>
                <input type="password" x-model="current" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">كلمة المرور الجديدة</label>
                <input type="password" x-model="next" class="border rounded px-3 py-2 w-full" required>
                <p class="text-xs text-gray-500 mt-1">يجب أن تكون 8 أحرف على الأقل وتتضمن رمزاً.</p>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">تأكيد كلمة المرور الجديدة</label>
                <input type="password" x-model="confirm" class="border rounded px-3 py-2 w-full" required>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ التغييرات</button>
        </form>

        <template x-if="message">
            <div :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-4 py-2 rounded" x-text="message"></div>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('passwordManager', () => ({
            current: '',
            next: '',
            confirm: '',
            message: '',
            messageType: 'success',

            changePassword() {
                if (this.next.length < 8 || !/[!@#$%^&*]/.test(this.next)) {
                    this.message = 'كلمة المرور الجديدة ضعيفة. الرجاء إضافة رمز واستخدام 8 أحرف على الأقل.';
                    this.messageType = 'error';
                    return;
                }
                if (this.next !== this.confirm) {
                    this.message = 'تأكيد كلمة المرور لا يطابق المدخل الجديد.';
                    this.messageType = 'error';
                    return;
                }
                if (this.current === this.next) {
                    this.message = 'يجب أن تختلف كلمة المرور الجديدة عن الحالية.';
                    this.messageType = 'error';
                    return;
                }

                this.message = 'تم تحديث كلمة المرور بنجاح.';
                this.messageType = 'success';
                this.current = '';
                this.next = '';
                this.confirm = '';
            }
        }));
    });
</script>
@endsection
