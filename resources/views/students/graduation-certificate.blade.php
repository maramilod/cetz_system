@extends('layouts.app')

@section('content')
<div class="certificate-page max-w-6xl mx-auto p-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إفادة التخرج</h1>
            <p class="text-sm text-gray-600 mt-1">المعاينة فقط. للطباعة سيفتح نموذج مستقل بدون أي عناصر جانبية.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('students.graduated') }}" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                رجوع للخريجين
            </a>
            <a href="{{ route('students.graduated.certificate', $student) }}?print=1" target="_blank" class="px-4 py-2 rounded bg-blue-700 text-white hover:bg-blue-800">
                طباعة الإفادة
            </a>
        </div>
    </div>

    <div class="overflow-auto rounded-lg bg-gray-100 p-4">
        @include('students.partials.graduation-certificate-content', ['certificateData' => $certificateData])
    </div>
</div>
@endsection
