@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{
    dept:'',
    semester:'',
    search:'',
    match(row){
        const s = this.search.trim().toLowerCase();
        const okDept = !this.dept || row.dept === this.dept;
        const okSem = !this.semester || row.sem === this.semester;
        const okSearch = !s || 
            (row.subject && row.subject.toLowerCase().includes(s)) ||
            (row.code && row.code.toLowerCase().includes(s)) ||
            (row.teacher && row.teacher.toLowerCase().includes(s));
        return okDept && okSem && okSearch;
    }
}">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">توزيع المواد</h1>
    <a href="{{ route('teaching-assignments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">إضافة توزيع جديد</a>
  </div>

  <div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap gap-3 items-end mb-3">
      <!-- القسم -->
      <div class="min-w-[200px]">
        <label class="block text-sm text-gray-600 mb-1">الشعبة</label>
        <select x-model="dept" class="border rounded px-3 py-2 w-full">
          <option value="">كل الأقسام</option>
          @php($deptOptions = collect($distributions)->pluck('department')->filter()->unique())
          @foreach($deptOptions as $d)
            <option value="{{ $d }}">{{ $d }}</option>
          @endforeach
        </select>
      </div>

      <!-- الفصل -->
      <div class="min-w-[180px]">
        <label class="block text-sm text-gray-600 mb-1">الفصل</label>
        <select x-model="semester" class="border rounded px-3 py-2 w-full">
          <option value="">كل الفصول</option>
          @php($semOptions = collect($distributions)->pluck('semester')->filter()->unique())
          @foreach($semOptions as $s)
            <option value="{{ $s }}">{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <!-- البحث -->
      <div class="flex-1 min-w-[220px]">
        <label class="block text-sm text-gray-600 mb-1">بحث</label>
        <input type="text" x-model.trim="search" placeholder="اسم/رمز المادة أو الأستاذ" class="border rounded px-3 py-2 w-full">
      </div>
    </div>

    <!-- جدول التوزيع -->
    <table id="distributionTable" class="w-full text-sm border-collapse border border-gray-300">
      <thead class="bg-gray-100 border-b">
        <tr>
          <th class="p-2 border">القسم</th>
          <th class="p-2 border">اسم المادة</th>
          <th class="p-2 border">رمز المادة</th>
          <th class="p-2 border">أستاذ المادة</th>
          <th class="p-2 border">رقم الفصل</th>
          <th class="p-2 border text-right">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($distributions as $dist)
        <tr class="border-b hover:bg-gray-50"
            x-show="match(@js([
                'dept' => $dist->department,
                'subject' => $dist->subject_name,
                'code' => $dist->subject_code ?? '',
                'teacher' => $dist->teacher ?? '',
                'sem' => $dist->semester ?? ''
            ]))">
          <td class="p-2 border">{{ $dist->department }}</td>
          <td class="p-2 border">{{ $dist->subject_name }}</td>
          <td class="p-2 border">{{ $dist->subject_code ?? '' }}</td>
          <td class="p-2 border">{{ $dist->teacher ?? '' }}</td>
          <td class="p-2 border">{{ $dist->semester ?? '' }}</td>
          <td class="p-2 border text-right space-x-2 rtl:space-x-reverse">
            <a href="{{ route('teaching-assignments.edit', $dist->id) }}" class="text-green-600">تعديل</a>

            <form action="{{ route('teaching-assignments.destroy', $dist->id) }}" method="POST" class="inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-600" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mt-4 flex justify-end">
      <button onclick="printTable()" class="px-4 py-2 bg-gray-200 rounded">طباعة</button>
    </div>
  </div>
</div>

<script>
function printTable() {
    // نسخ الجدول بالكامل
    const table = document.querySelector('#distributionTable');
    const tableClone = table.cloneNode(true);

    // إزالة آخر عمود (الإجراءات) من thead
    const thead = tableClone.querySelector('thead tr');
    thead.removeChild(thead.lastElementChild);

    // إزالة آخر عمود من كل صف tbody
    tableClone.querySelectorAll('tbody tr').forEach(row => {
        row.removeChild(row.lastElementChild);
    });

    // فتح نافذة جديدة للطباعة
    const newWin = window.open('', '_blank', 'width=900,height=600');
    newWin.document.write(`
        <html>
            <head>
                <title>طباعة الجدول</title>
                <style>
                    table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; direction: rtl; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
                    th { background-color: #f0f0f0; }
                </style>
            </head>
            <body>${tableClone.outerHTML}</body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}
</script>

@endsection
