<header class="bg-white border-b p-4 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <button class="lg:hidden p-2 rounded bg-gray-100">☰</button>
    <!-- نموذج البحث تم إزالته -->
  </div>

  <div class="flex items-center gap-3">
    <div class="text-sm text-gray-600">
      مرحبا، {{ $institute->name ?? 'المؤسسة' }}
    </div>
    <img 
        src="{{ isset($institute->logo) ? asset('storage/' . $institute->logo) : '/images/placeholder.png' }}" 
        alt="شعار المؤسسة" 
        class="w-10 h-10 rounded-full object-contain"
    >
  </div>
</header>
