@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="deprivedEntry()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- البحث عن الطالب -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6 space-y-4">
            <h2 class="text-xl font-semibold">اختيار الطالب</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">القسم</label>
                    <select x-model="filters.department" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل الأقسام</option>
                        <template x-for="dept in filters.departments" :key="dept"><option :value="dept" x-text="dept"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                    <select x-model="filters.semester" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل الفصول</option>
                        <template x-for="sem in filters.semesters" :key="sem"><option :value="sem" x-text="sem"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">المادة</label>
                    <select x-model="filters.subject" @change="applyFilters" class="border rounded px-3 py-2 w-full">
                        <option value="">كل المواد</option>
                        <template x-for="sub in filters.subjects" :key="sub"><option :value="sub" x-text="sub"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">بحث</label>
                    <input type="text" x-model.trim="filters.search" @input.debounce.300="applyFilters" placeholder="اسم/رقم قيد" class="border rounded px-3 py-2 w-full">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2">#</th>
                            <th class="border px-3 py-2 text-right">اسم الطالب</th>
                            <th class="border px-3 py-2 text-right">رقم القيد</th>
                            <th class="border px-3 py-2 text-right">القسم</th>
                            <th class="border px-3 py-2 text-right">الفصل</th>
                            <th class="border px-3 py-2 text-right">المادة</th>
                            <th class="border px-3 py-2 text-right">تحديد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="!results.length">
                            <tr><td colspan="7" class="border px-3 py-3 text-center text-gray-500">لا توجد نتائج مطابقة.</td></tr>
                        </template>
                        <template x-for="(row, i) in results" :key="row.number + '-' + row.subject">
                            <tr class="hover:bg-gray-50">
                                <td class="border px-3 py-2" x-text="i+1"></td>
                                <td class="border px-3 py-2" x-text="row.name"></td>
                                <td class="border px-3 py-2" x-text="row.number"></td>
                                <td class="border px-3 py-2" x-text="row.department"></td>
                                <td class="border px-3 py-2" x-text="row.semester"></td>
                                <td class="border px-3 py-2" x-text="row.subject"></td>
                                <td class="border px-3 py-2 text-center">
                                    <button class="px-2 py-1 bg-blue-600 text-white rounded" @click="selectRow(row)">اختيار</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- إدخال سبب الحرمان -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h2 class="text-xl font-semibold">إدخال/تعديل الحرمان</h2>
            <div class="space-y-2 text-sm">
                <div>الطالب: <span class="font-semibold" x-text="form.name || '—'"></span></div>
                <div>رقم القيد: <span class="font-semibold" x-text="form.number || '—'"></span></div>
                <div>القسم: <span class="font-semibold" x-text="form.department || '—'"></span></div>
                <div>الفصل: <span class="font-semibold" x-text="form.semester || '—'"></span></div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">المادة</label>
                    <select x-model="form.subject" class="border rounded px-3 py-2 w-full">
                        <template x-for="sub in availableSubjects" :key="sub"><option :value="sub" x-text="sub"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">سبب الحرمان</label>
                    <select x-model="form.reason_type" class="border rounded px-3 py-2 w-full">
                        <option value="">— اختر السبب —</option>
                        <option value="غياب">غياب</option>
                        <option value="سلوك">سلوك</option>
                        <option value="رسوب سابق">رسوب سابق</option>
                        <option value="أخرى">أخرى</option>
                    </select>
                </div>
                <div x-show="form.reason_type === 'غياب'">
                    <label class="block text-sm text-gray-600 mb-1">نسبة الغياب %</label>
                    <input type="number" min="0" max="100" x-model.number="form.absence_percent" class="border rounded px-3 py-2 w-full" placeholder="مثال: 30">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">تاريخ الإدراج</label>
                    <input type="date" x-model="form.date" class="border rounded px-3 py-2 w-full">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" class="px-4 py-2 bg-green-600 text-white rounded" @click="save()" :disabled="!canSave">حفظ</button>
                    <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="resetForm">إلغاء</button>
                </div>
                <template x-if="message">
                    <div class="bg-green-100 text-green-700 px-3 py-2 rounded" x-text="message"></div>
                </template>
            </div>

            <div class="mt-4">
                <h3 class="font-semibold mb-2">المضاف محلياً (لن يظهر إلا بعد فتح قائمة المحرومين)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-1">الطالب</th>
                                <th class="border px-2 py-1">رقم القيد</th>
                                <th class="border px-2 py-1">المادة</th>
                                <th class="border px-2 py-1">الفصل</th>
                                <th class="border px-2 py-1">السبب</th>
                                <th class="border px-2 py-1">تاريخ</th>
                                <th class="border px-2 py-1">حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!localItems.length"><tr><td colspan="7" class="border px-3 py-2 text-center text-gray-500">لا يوجد عناصر محفوظة.</td></tr></template>
                            <template x-for="(r, idx) in localItems" :key="idx">
                                <tr>
                                    <td class="border px-2 py-1" x-text="r.student"></td>
                                    <td class="border px-2 py-1" x-text="r.number"></td>
                                    <td class="border px-2 py-1" x-text="r.subject"></td>
                                    <td class="border px-2 py-1" x-text="r.semester"></td>
                                    <td class="border px-2 py-1" x-text="r.reason_type === 'غياب' ? ('غياب — ' + (r.absence_percent || 0) + '%') : r.reason_type"></td>
                                    <td class="border px-2 py-1" x-text="r.date"></td>
                                    <td class="border px-2 py-1 text-center"><button class="px-2 py-1 bg-red-600 text-white rounded" @click="removeLocal(idx)">حذف</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('deprivedEntry', () => ({
            students: [
                { number: '2025-001', name: 'آمنة علي',   department: 'هندسة كهربائية', semester: 'ربيع 2025', subjects: ['رياضيات 1','فيزياء 1','دوائر'] },
                { number: '2025-010', name: 'محمد عمر',   department: 'علوم حاسوب',     semester: 'ربيع 2025', subjects: ['برمجة 1','قواعد بيانات','هياكل'] },
                { number: '2024-075', name: 'سارة محمود', department: 'هندسة ميكانيك',  semester: 'خريف 2024', subjects: ['ميكانيكا','رسم هندسي'] }
            ],
            rows: [],
            results: [],
            availableSubjects: [],
            localItems: [],
            filters: { department: '', semester: '', subject: '', search: '', departments: [], semesters: [], subjects: [] },
            form: { number: '', name: '', department: '', semester: '', subject: '', reason_type: '', absence_percent: null, date: new Date().toISOString().slice(0,10) },
            message: '',

            get canSave() {
                return this.form.number && this.form.subject && this.form.reason_type;
            },

            init() {
                // تفكيك الطلاب إلى صفوف لكل مادة لتسهيل البحث
                this.rows = this.students.flatMap(s => (s.subjects || []).map(sub => ({ ...s, subject: sub })));
                this.filters.departments = Array.from(new Set(this.rows.map(r => r.department)));
                this.filters.semesters = Array.from(new Set(this.rows.map(r => r.semester)));
                this.filters.subjects = Array.from(new Set(this.rows.map(r => r.subject)));
                this.applyFilters();
                this.loadLocal();
            },

            applyFilters() {
                const term = (this.filters.search || '').toLowerCase();
                this.results = this.rows.filter(r =>
                    (!this.filters.department || r.department === this.filters.department) &&
                    (!this.filters.semester || r.semester === this.filters.semester) &&
                    (!this.filters.subject || r.subject === this.filters.subject) &&
                    (!term || (r.name + ' ' + r.number).toLowerCase().includes(term))
                );
            },

            selectRow(row) {
                this.form.number = row.number;
                this.form.name = row.name;
                this.form.department = row.department;
                this.form.semester = row.semester;
                this.availableSubjects = this.students.find(s => s.number === row.number)?.subjects || [];
                this.form.subject = row.subject;
            },

            resetForm() {
                this.form = { number: '', name: '', department: '', semester: '', subject: '', reason_type: '', absence_percent: null, date: new Date().toISOString().slice(0,10) };
            },

            loadLocal() {
                try { this.localItems = JSON.parse(localStorage.getItem('deprived_extra') || '[]'); } catch { this.localItems = []; }
            },

            saveLocal(items) { localStorage.setItem('deprived_extra', JSON.stringify(items)); },

            save() {
                if (!this.canSave) return;
                const payload = {
                    student: this.form.name,
                    number: this.form.number,
                    department: this.form.department,
                    subject: this.form.subject,
                    semester: this.form.semester,
                    reason_type: this.form.reason_type,
                    absence_percent: this.form.reason_type === 'غياب' ? (Number(this.form.absence_percent)||0) : null,
                    date: this.form.date
                };
                const items = Array.isArray(this.localItems) ? [...this.localItems] : [];
                items.push(payload);
                this.saveLocal(items);
                this.loadLocal();
                this.message = 'تم حفظ الحرمان محلياً. ستراه ضمن قائمة المحرومين.';
                setTimeout(()=> this.message = '', 2500);
                this.resetForm();
            },

            removeLocal(idx) {
                const items = [...this.localItems];
                items.splice(idx, 1);
                this.saveLocal(items);
                this.loadLocal();
            }
        }));
    });
</script>
@endsection
