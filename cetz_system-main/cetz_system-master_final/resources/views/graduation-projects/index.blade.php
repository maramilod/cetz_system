@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto p-6">

    {{-- عنوان الصفحة وأزرار الإضافة --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">مشاريع التخرج</h1>

        <a href="{{ route('graduation-projects.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + إنشاء مشروع جديد
        </a>
    </div>

    {{-- رسالة نجاح --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- نموذج البحث بالكود --}}
    <form method="GET" class="mb-4 flex gap-2 items-center">
        <input type="text" name="team_code" value="{{ request('team_code') }}"
               placeholder="ابحث بكود الفريق"
               class="border rounded px-3 py-2 w-60">

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            بحث
        </button>

        @if(request('team_code'))
            <a href="{{ route('graduation-projects.index') }}"
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                عرض الكل
            </a>
        @endif
    </form>

    {{-- جدول المشاريع --}}
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">اسم المشروع</th>
                    <th class="p-3 text-right">المشرف</th>
                    <th class="p-3 text-right">الطلاب</th>
                    <th class="p-3 text-right">الحالة</th>
                    <th class="p-3 text-right">كود الفريق</th>
                    <th class="p-3 text-right">إجراءات</th>
                </tr>
            </thead>

            <tbody>
            @forelse($projects as $project)
                <tr class="border-t">
                    <td class="p-3">{{ $loop->iteration }}</td>
                    <td class="p-3 font-medium">{{ $project->title }}</td>
                    <td class="p-3">
                        {{ $project->supervisorRelation->full_name
 ?? '—' }}
                    </td>
                    <td class="p-3">
                        <ul class="list-disc pr-5">
                            @foreach($project->students as $student)
                                <li>{{ $student->full_name }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-sm
                            @if($project->status === 'pending') bg-yellow-100 text-yellow-700
                            @elseif($project->status === 'approved') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-600
                            @endif
                        ">
                            {{ $project->status }}
                        </span>
                    </td>
                    <td class="p-3 font-mono">
                        {{ $project->team_code }}
                    </td>
                    <td class="p-3 flex flex-col gap-1">
                        @if($project->status === 'pending')
                            <a href="{{ route('graduation-projects.edit', $project->id) }}"
                               class="inline-flex items-center px-3 py-1
                                      bg-yellow-500 text-white rounded
                                      hover:bg-yellow-600 text-sm">
                                تعديل
                            </a>

                            <form method="POST"
                                  action="{{ route('graduation-projects.destroy', $project->id) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm">
                                    حذف
                                </button>
                            </form>

                            <a href="{{ route('graduation-projects.pass', $project->id) }}"
                               class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                نجح
                            </a>

                        @else
                            <span class="text-gray-400 text-sm">غير متاح</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-6 text-center text-gray-500">
                        لا توجد مشاريع بعد
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
