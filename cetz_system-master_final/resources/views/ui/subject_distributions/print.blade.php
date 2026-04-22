@extends('layouts.app')

@section('content')
<div class="p-6">
  <h2 class="text-xl font-bold mb-4">طباعة توزيع المواد</h2>

  <table class="w-full text-sm border border-gray-300">
    <thead class="bg-gray-100 border-b">
      <tr>
        <th class="p-2 border">القسم</th>
        <th class="p-2 border">اسم المادة</th>
        <th class="p-2 border">رمز المادة</th>
        <th class="p-2 border">أستاذ المادة</th>
        <th class="p-2 border">رقم الفصل</th>
      </tr>
    </thead>
    <tbody>
      @foreach($distributions as $dist)
      <tr>
        <td class="p-2 border">{{ $dist->department }}</td>
        <td class="p-2 border">{{ $dist->subject_name }}</td>
        <td class="p-2 border">{{ $dist->subject_code }}</td>
        <td class="p-2 border">{{ $dist->teacher }}</td>
        <td class="p-2 border">{{ $dist->semester }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4">
    <button onclick="window.print()" class="px-4 py-2 bg-gray-200 rounded">طباعة</button>
  </div>
</div>
@endsection
