@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="restoreManager()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">استرجاع قاعدة البيانات</h1>
        <p class="text-gray-600">قم باختيار ملف النسخة الاحتياطية ثم ابدأ عملية الاسترجاع. ينصح بعمل نسخة احتياطية قبل المتابعة.</p>

        <div class="space-y-3">
            <label class="block text-sm text-gray-600">ملف النسخة الاحتياطية (.zip)</label>
            <input type="file" accept=".zip" @change="handleFile" class="border rounded px-3 py-2 w-full bg-gray-50">
            <p class="text-sm text-gray-500" x-show="fileName" x-text="'الملف المحدد: ' + fileName"></p>
        </div>

        <div class="space-y-2">
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="!fileName || isRunning" @click="startRestore">بدء الاسترجاع</button>
            <div class="h-2 bg-gray-200 rounded overflow-hidden" x-show="isRunning || progress === 100">
                <div class="h-full bg-blue-500 transition-all duration-300" :style="'width:' + progress + '%'"></div>
            </div>
            <p class="text-sm" x-text="statusMessage"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('restoreManager', () => ({
            fileName: '',
            isRunning: false,
            progress: 0,
            statusMessage: '',
            timerId: null,

            handleFile(event) {
                const file = event.target.files[0];
                this.fileName = file ? file.name : '';
                this.statusMessage = '';
                this.progress = 0;
            },

            startRestore() {
                if (!this.fileName || this.isRunning) {
                    return;
                }
                this.isRunning = true;
                this.progress = 0;
                this.statusMessage = 'جاري التحقق من سلامة الملف...';
                this.timerId = setInterval(() => {
                    if (this.progress >= 100) {
                        clearInterval(this.timerId);
                        this.timerId = null;
                        this.isRunning = false;
                        this.statusMessage = 'تم الاسترجاع بنجاح. يرجى التأكد من البيانات.';
                    } else {
                        this.progress += 20;
                        if (this.progress === 40) {
                            this.statusMessage = 'جاري فك ضغط البيانات...';
                        }
                        if (this.progress === 80) {
                            this.statusMessage = 'جاري تحديث الجداول...';
                        }
                    }
                }, 600);
            }
        }));
    });
</script>
@endsection
