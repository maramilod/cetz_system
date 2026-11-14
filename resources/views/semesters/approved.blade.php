@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <h1 class="text-2xl font-bold mb-2">إدارة اعتماد الفصول الدراسية</h1>
        <p class="text-gray-600 mb-4">يمكنك الاعتماد أو إلغاء الاعتماد لكل فصل بسهولة باستخدام الزر الموجود لكل فصل.</p>

        <!-- جدول الفصول -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-right">نوع الدرجة</th>
                        <th class="px-4 py-2 text-right">نوع الفصل</th>
                        <th class="px-4 py-2 text-right">تاريخ البداية</th>
                        <th class="px-4 py-2 text-right">تاريخ النهاية</th>
                        <th class="px-4 py-2 text-right">عدد السيمسترات</th>
                        <th class="px-4 py-2 text-right">فعال</th>
                        <th class="px-4 py-2 text-right">الاعتماد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semesters as $semester)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2 text-right">{{ $semester->degree_type }}</td>
                            <td class="border px-4 py-2 text-right">{{ $semester->term_type }}</td>
                            <td class="border px-4 py-2 text-right">{{ $semester->start_date }}</td>
                            <td class="border px-4 py-2 text-right">{{ $semester->end_date }}</td>
                            <td class="border px-4 py-2 text-right">{{ $semester->semesters_count }}</td>
                            <td class="border px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded-full {{ $semester->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $semester->active ? 'نعم' : 'لا' }}
                                </span>
                            </td>
                            <td class="border px-4 py-2 text-center">
                                <form action="{{ route('semesters.toggleApprovalGroup') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="degree_type" value="{{ $semester->degree_type }}">
                                    <input type="hidden" name="term_type" value="{{ $semester->term_type }}">
                                    <input type="hidden" name="start_date" value="{{ $semester->start_date }}">
                                    <input type="hidden" name="end_date" value="{{ $semester->end_date }}">
                                    <button type="submit" 
                                        class="btn btn-sm {{ $semester->approved ? 'btn-danger' : 'btn-success' }}">
                                        {{ $semester->approved ? 'إلغاء الاعتماد' : 'اعتماد الفصل' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
