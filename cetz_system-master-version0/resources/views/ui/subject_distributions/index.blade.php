@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{dept:'',semester:'',search:'',match(row){const s=this.search.trim().toLowerCase();const okDept=!this.dept||row.dept===this.dept;const okSem=!this.semester||row.sem===this.semester;const okSearch=!s||(row.subject.toLowerCase().includes(s)||row.code.toLowerCase().includes(s)||row.teacher.toLowerCase().includes(s));return okDept&&okSem&&okSearch;}}">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">توزيع المواد</h1>
    <a href="{{ route('subject-distributions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">إضافة توزيع جديد</a>
  </div>

  <div class="bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap gap-3 items-end mb-3">
      <div class="min-w-[200px]">
        <label class="block text-sm text-gray-600 mb-1">القسم</label>
        <select x-model="dept" class="border rounded px-3 py-2 w-full">
          <option value="">كل الأقسام</option>
          @php($deptOptions = collect($distributions)->pluck('department')->filter()->unique())
          @foreach($deptOptions as $d)
            <option value="{{ $d }}">{{ $d }}</option>
          @endforeach
        </select>
      </div>
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
      <div class="flex-1 min-w-[220px]">
        <label class="block text-sm text-gray-600 mb-1">بحث</label>
        <input type="text" x-model.trim="search" placeholder="اسم/رمز المادة أو الأستاذ" class="border rounded px-3 py-2 w-full">
      </div>
    </div>

    <table class="w-full text-sm">
      <thead class="bg-gray-100 border-b">
        <tr>
          <th class="p-2">القسم</th>
          <th class="p-2">اسم المادة</th>
          <th class="p-2">رمز المادة</th>
          <th class="p-2">أستاذ المادة</th>
          <th class="p-2">رقم الفصل</th>
          <th class="p-2 text-right">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($distributions as $dist)
        @php($row = ['dept'=>$dist->department,'subject'=>$dist->subject_name,'code'=>$dist->subject_code ?? '', 'teacher'=>$dist->teacher ?? '', 'sem'=>$dist->semester ?? ''])
        <tr class="border-b hover:bg-gray-50" x-show="match(@js($row))">
          <td class="p-2">{{ $row['dept'] }}</td>
          <td class="p-2">{{ $row['subject'] }}</td>
          <td class="p-2">{{ $row['code'] }}</td>
          <td class="p-2">{{ $row['teacher'] }}</td>
          <td class="p-2">{{ $row['sem'] }}</td>
          <td class="p-2 text-right space-x-2 rtl:space-x-reverse">
            <a href="{{ route('subject-distributions.edit', $dist->id) }}" class="text-green-600">تعديل</a>
            <form action="{{ route('subject-distributions.destroy', $dist->id) }}" method="POST" class="inline">
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
      <a href="{{ route('subject-distributions.print') }}" class="px-4 py-2 bg-gray-200 rounded">طباعة</a>
    </div>
  </div>
</div>
@endsection
