@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="instituteInfoForm()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">بيانات المعهد / الكلية</h1>
        <p class="text-gray-600">حدّث معلومات الاتصال الرسمية التي تظهر في الوثائق والتقارير.</p>

        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">اسم المؤسسة</label>
                <input type="text" x-model="form.name" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">العنوان</label>
                <input type="text" x-model="form.address" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم الهاتف</label>
                <input type="text" x-model="form.phone" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">البريد الإلكتروني</label>
                <input type="email" x-model="form.email" class="border rounded px-3 py-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الموقع الإلكتروني</label>
                <input type="url" x-model="form.website" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">وصف قصير</label>
                <textarea rows="3" x-model="form.description" class="border rounded px-3 py-2 w-full"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="resetForm">إعادة الضبط</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
            </div>
        </form>

        <template x-if="message">
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded" x-text="message"></div>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('instituteInfoForm', () => ({
            defaultValues: {
                name: 'كلية التقنية الهندسية',
                address: 'زوارة - ليبيا',
                phone: '+218 21 1234567',
                email: 'info@example.edu',
                website: 'https://example.edu',
                description: 'مؤسسة تعليمية تمنح درجة البكالوريوس في العلوم الهندسية والحاسوبية.'
            },
            form: {},
            message: '',

            init() {
                this.form = { ...this.defaultValues };
            },

            resetForm() {
                this.form = { ...this.defaultValues };
                this.message = '';
            },

            save() {
                this.message = 'تم تحديث بيانات المؤسسة بنجاح.';
                setTimeout(() => {
                    this.message = '';
                }, 2500);
            }
        }));
    });
</script>
@endsection
