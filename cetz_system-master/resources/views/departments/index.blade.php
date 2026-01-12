@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{ search: '' }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">الأقسام</h1>
        <a href="{{ route('departments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">إضافة قسم</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <input type="text" x-model="search" placeholder="ابحث باسم القسم أو الشعبة"
               class="border rounded px-3 py-2 w-full md:w-1/2">
    </div>

    <div class="grid gap-6">
        @foreach($departments as $department)
            <div x-show="search === '' || '{{ $department['name'] }}'.toLowerCase().includes(search.toLowerCase())"
                 class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-lg font-semibold">{{ $department['name'] }}
                        <span class="text-sm text-gray-500">({{ $department['is_general'] ? 'عام' : 'تخصص' }})</span>
                                                                <span class="text-xs text-gray-400">({{ $department['updated_by_name'] ?? '-' }})</span>

                    </h2>
                    <div class="flex gap-2">
                        <!-- تفعيل / إخفاء القسم -->
                        <form action="{{ route('departments.toggle', $department['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="px-2 py-1 rounded {{ $department['is_active'] ? 'bg-yellow-300' : 'bg-green-300' }}">
                                {{ $department['is_active'] ? 'إخفاء' : 'تفعيل' }}
                            </button>
                        </form>

                        <!-- تعديل القسم -->
                        <a href="{{ route('departments.edit', $department['id']) }}"
                           class="px-2 py-1 bg-blue-200 rounded">تعديل</a>

                        <!-- حذف القسم -->
                        @if($department['students_count'] == 0)
                            <form action="{{ route('departments.destroy', $department['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-red-100 rounded">حذف</button>
                            </form>
                        @else
                            <button class="px-2 py-1 bg-gray-200 rounded cursor-not-allowed" disabled>لا يمكن الحذف</button>
                        @endif
                    </div>
                </div>

                <!-- الشعب للقسم التخصصي -->
                @if(!$department['is_general'])
                    <div class="ml-4 mt-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold">الشعب</h3>
                            <a href="{{ route('sections.create', $department['id']) }}"
                               class="px-2 py-1 bg-green-500 text-white rounded text-sm">إضافة شعبة</a>
                        </div>
                        <div class="grid gap-2">
                            @foreach($department['sections'] as $section)
                                <div class="border rounded p-2 flex justify-between items-center">
                                    <div>
                                        <span class="font-medium">{{ $section['name'] }}</span>
                                        <span class="text-sm text-gray-500">
                                            ({{ $section['is_active'] ? 'نشط' : 'مخفي' }})
                                        </span>
                                        <span class="text-xs text-gray-400">({{ $section['updated_by_name'] ?? '-' }})</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('sections.edit', $section['id']) }}"
                                           class="px-2 py-1 bg-blue-200 rounded text-sm">تعديل</a>

                                        <form action="{{ route('sections.toggle', $section['id']) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded {{ $section['is_active'] ? 'bg-yellow-300' : 'bg-green-300' }} text-sm">
                                                {{ $section['is_active'] ? 'إخفاء' : 'تفعيل' }}
                                            </button>
                                        </form>

                                        @if($section['students_count'] == 0)
                                            <form action="{{ route('sections.destroy', $section['id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 bg-red-100 rounded text-sm">حذف</button>
                                            </form>
                                        @else
                                            <button class="px-2 py-1 bg-gray-200 rounded text-sm cursor-not-allowed" disabled>لا يمكن الحذف</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
