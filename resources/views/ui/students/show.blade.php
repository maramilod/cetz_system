@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">بيانات الطالب</h1>

    <table class="w-full text-sm">
        <tr>
            <th class="text-right p-2">الاسم:</th>
            <td class="p-2">{{ $student->full_name }}</td>

        </tr>
        <tr>
            <th class="text-right p-2">الجنسية:</th>
            <td class="p-2">{{ $student->nationality }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">الجنس:</th>
            <td class="p-2">{{ $student->gender }}</td>
        </tr>
      <tr>
    <th class="text-right p-2">القسم:</th>
    <td class="p-2">{{ $student->department->name ?? '' }}</td>
</tr>

        <tr>
            <th class="text-right p-2">سنة التسجيل:</th>
            <td class="p-2">{{ $student->enrollment_year }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">الفصل:</th>
            <td class="p-2">{{ $student->semester }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">الرقم:</th>
            <td class="p-2">{{ $student->student_number }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">رقم يدوي:</th>
            <td class="p-2">{{ $student->manual_number }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">الرقم الوطني:</th>
            <td class="p-2">{{ $student->national_id }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">جواز السفر:</th>
            <td class="p-2">{{ $student->passport }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">تاريخ الميلاد:</th>
            <td class="p-2">{{ $student->dob }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">المصرف:</th>
            <td class="p-2">{{ $student->bank }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">رقم الحساب:</th>
            <td class="p-2">{{ $student->account_number }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">اسم الأم:</th>
            <td class="p-2">{{ $student->mother_name }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">قيد الكتيب:</th>
            <td class="p-2">{{ $student->registry_book }}</td>
        </tr>
    </table>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-100 rounded">عودة</a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded">طباعة</button>
    </div>
</div>
@endsection
