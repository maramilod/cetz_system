@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="accreditationDashboard()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">لوحة الاعتماد المؤسسي</h1>
        <p class="text-gray-600">تابع حالة متطلبات الاعتماد المختلفة وخطط العمل المرتبطة بها.</p>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <template x-for="card in cards" :key="card.key">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500" x-text="card.label"></div>
                    <div class="text-2xl font-bold" :class="card.color" x-text="card.value"></div>
                    <div class="text-xs text-gray-400" x-text="card.note"></div>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border rounded-lg p-4 space-y-3">
                <h3 class="font-semibold">خطة العمل</h3>
                <template x-for="item in timeline" :key="item.name">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-2 rounded-full" :class="item.color"></div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium" x-text="item.name"></span>
                                <span class="text-gray-500" x-text="item.due"></span>
                            </div>
                            <p class="text-xs text-gray-500" x-text="item.note"></p>
                        </div>
                    </div>
                </template>
            </div>
            <div class="border rounded-lg p-4 space-y-3">
                <h3 class="font-semibold">مصفوفة المتطلبات</h3>
                <template x-for="requirement in requirements" :key="requirement.name">
                    <div class="border rounded p-3">
                        <div class="flex justify-between text-sm">
                            <span x-text="requirement.name"></span>
                            <span class="px-2 py-1 rounded" :class="statusBadge(requirement.status)" x-text="statusLabel(requirement.status)"></span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded mt-3">
                            <div class="h-full bg-blue-500 rounded" :style="'width:' + requirement.progress + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('accreditationDashboard', () => ({
            cards: [],
            requirements: [
                { name: 'معايير الجودة البكالوريوسة', status: 'in-progress', progress: 68 },
                { name: 'البنية التحتية والمختبرات', status: 'at-risk', progress: 45 },
                { name: 'التقويم والقياس', status: 'completed', progress: 100 }
            ],
            timeline: [
                { name: 'تسليم تقرير التقييم الذاتي', due: '2025-02-01', note: 'تجميع الملاحظات النهائية من الأقسام', color: 'bg-blue-500' },
                { name: 'زيارة لجنة الاعتماد', due: '2025-03-10', note: 'تجهيز العروض التقديمية والملفات الداعمة', color: 'bg-amber-500' },
                { name: 'استلام قرار الاعتماد', due: '2025-05-01', note: 'متابعة تنفيذ التوصيات إن وجدت', color: 'bg-green-500' }
            ],

            init() {
                const completed = this.requirements.filter(item => item.status === 'completed').length;
                const total = this.requirements.length;
                const avgProgress = Math.round(this.requirements.reduce((sum, item) => sum + item.progress, 0) / total);
                this.cards = [
                    { key: 'requirements', label: 'عدد المتطلبات', value: total, color: '', note: 'ضمن دورة الاعتماد الحالية' },
                    { key: 'completed', label: 'المكتمل', value: completed, color: 'text-green-600', note: 'متطلبات مكتملة بالكامل' },
                    { key: 'progress', label: 'متوسط التقدم', value: avgProgress + '%', color: 'text-blue-600', note: 'نسبة إنجاز عامة' },
                    { key: 'next', label: 'أقرب موعد', value: this.timeline[0].due, color: 'text-amber-600', note: this.timeline[0].name }
                ];
            },

            statusLabel(status) {
                if (status === 'completed') return 'مكتمل';
                if (status === 'in-progress') return 'قيد التنفيذ';
                return 'بحاجة لمتابعة';
            },

            statusBadge(status) {
                if (status === 'completed') return 'bg-green-100 text-green-700';
                if (status === 'in-progress') return 'bg-blue-100 text-blue-700';
                return 'bg-red-100 text-red-700';
            }
        }));
    });
</script>
@endsection
