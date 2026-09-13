@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <h1 class="text-2xl font-bold">تغيير كلمة السر</h1>
        <p class="text-gray-600">يمكنك تحديث كلمة السر الخاصة بحسابك هنا.</p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('data_management.update-password') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">كلمة السر الجديدة</label>
                <input type="password" name="password" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="أدخل كلمة السر الجديدة">
                @error('password')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">تأكيد كلمة السر</label>
                <input type="password" name="password_confirmation" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="أعد إدخال كلمة السر">
            </div>

            <button type="submit" 
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition">
                تحديث كلمة السر
            </button>
        </form>
    </div>
</div>
@endsection
