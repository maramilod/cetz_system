@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6" x-data="usersManager()" x-init="init()">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">إدارة المستخدمين</h1>

        <div class="flex gap-3 mb-4">
            <select x-model="roleFilter" class="border rounded px-3 py-2">
                <option value="">كل الأدوار</option>
                <template x-for="role in roles" :key="role.id">
                    <option :value="role.id" x-text="role.display_name"></option>
                </template>
            </select>
            <select x-model="statusFilter" class="border rounded px-3 py-2">
                <option value="">الكل</option>
                <option value="active">نشط</option>
                <option value="disabled">موقوف</option>
            </select>
            <input type="text" x-model="search" placeholder="بحث" class="border rounded px-3 py-2">
            <button @click="showModal=true" class="bg-blue-600 text-white px-3 py-2 rounded">➕ إضافة مستخدم</button>
        </div>

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">الاسم</th>
                    <th class="border px-2 py-1">البريد</th>
                    <th class="border px-2 py-1">الدور</th>
                    <th class="border px-2 py-1">الحالة</th>
                    <th class="border px-2 py-1">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="!filteredRecords.length">
                    <tr><td colspan="5" class="text-center p-2">لا يوجد مستخدمون</td></tr>
                </template>
                <template x-for="user in filteredRecords" :key="user.id">
                    <tr>
                        <td class="border px-2 py-1" x-text="user.full_name"></td>
                        <td class="border px-2 py-1" x-text="user.email"></td>
                        <td class="border px-2 py-1" x-text="roles.find(r => r.id===user.role_id)?.display_name"></td>
                        <td class="border px-2 py-1">
                            <span :class="user.is_active?'bg-green-100 text-green-700':'bg-red-100 text-red-700'" class="px-2 py-1 rounded" x-text="user.is_active?'نشط':'موقوف'"></span>
                        </td>
                        <td class="border px-2 py-1">
                            <button @click="toggleUser(user)" class="px-2 py-1 bg-gray-200 rounded" x-text="user.is_active?'إيقاف':'تفعيل'"></button>
                            <button @click="removeUser(user)" class="px-2 py-1 bg-red-100 text-red-700 rounded">حذف</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal إضافة مستخدم -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded w-96">
            <h2 class="text-xl font-bold mb-2">إضافة مستخدم</h2>
            <form @submit.prevent="submitUser">
                <input type="text" x-model="newUser.full_name" placeholder="الاسم الكامل" class="border rounded w-full px-2 py-1 mb-2" required>
                <input type="email" x-model="newUser.email" placeholder="البريد الإلكتروني" class="border rounded w-full px-2 py-1 mb-2" required>
                <input type="password" x-model="newUser.password" placeholder="كلمة المرور" class="border rounded w-full px-2 py-1 mb-2" required>
                <select x-model="newUser.role_id" class="border rounded w-full px-2 py-1 mb-2" required>
                    <template x-for="role in roles" :key="role.id">
                        <option :value="role.id" x-text="role.display_name"></option>
                    </template>
                </select>
                
                <select x-model="newUser.is_active" class="border rounded w-full px-2 py-1 mb-2">
                    <option :value="true">نشط</option>
                    <option :value="false">موقوف</option>
                </select>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showModal=false" class="border px-3 py-1 rounded">إلغاء</button>
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function csrf(){ return document.querySelector('meta[name="csrf-token"]').content; }
document.addEventListener('alpine:init',()=>{
    Alpine.data('usersManager',()=>{
        return {
            records:[], roles:[], showModal:false,
            newUser:{full_name:'',email:'',password:'',role_id:null,is_active:true},
            roleFilter:'', statusFilter:'', search:'',

            init(){ this.loadRoles(); this.loadUsers(); },
            loadRoles(){ fetch('/roles').then(r=>r.json()).then(d=>this.roles=d); },
            loadUsers(){ fetch('/users').then(r=>r.json()).then(d=>{ this.records=d.map(u=>({...u,role_id:u.roles[0]?.id||null})); }); },

      submitUser() {
    fetch('/users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            full_name: this.newUser.full_name,
            email: this.newUser.email,
            password: this.newUser.password,
            is_active: this.newUser.is_active,
            roles: [this.newUser.role_id]   // array
        })
    })
    .then(async res => {
        const data = await res.json();
        console.log('Server response:', data); // ✅ اطبع كل البيانات هنا
        if(data.errors){
            console.error('Validation errors:', data.errors); // ✅ اطبع تفاصيل الـ validation
            alert(Object.values(data.errors).flat().join('\n'));
        } else if(data.message){
            alert(data.message);
        } else {
            this.showModal = false;
            this.newUser = { full_name:'', email:'', password:'', role_id:1, is_active:true };
            this.loadUsers();
        }
    })
    .catch(err => {
        console.error('Fetch error:', err); // ✅ اطبع أي خطأ في الشبكة أو السيرفر
        alert('حدث خطأ أثناء الاتصال بالسيرفر. تحقق من الـ console.');
    });
},

            toggleUser(u){
                fetch(`/users/${u.id}/toggle`,{method:'PUT',headers:{'X-CSRF-TOKEN':csrf()}})
                .then(()=>{ u.is_active=!u.is_active; });
            },

            removeUser(u){
                if(!confirm('هل تريد حذف المستخدم؟')) return;
                fetch(`/users/${u.id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf()}})
                .then(()=>{ this.records=this.records.filter(x=>x.id!==u.id); });
            },

            get filteredRecords(){
                return this.records.filter(u=>
                    (!this.search || u.full_name.includes(this.search)||u.email.includes(this.search))
                    && (!this.roleFilter || u.role_id==this.roleFilter)
                    && (!this.statusFilter || (this.statusFilter==='active'?u.is_active:!u.is_active))
                );
            }
        };
    });
});
</script>
@endsection
