@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="resetManager()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">إعادة ضبط مبدئي</h1>
        <p class="text-gray-600">يعيد النظام إلى وضعه الافتراضي مع حذف البيانات. تأكد من وجود نسخة احتياطية.</p>

        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
            <li>سيتم حذف جميع سجلات الطلبة والمواد والتقارير.</li>
            <li>لن يتم حذف المستخدمين الإداريين الرئيسيين.</li>
            <li>يمكنك استرجاع البيانات لاحقاً من خلال صفحة الاسترجاع.</li>
        </ul>

        <div class="space-y-3">
            <label class="block text-sm text-gray-600">اكتب كلمة <strong>إعادة</strong> للتأكيد</label>
            <input type="text" x-model.trim="confirmation" class="border rounded px-3 py-2 w-full" placeholder="إعادة">
            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded" :disabled="!canReset" @click="executeReset">تنفيذ إعادة الضبط</button>
            <p class="text-sm" x-text="statusMessage"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('resetManager', () => ({
            confirmation: '',
            statusMessage: '',

            get canReset() {
                return this.confirmation === 'إعادة';
            },

            executeReset() {
                if (!this.canReset) {
                    return;
                }
                if (!confirm('هل أنت متأكد من تنفيذ إعادة الضبط؟ لا يمكن التراجع عن هذه العملية.')) {
                    return;
                }
                this.statusMessage = 'تمت إعادة ضبط النظام بنجاح. يمكنك البدء بإعداد البيانات من جديد.';
                this.confirmation = '';
            }
        }));
    });
</script>
@endsection
