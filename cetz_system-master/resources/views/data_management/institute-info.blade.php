@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-3xl mx-auto space-y-6" x-data="instituteInfoForm()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">بيانات المعهد / الكلية</h1>
        <p class="text-gray-600">حدّث معلومات الاتصال الرسمية التي تظهر في الوثائق والتقارير.</p>

        <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4" enctype="multipart/form-data">
            <!-- اسم المؤسسة -->
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">اسم المؤسسة</label>
                <input type="text" x-model="form.name" class="border rounded px-3 py-2 w-full" required>
            </div>

            <!-- العنوان -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">العنوان</label>
                <input type="text" x-model="form.address" class="border rounded px-3 py-2 w-full">
            </div>

            <!-- رقم الهاتف -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم الهاتف</label>
                <input type="text" x-model="form.phone" class="border rounded px-3 py-2 w-full">
            </div>

            <!-- البريد الإلكتروني -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">البريد الإلكتروني</label>
                <input type="email" x-model="form.email" class="border rounded px-3 py-2 w-full">
            </div>

            <!-- الموقع الإلكتروني -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">الموقع الإلكتروني</label>
                <input type="url" x-model="form.website" class="border rounded px-3 py-2 w-full">
            </div>

            <!-- وصف قصير -->
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">وصف قصير</label>
                <textarea rows="3" x-model="form.description" class="border rounded px-3 py-2 w-full"></textarea>
            </div>

            <!-- رفع الشعار -->
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">شعار المؤسسة</label>
                <input type="file" @change="handleLogoUpload" class="border rounded px-3 py-2 w-full">
                <template x-if="form.logoUrl">
                    <img :src="form.logoUrl" class="mt-2 h-20 object-contain" alt="شعار المؤسسة">
                </template>
            </div>

            <!-- زر الحفظ -->
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
            </div>
        </form>

        <!-- رسالة نجاح -->
        <template x-if="message">
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded" x-text="message"></div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('instituteInfoForm', () => ({
        form: {
            name: '',
            address: '',
            phone: '',
            email: '',
            website: '',
            description: '',
            logo: null,
            logoUrl: ''
        },
        message: '',

        init() {
            // جلب البيانات من البوت
            const data = @json($institute ?? []);
            if(data) {
                this.form.name = data.name ?? '';
                this.form.address = data.address ?? '';
                this.form.phone = data.phone ?? '';
                this.form.email = data.email ?? '';
                this.form.website = data.website ?? '';
                this.form.description = data.description ?? '';
                if(data.logo) this.form.logoUrl = '/storage/' + data.logo;
            }
        },

        handleLogoUpload(event) {
            const file = event.target.files[0];
            if(!file) return;
            this.form.logo = file;
            this.form.logoUrl = URL.createObjectURL(file);
        },

        async save() {
            const formData = new FormData();
            for (let key in this.form) {
                if(this.form[key] !== null) formData.append(key, this.form[key]);
            }

            try {
                const res = await fetch("{{ route('data.save-institute-info') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                if(!res.ok) throw new Error('حدث خطأ أثناء الحفظ');

                const data = await res.json();
                this.message = data.message ?? 'تم تحديث بيانات المؤسسة بنجاح';
                if(data.institute?.logo) this.form.logoUrl = '/storage/' + data.institute.logo;
            } catch(err) {
                console.error(err);
                this.message = err.message;
            }

            setTimeout(() => this.message = '', 2500);
        }
    }));
});
</script>
@endsection
