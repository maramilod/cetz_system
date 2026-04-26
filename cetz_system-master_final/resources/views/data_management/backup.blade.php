@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="backupManager()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">حفظ قاعدة البيانات</h1>
        <p class="text-gray-600">اختر نوع النسخة الاحتياطية ثم اضغط على زر البدء. سيتم حفظ الملف محلياً بعد اكتمال العملية.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">نوع النسخة</label>
                <select x-model="backupType" class="border rounded px-3 py-2 w-full">
                    <option value="full">نسخة كاملة (بيانات + ملفات)</option>
                    <option value="data">بيانات فقط</option>
                    <option value="files">ملفات مرفقة فقط</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الجدولة</label>
                <select x-model="schedule" class="border rounded px-3 py-2 w-full">
                    <option value="manual">يدوي</option>
                    <option value="daily">يومي</option>
                    <option value="weekly">أسبوعي</option>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded" @click="startBackup" :disabled="isRunning">بدء النسخ الاحتياطي</button>
            <div class="h-2 bg-gray-200 rounded overflow-hidden" x-show="isRunning || progress === 100">
                <div class="h-full bg-green-500 transition-all duration-300" :style="'width:' + progress + '%'"></div>
            </div>
            <p class="text-sm" x-text="statusMessage"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('backupManager', () => ({
            backupType: 'full',
            schedule: 'manual',
            isRunning: false,
            progress: 0,
            statusMessage: '',
            timerId: null,

            startBackup() {
                if (this.isRunning) {
                    return;
                }
                this.isRunning = true;
                this.progress = 0;
                this.statusMessage = 'جاري إنشاء النسخة الاحتياطية...';
                this.timerId = setInterval(() => {
                    if (this.progress >= 100) {
                        clearInterval(this.timerId);
                        this.timerId = null;
                        this.isRunning = false;
                        this.statusMessage = 'اكتملت العملية. تم تنزيل الملف على جهازك.';
                        this.downloadFile();
                    } else {
                        this.progress += 15;
                    }
                }, 600);
            },

            downloadFile() {
                const fileName = 'backup-' + this.backupType + '-' + new Date().toISOString().slice(0, 10) + '.zip';
                const blob = new Blob(['Backup placeholder'], { type: 'application/zip' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                link.click();
                URL.revokeObjectURL(link.href);
            }
        }));
    });
</script>
@endsection
