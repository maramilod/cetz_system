<aside class="w-64 bg-gray-800 text-white min-h-screen">
  <div class="p-4 text-xl font-bold border-b border-gray-700">نظام الكلية</div>

  <nav class="mt-4 space-y-2">
    <!-- لوحة التحكم -->
    <a href="{{ route('dashboard.view') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">📊 لوحة التحكم</a>

    <!-- الأساسيات -->
    <div x-data="{ open: true }" class="px-4">
      <button @click="open = !open" class="flex items-center justify-between w-full py-2 hover:bg-gray-700 rounded">
        <span>🧩 الأساسيات</span>
        <span x-text="open ? '▲' : '▼'"></span>
      </button>

      <div x-show="open" class="mt-2 ml-2 space-y-1" x-transition>
        <a href="{{ route('students.store') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🎓 الطلاب</a>
        <a href="{{ route('departments.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🏫 الأقسام</a>
                <a href="{{ route('semesters.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🏛️ الفصول الدراسية</a>
        <a href="{{ route('courses.create') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">📚 المواد</a>
<a href="{{ route('teaching-assignments.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🗂️ توزيع المواد</a>
        <a href="{{ route('teachers.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded flex items-center gap-2">
    🧑‍🏫 الأساتذة
</a>
 </div>


    </div>

    <!-- التسجيل والقبول -->
    <div x-data="{ open2: false }" class="px-4">
      <button @click="open2 = !open2" class="flex items-center justify-between w-full py-2 hover:bg-gray-700 rounded">
        <span>🧾 التسجيل والقبول</span>
        <span x-text="open2 ? '▲' : '▼'"></span>
      </button>

      <div x-show="open2" class="mt-2 ml-2 space-y-1" x-transition>
        <a href="{{ route('students.create') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">تسجيل الطلبة</a>
        <a href="{{ route('courses.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">إدارة تسجيل المواد</a>
                <a href="{{ route('registration.enrollment-stop') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">تجديد القيد</a>


                <a href="{{ route('materials.download') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">تنزيل المواد</a>

        <a href="{{ route('registration.attendance-form') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">نموذج الحضور والغياب</a>
        <a href="{{ route('registration.student-certificate') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">تعريف الطالب</a>
        <a href="{{ route('students.excel') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">كشف الطلبة  </a>

      </div>
    </div>

    <!-- الدراسة والامتحانات -->
    <div x-data="{ open3: false }" class="px-4">
      <button @click="open3 = !open3" class="flex items-center justify-between w-full py-2 hover:bg-gray-700 rounded">
        <span>📘 الدراسة والامتحانات</span>
        <span x-text="open3 ? '▲' : '▼'"></span>
      </button>

      <div x-show="open3" class="mt-2 ml-2 space-y-1" x-transition>
        <a href="{{ route('results.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">📘 رصد الدرجات</a>
                <a href="{{ route('grades.appeals') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">📄 تعديل النتائج (الطعون)</a>
                        <a href="{{ route('semesters.approved') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">✅ اعتماد النتائج</a>

       
        <a href="{{ route('deprivations.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🚷 الطلبة المحرومين</a>
        <a href="{{ route('alerts.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">⚠️ الاشعارات</a>
      </div>
    </div>

    <!-- الخريجين -->
    <div x-data="{ open4: false }" class="px-4">
      <button @click="open4 = !open4" class="flex items-center justify-between w-full py-2 hover:bg-gray-700 rounded">
        <span>🎓 الخريجين</span>
        <span x-text="open4 ? '▲' : '▼'"></span>
      </button>

      <div x-show="open4" class="mt-2 ml-2 space-y-1" x-transition>
        <a href="{{ route('students.enrollments') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">📑 كشف الدرجات</a>
         <a href="{{ route('graduation-projects.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🎓 كشف طلبة مشروع التخرج</a>
        <a href="{{ route('students.graduated') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🎖️ كشف الخريجين</a>
      </div>
    </div>

    <!-- إدارة البيانات -->
    <div x-data="{ open5: false }" class="px-4">
      <button @click="open5 = !open5" class="flex items-center justify-between w-full py-2 hover:bg-gray-700 rounded">
        <span>⚙️ إدارة البيانات</span>
        <span x-text="open5 ? '▲' : '▼'"></span>
      </button>

      <div x-show="open5" class="mt-2 ml-2 space-y-1" x-transition>
        <a href="{{ route('data_management.change-password') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🔐 تغيير كلمة المرور</a>
<a href="{{ route('users.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">👥 إدارة المستخدمين</a>
        <a href="{{ route('roles.index') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🎯 إدارة الأدوار والصلاحيات</a>
            <a href="{{ route('data.institute-number') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🏷️ رقم المعهد</a>
        <a href="{{ route('data.institute-info') }}" class="block px-3 py-1 hover:bg-gray-700 rounded">🏫 معهد / كلية</a>
      </div>
    </div>



<!-- زر تسجيل الخروج -->
<form method="POST" action="{{ route('logout') }}" dir="rtl">
    @csrf
    <button type="submit" 
            class="w-full text-right px-4 py-2 text-white font-semibold rounded hover:bg-red-700 transition-colors">
        تسجيل الخروج
    </button>
</form>


  </nav>
</aside>

<!-- تفعيل القوائم المنسدلة -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
