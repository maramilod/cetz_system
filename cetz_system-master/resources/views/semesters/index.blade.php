@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="text-2xl font-bold mb-4">إدارة الفصول الدراسية</h1>

    @if($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <!-- إضافة حزمة -->
    <form action="{{ route('semesters.store') }}" method="POST" class="bg-white p-4 rounded shadow space-y-3">
        @csrf
        <div>
            <label class="block mb-1 text-sm">نوع البرنامج</label>
            <select name="degree_type" class="border rounded px-3 py-2 w-full" required>
                <option value="بكالوريوس">بكالوريوس</option>
                <option value="دبلوم">دبلوم</option>
            </select>
        </div>
        <div>
            <label class="block mb-1 text-sm">نوع الفصل</label>
            <select name="term_type" class="border rounded px-3 py-2 w-full" required>
                <option value="">اختر الفصل</option>
                <option value="خريفي">خريفي</option>
                <option value="ربيعي">ربيعي</option>
            </select>
        </div>

        <div>
            <label class="block mb-1 text-sm">تاريخ البداية</label>
            <input type="date" name="start_at" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block mb-1 text-sm">تاريخ النهاية</label>
            <input type="date" name="end_at" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">إضافة الحزمة</button>
        </div>
    </form>
    <div class="grid grid-cols-2 gap-4">



        <!-- قائمة الحزم المفعلة -->
        <div class="bg-white p-4 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-2">الحزم المفعلة حاليًا</h2>

            @if($activePackages->isNotEmpty())
            @foreach($activePackages as $activePackage)
            <div class="mb-4 p-3 rounded bg-green-50 border border-green-300">
                <p class="text-green-800 font-medium">
                    {{ $activePackage->degree_type }} – {{ $activePackage->term_type }}
                    ({{ $activePackage->start_date }} → {{ $activePackage->end_date }})
                </p>

                <!-- نموذج تعديل -->
                <form action="{{ route('semesters.updatePackage') }}" method="POST" class="inline mr-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="degree_type" value="{{ $activePackage->degree_type }}">
                    <input type="hidden" name="start_date" value="{{ $activePackage->start_date }}">
                    <input type="hidden" name="end_date" value="{{ $activePackage->end_date }}">

                    <input type="date" name="new_start_date" value="{{ $activePackage->start_date }}" required>
                    <input type="date" name="new_end_date" value="{{ $activePackage->end_date }}" required>
                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded">تعديل</button>
                </form>

                <!-- نموذج حذف -->
                <form action="{{ route('semesters.destroyPackage') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="degree_type" value="{{ $activePackage->degree_type }}">
                    <input type="hidden" name="start_date" value="{{ $activePackage->start_date }}">
                    <input type="hidden" name="end_date" value="{{ $activePackage->end_date }}">
                    <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded"
                        onclick="return confirm('هل تريد حذف الحزمة المفعلة؟')">
                        حذف
                    </button>
                </form>
            </div>
            @endforeach
            @else
            <p class="text-gray-600">لا توجد حزم مفعلة حاليًا</p>
            @endif
        </div>

        <div class="bg-white p-4 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">تفعيل حزمة جديدة</h2>

            <form action="{{ route('semesters.activate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <select name="package" required class="border rounded px-3 py-2 w-full">
                        <option value="">اختر الحزمة</option>
                        @foreach ($package as $p)
                        <option value="{{ $p->degree_type }}|{{ $p->term_type }}|{{ $p->start_date }}|{{ $p->end_date }}">
                            {{ $p->degree_type }} – {{ $p->term_type }} ({{ $p->start_date }} → {{ $p->end_date }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    تفعيل الحزمة
                </button>
            </form>
        </div>

<div class="bg-red-50 border border-red-300 p-4 rounded shadow mt-6">
    <h2 class="text-lg font-semibold text-red-700 mb-2">إجراءات إدارية حساسة</h2>

    <form action="{{ route('students.freezeAll') }}" method="POST"
onsubmit="return confirm('⚠️ هل أنت متأكدة؟ لا يمكن التراجع عن هذا الإجراء بسهولة. يُستخدم هذا الخيار عند انتهاء جميع إجراءات الفصل الدراسي السابق وبدء فصل دراسي جديد كليًا.');"

        @csrf
        <button type="submit"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            🚫 جهاز تجديد القيد        </button>
    </form>
</div>

    </div>
    @endsection