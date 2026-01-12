@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="teachersList(@js($teachers))">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">إدارة الأساتذة</h1>

        <!-- إضافة أستاذ -->
        <form method="POST" action="{{ route('teachers.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            @csrf
            <input type="text" name="full_name" placeholder="الاسم الكامل" class="border rounded px-3 py-2" required>
            <input type="text" name="working_id" placeholder="الرقم الوظيفي" class="border rounded px-3 py-2" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" class="border rounded px-3 py-2" required>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">إضافة أستاذ</button>
        </form>

        <!-- قائمة الأساتذة -->
        <table class="min-w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2 text-right">الاسم الكامل</th>
                    <th class="border px-3 py-2 text-right">الرقم الوظيفي</th>
                    <th class="border px-3 py-2 text-right">البريد الإلكتروني</th>
                    <th class="border px-3 py-2 text-right">الحالة</th>
                    <th class="border px-3 py-2 text-right">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="teacher in teachers" :key="teacher.id">
                    <tr class="hover:bg-gray-50">
                        <td class="border px-3 py-2" x-text="teacher.full_name"></td>
                        <td class="border px-3 py-2" x-text="teacher.working_id"></td>
                        <td class="border px-3 py-2" x-text="teacher.email"></td>
                        <td class="border px-3 py-2">
                            <input type="checkbox" x-model="teacher.active" @change="toggleActive(teacher)" class="rounded">
                        </td>
                        <td class="border px-3 py-2 flex gap-2">
                            <button type="button" class="bg-blue-600 text-white px-2 py-1 rounded" @click="editTeacher(teacher)">تعديل</button>
                            <button type="button" class="bg-red-600 text-white px-2 py-1 rounded" @click="deleteTeacher(teacher)">حذف</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('teachersList', (initialTeachers) => ({
        teachers: initialTeachers,
        newTeacher: { full_name: '', working_id: '', email: '', active: true },

        addTeacher() {
            if(!this.newTeacher.full_name || !this.newTeacher.working_id || !this.newTeacher.email) return;
            const id = Date.now();
            this.teachers.push({ ...this.newTeacher, id });
            this.newTeacher = { full_name: '',  working_id: '', email: '', active: true };
        },
  // تعديل أستاذ
        editTeacher(teacher) {
            const full_name = prompt("الاسم الكامل:", teacher.full_name);
            if(full_name === null) return;
         
            const working_id = prompt("الرقم الوظيفي:", teacher.working_id);
            if(working_id === null) return;
            const email = prompt("البريد الإلكتروني:", teacher.email);
            if(email === null) return;

            axios.put(`/teachers/${teacher.id}`, {
                full_name,
                working_id,
                email
            })
           
                teacher.full_name = full_name;
                teacher.working_id = working_id;
                teacher.email = email;
                alert('تم تعديل الأستاذ بنجاح');
           
          
        },
deleteTeacher(teacher) {
    if(confirm("هل تريد حذف هذا الأستاذ؟")) {
        axios.delete(`/teachers/${teacher.id}`)
        
            this.teachers = this.teachers.filter(t => t.id !== teacher.id);
        
    }
},

        toggleActive(teacher) {
            axios.patch(`/teachers/${teacher.id}/toggle-active`, {
                active: teacher.active ? 1 : 0
            })
            .then(res => {
                console.log(`${teacher.full_name} updated active: ${teacher.active}`);
            })
            .catch(err => {
                console.error(err);
                alert('حدث خطأ أثناء تحديث الحالة');
                teacher.active = !teacher.active;
            });
        }
    }));
});
</script>
@endsection
