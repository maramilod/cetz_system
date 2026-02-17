@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">
        ترقية الأستاذ: {{ $teacher->full_name }}
    </h2>

    <form method="POST" action="{{ route('teachers.promotion.store', $teacher->id) }}">
        @csrf

         <div class="mb-4">
            <label class="block mb-1 font-semibold">الرتبة الأكاديمية</label>
            <select name="academic_rank_id" class="w-full border rounded px-3 py-2" required>
                <!-- الخيار الأول: الرتبة الحالية -->
                @if($teacher->teacherRanks->first())
                    <option value="{{ $teacher->teacherRanks->first()->academicRank->id }}">
                        {{ $teacher->teacherRanks->first()->academicRank->name }} (الحالية)
                    </option>
                @else
                    <option disabled selected>اختر رتبة</option>
                @endif

                <!-- باقي الرتب -->
                @foreach($academicRanks as $rank)
                    @if($teacher->teacherRanks->first()?->academicRank->id != $rank->id)
                        <option value="{{ $rank->id }}">
                            {{ $rank->name }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <!-- الوضع الوظيفي -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold">الوضع الوظيفي</label>
            <select name="employment_status_id" class="w-full border rounded px-3 py-2" required>
                <!-- الخيار الأول: الوضع الحالي -->
                @if($teacher->teacherEmploymentStatuses->first())
                    <option value="{{ $teacher->teacherEmploymentStatuses->first()->employmentStatus->id }}">
                        {{ $teacher->teacherEmploymentStatuses->first()->employmentStatus->name }} (الحالي)
                    </option>
                @else
                    <option disabled selected>اختر وضع وظيفي</option>
                @endif

                <!-- باقي الوضعيات -->
                @foreach($employmentStatuses as $status)
                    @if($teacher->teacherEmploymentStatuses->first()?->employmentStatus->id != $status->id)
                        <option value="{{ $status->id }}">
                            {{ $status->name }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <!-- تاريخ الترقية -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold">تاريخ الترقية</label>
            <input type="date" name="from_date"
                   class="w-full border rounded px-3 py-2"
                   value="{{ now()->toDateString() }}"
                   required>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded">
                حفظ الترقية
            </button>

            <a href="{{ route('teachers.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                رجوع
            </a>
        </div>
    </form>
</div>
@endsection
