@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{search:'',department:'',academicYear:'',term:'',match(row){const s=this.search.trim().toLowerCase();const okDept=!this.department||String(row.dept||'')===this.department;const okYear=!this.academicYear||String(row.year||'')===this.academicYear;const okTerm=!this.term||String(row.term||'')===this.term;const okSearch=!s||String(row.name||'').toLowerCase().includes(s);return okDept&&okYear&&okTerm&&okSearch;}}">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">الفصول الدراسية</h1>
        <a href="{{ route('classrooms.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">إضافة فصل</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-3 border-b flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">بحث</label>
                <input type="text" x-model.trim="search" placeholder="ابحث باسم الفصل" class="border rounded px-3 py-2 w-full">
            </div>
            <div class="min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select x-model="department" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    @php($deptOptions = collect($classrooms)->pluck('department.name')->filter()->unique())
                    @foreach($deptOptions as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">السنة الدراسية</label>
                <select x-model="academicYear" class="border rounded px-3 py-2 w-full">
                    <option value="">كل السنوات</option>
                    @php($yearOptions = collect($classrooms)->pluck('academic_year')->filter()->unique())
                    @foreach($yearOptions as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-sm text-gray-600 mb-1">الفصل</label>
                <select x-model="term" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    @php($termOptions = collect($classrooms)->pluck('term')->filter()->unique())
                    @foreach($termOptions as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                    @if(empty($termOptions) || $termOptions->isEmpty())
                        <option value="خريف">خريف</option>
                        <option value="صيف">صيف</option>
                    @endif
                </select>
            </div>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">رقم الفصل</th>
                    <th class="p-2">القسم</th>
                    <th class="p-2">السنة الدراسية</th>
                    <th class="p-2">الفصل</th>
                    <th class="p-2">المستخدم</th>
                    <th class="p-2 text-right">الاجرائات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classrooms as $classroom)
                <tr class="border-b hover:bg-gray-50" x-show="match(@js(['dept'=> $classroom->department->name ?? '', 'name'=> $classroom->name, 'year'=> $classroom->academic_year ?? '', 'term'=> $classroom->term ?? '']))">
                    <td class="p-2">{{ $loop->iteration }}</td>
                    <td class="p-2">{{ $classroom->name }}</td>
                    <td class="p-2">{{ $classroom->department->name ?? '-' }}</td>
                    <td class="p-2">{{ $classroom->academic_year ?? '-' }}</td>
                    <td class="p-2">{{ $classroom->term ?? '-' }}</td>
                    <td class="p-2 text-right space-x-2 rtl:space-x-reverse">
                        <a href="{{ route('classrooms.edit', $classroom) }}" class="text-green-600">تعديل</a>
                        <form action="{{ route('classrooms.destroy', $classroom) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
