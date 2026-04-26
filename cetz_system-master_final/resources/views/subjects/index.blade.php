@extends('layouts.app')

@section('content')
<div class="p-6" x-data="{code:'',name:'',units:'',hours:'',depends:'',alt:'',user:'',
  match(row){
    const s=(v)=>String(v||'').toLowerCase();
    const okCode=!this.code||s(row.code).includes(s(this.code));
    const okName=!this.name||s(row.name).includes(s(this.name));
    const okUnits=!this.units||s(row.units)==s(this.units)||s(row.units).includes(s(this.units));
    const okHours=!this.hours||s(row.hours)==s(this.hours)||s(row.hours).includes(s(this.hours));
    const okDep=!this.depends||s(row.depends_on).includes(s(this.depends));
    const okAlt=!this.alt||s(row.alternative_for).includes(s(this.alt));
    const okUser=!this.user||s(row.user_name).includes(s(this.user));
    return okCode&&okName&&okUnits&&okHours&&okDep&&okAlt&&okUser;
  }
}">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">المواد</h1>
        <a href="{{ route('subjects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">إضافة مادة</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-3 border-b flex flex-wrap gap-3 items-end">
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">رمز المادة</label>
                <input type="text" x-model.trim="code" class="border rounded px-3 py-2 w-full" placeholder="مثال: CS101">
            </div>
            <div class="min-w-[200px]">
                <label class="block text-sm text-gray-600 mb-1">اسم المادة</label>
                <input type="text" x-model.trim="name" class="border rounded px-3 py-2 w-full" placeholder="اسم المادة">
            </div>
            <div class="min-w-[120px]">
                <label class="block text-sm text-gray-600 mb-1">الوحدات</label>
                <input type="text" x-model.trim="units" class="border rounded px-3 py-2 w-full" placeholder="مثال: 3">
            </div>
            <div class="min-w-[120px]">
                <label class="block text-sm text-gray-600 mb-1">الساعات</label>
                <input type="text" x-model.trim="hours" class="border rounded px-3 py-2 w-full" placeholder="مثال: 4">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-sm text-gray-600 mb-1">تعتمد على</label>
                <input type="text" x-model.trim="depends" class="border rounded px-3 py-2 w-full" placeholder="رمز/اسم المادة">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-sm text-gray-600 mb-1">بديلة عن</label>
                <input type="text" x-model.trim="alt" class="border rounded px-3 py-2 w-full" placeholder="رمز/اسم المادة">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm text-gray-600 mb-1">المستخدم</label>
                <input type="text" x-model.trim="user" class="border rounded px-3 py-2 w-full" placeholder="اسم المستخدم">
            </div>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">رمز المادة</th>
                    <th class="p-2">اسم المادة</th>
                    <th class="p-2">الوحدات</th>
                    <th class="p-2">الساعات</th>
                    <th class="p-2">تعتمد على</th>
                    <th class="p-2">بديلة عن</th>
                    <th class="p-2">المستخدم</th>
                    <th class="p-2 text-right">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                @php($row = [
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'units' => $subject->units,
                    'hours' => $subject->hours,
                    'depends_on' => $subject->depends_on,
                    'alternative_for' => $subject->alternative_for,
                    'user_name' => $subject->user_name,
                ])
                <tr class="border-b hover:bg-gray-50" x-show="match(@js($row))">
                    <td class="p-2">{{ $loop->iteration }}</td>
                    <td class="p-2">{{ $subject->code }}</td>
                    <td class="p-2">{{ $subject->name }}</td>
                    <td class="p-2">{{ $subject->units }}</td>
                    <td class="p-2">{{ $subject->hours }}</td>
                    <td class="p-2">{{ $subject->depends_on }}</td>
                    <td class="p-2">{{ $subject->alternative_for }}</td>
                    <td class="p-2">{{ $subject->user_name }}</td>
                    <td class="p-2 text-right space-x-2 rtl:space-x-reverse">
                        <a href="{{ route('subjects.edit', $subject) }}" class="text-green-600">تعديل</a>
                        <form action="{{ route('subjects.destroy', $subject) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
