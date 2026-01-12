@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">بيانات الطالب</h1>

    <!-- صورة الطالب -->
@if($student->photo)
<div class="mb-4 text-center">
    <img id="student-photo" src="{{ asset('storage/students/' . $student->photo) }}" 
         alt="صورة الطالب" 
         class="w-32 h-32 object-cover rounded-full mx-auto border">
</div>
@endif


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
            <td class="p-2">{{ $student->registration_year }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">الفصل:</th>
            <td class="p-2">{{ $student->academic_term }}</td>
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
            <td class="p-2">{{ $student->passport_number }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">تاريخ الميلاد:</th>
            <td class="p-2">{{ $student->dob }}</td>
        </tr>
        <tr>
            <th class="text-right p-2">المصرف:</th>
            <td class="p-2">{{ $student->bank_name }}</td>
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
            <td class="p-2">{{ $student->family_record }}</td>
        </tr>
    </table>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-100 rounded">عودة</a>
      
    <button onclick="printTable()" class="px-4 py-2 bg-blue-600 text-white rounded">طباعة</button>

    </div>
</div>
<!-- ضع هذا في Blade -->
<script>
function printTable() {
    const img = document.getElementById('student-photo'); // تحديد العنصر بدقة
    const imgHtml = img ? `<img src="${img.src}" alt="صورة الطالب" style="display:block;margin:0 auto 10px;border-radius:50%;width:100px;height:100px;object-fit:cover;">` : '';
    const tableHtml = document.querySelector('table').outerHTML;

    const newWin = window.open('', '_blank', 'width=800,height=600');
    newWin.document.write(`
        <html>
            <head>
                <title>طباعة بيانات الطالب</title>
                <style>
                    table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; direction: rtl; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
                    th { background-color: #f0f0f0; }
                </style>
            </head>
            <body>
                ${imgHtml}
                ${tableHtml}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}
</script>



@endsection
