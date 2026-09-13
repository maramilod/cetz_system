@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">النتائج النهائية</h1>

        <form method="GET" action="{{ route('final-results.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">الفصل الدراسي</label>
                <select name="term_type" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الفصول</option>
                    @foreach($termTypes as $termType)
                        <option value="{{ $termType }}" @selected($filters['term_type'] === (string) $termType)>{{ $termType }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">السنة</label>
                <select name="year" class="border rounded px-3 py-2 w-full">
                    <option value="">كل السنوات</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected($filters['year'] === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">القسم</label>
                <select id="departmentFilter" name="department_id" class="border rounded px-3 py-2 w-full">
                    <option value="">كل الأقسام</option>
                    @foreach($departments as $department)
                        <option
                            value="{{ $department->id }}"
                            data-is-general="{{ (int) $department->is_general }}"
                            @selected($filters['department_id'] === (string) $department->id)
                        >
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">رقم الفصل</label>
                <select id="semesterNumberFilter" name="semester_number" class="border rounded px-3 py-2 w-full">
                    <option value="">الكل</option>
                    @foreach($semesterNumbers as $semesterNumber)
                        <option value="{{ $semesterNumber }}" @selected($filters['semester_number'] === (string) $semesterNumber)>{{ $semesterNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-full">عرض</button>
                @if($hasRequiredFilters)
                    <a href="{{ route('final-results.print', request()->query()) }}" target="_blank" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 w-full text-center">طباعة</a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @if(!$hasRequiredFilters)
            <div class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                اختر `الفصل الدراسي` و`السنة` و`القسم` و`رقم الفصل` ثم اضغط عرض.
            </div>
        @endif
        <div class="mb-4 text-sm text-gray-700">
            <span class="font-semibold">الفصل الدراسي:</span> {{ $selectedTermType }}
            <span class="mx-2">|</span>
            <span class="font-semibold">السنة:</span> {{ $selectedYear }}
            <span class="mx-2">|</span>
            <span class="font-semibold">القسم:</span> {{ $selectedDepartmentName }}
            <span class="mx-2">|</span>
            <span class="font-semibold">رقم الفصل:</span> {{ $selectedSemesterNumber }}
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2">ر.ت</th>
                        <th class="border border-gray-300 px-3 py-2">الاسم</th>
                        <th class="border border-gray-300 px-3 py-2">رقم القيد</th>
                        <th class="border border-gray-300 px-3 py-2">النسبة</th>
                        <th class="border border-gray-300 px-3 py-2">التقدير</th>
                        @foreach($courses as $course)
                            <th class="border border-gray-300 px-3 py-2">{{ $course['name'] }}</th>
                        @endforeach
                        <th class="border border-gray-300 px-3 py-2">المواد المرحلة</th>
                        <th class="border border-gray-300 px-3 py-2">درجة المواد المرحلة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        <tr class="odd:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-2">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $row['student_name'] }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $row['student_number'] }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $row['average'] }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $row['classification'] }}</td>
                            @foreach($row['course_grades'] as $courseGrade)
                                <td class="border border-gray-300 px-3 py-2">{{ $courseGrade['grade'] }}</td>
                            @endforeach
                            <td class="border border-gray-300 px-3 py-2 text-right">
                                @forelse($row['carry_courses'] as $carry)
                                    <div>{{ $carry['course_name'] }}</div>
                                @empty
                                    -
                                @endforelse
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                @forelse($row['carry_courses'] as $carry)
                                    <div>{{ $carry['grade'] }}</div>
                                @empty
                                    -
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($courses) }}" class="border border-gray-300 px-3 py-6 text-gray-500">
                                {{ $hasRequiredFilters ? 'لا توجد نتائج مطابقة للفلاتر المختارة.' : 'لا توجد بيانات للعرض قبل اختيار الفلاتر المطلوبة.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        const departmentSelect = document.getElementById('departmentFilter');
        const semesterSelect = document.getElementById('semesterNumberFilter');
        if (!departmentSelect || !semesterSelect) return;

        const selectedSemester = @json($filters['semester_number']);
        const allSemesters = @json($semesterNumbersAll->values());
        const generalSemesters = @json($semesterNumbersGeneral->values());
        const nonGeneralSemesters = @json($semesterNumbersNonGeneral->values());

        function renderSemesterOptions(values, keepSelected, includeAllOption) {
            semesterSelect.innerHTML = '';
            if (includeAllOption) {
                semesterSelect.innerHTML = '<option value="">الكل</option>';
            }
            values.forEach(function (value) {
                const option = document.createElement('option');
                option.value = String(value);
                option.textContent = String(value);
                if (keepSelected && String(keepSelected) === String(value)) {
                    option.selected = true;
                }
                semesterSelect.appendChild(option);
            });
        }

        function getSemesterListByDepartment() {
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            if (!selectedOption || selectedOption.value === '') {
                return { values: allSemesters, isGeneral: false };
            }
            const isGeneral = selectedOption.dataset.isGeneral === '1';
            return {
                values: isGeneral ? generalSemesters : nonGeneralSemesters,
                isGeneral: isGeneral
            };
        }

        const initialConfig = getSemesterListByDepartment();
        renderSemesterOptions(initialConfig.values, selectedSemester, !initialConfig.isGeneral);

        departmentSelect.addEventListener('change', function () {
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            const isGeneral = selectedOption && selectedOption.dataset.isGeneral === '1';
            const config = getSemesterListByDepartment();
            renderSemesterOptions(config.values, isGeneral ? '1' : '', !isGeneral);
            if (isGeneral) {
                semesterSelect.value = '1';
            }
        });
    })();
</script>
@endsection
