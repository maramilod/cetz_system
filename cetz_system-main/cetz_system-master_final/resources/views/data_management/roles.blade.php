@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div x-data="rolesManager({{ $roles->toJson() }}, {{ $permissions->toJson() }})"
     class="p-6 bg-white rounded shadow">

    <h2 class="text-2xl font-bold mb-4">إدارة الأدوار والصلاحيات</h2>

    <div class="flex gap-6">

        <!-- ===== Roles List ===== -->
        <div class="w-1/3">
            <div class="flex justify-between mb-2">
                <h3 class="font-semibold">الأدوار</h3>
                <button @click="showAddRole = true"
                        class="px-2 py-1 bg-green-600 text-white rounded text-sm">
                    ➕ إضافة دور
                </button>
            </div>

            <template x-for="role in roles" :key="role.id">
                <div class="p-2 border mb-1 cursor-pointer flex justify-between items-center"
                     :class="{'bg-gray-100': selectedRole && selectedRole.id === role.id}"
                     @click="selectRole(role)">
                    <span x-text="role.display_name"></span>
                    <button @click.stop="deleteRole(role)"
                            class="text-red-500 text-sm">✖</button>
                </div>
            </template>
        </div>

        <!-- ===== Permissions + Role Info ===== -->
        <template x-if="selectedRole">
            <div class="w-2/3 space-y-4">

                <!-- Permissions -->
                <div>
                    <div class="flex justify-between mb-2">
                        <h3 class="font-semibold">
                            صلاحيات: <span x-text="selectedRole.display_name"></span>
                        </h3>
                    </div>

                    <template x-for="perm in permissions" :key="perm.id">
                        <div class="flex items-center justify-between mb-1 p-2 border rounded">
                            <label class="flex-1">
                                <input type="checkbox"
                                       :value="perm.id"
                                       x-model="selectedPermissions">
                                <span x-text="perm.display_name"></span>
                            </label>
                          
                        </div>
                    </template>

                    <button @click="savePermissions()"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                        💾 حفظ الصلاحيات
                    </button>
                </div>

                <!-- ===== Role Description Panel (BEST PART) ===== -->
                <div class="p-4 border rounded bg-gray-50">
                    <h4 class="font-semibold mb-1">ℹ️ معلومات الدور</h4>

                    <p class="text-sm text-gray-700 mb-2"
                       x-text="selectedRole.description || 'لا يوجد وصف لهذا الدور'">
                    </p>

                    <div class="text-xs text-gray-500">
                        عدد الصلاحيات:
                        <span x-text="selectedPermissions.length"></span>
                    </div>
                </div>

            </div>
        </template>
    </div>

    <!-- ===== Add Role Modal ===== -->
    <template x-if="showAddRole">
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-96 p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">إضافة دور جديد</h3>

                <div class="mb-3">
                    <label class="block text-sm mb-1">اسم الدور</label>
                    <input type="text"
                           x-model="newRole.display_name"
                           class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-3">
                    <label class="block text-sm mb-1">الوصف</label>
                    <textarea x-model="newRole.description"
                              class="w-full border rounded px-3 py-2"></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeAddRole()"
                            class="px-3 py-1 border rounded">
                        إلغاء
                    </button>

                    <button @click="saveRole()"
                            class="px-3 py-1 bg-green-600 text-white rounded">
                        حفظ
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function csrf(){
    return document.querySelector('meta[name="csrf-token"]').content;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('rolesManager', (rolesData, permissionsData) => ({
        roles: rolesData.map(r => ({...r, permissions: r.permissions || []})),
        permissions: permissionsData,

        selectedRole: null,
        selectedPermissions: [],

        showAddRole: false,
        newRole: { display_name:'', description:'' },

        selectRole(role){
            this.selectedRole = role;
            this.selectedPermissions = role.permissions.map(p => p.id);
        },

        closeAddRole(){
            this.showAddRole = false;
            this.newRole = { display_name:'', description:'' };
        },

        saveRole(){
            if(!this.newRole.display_name){
                alert('اسم الدور مطلوب');
                return;
            }

            fetch('/roles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(this.newRole)
            })
            .then(r => r.json())
            .then(role => {
                role.permissions = [];
                this.roles.push(role);
                this.closeAddRole();
            });
        },

        deleteRole(role){
            if(!confirm('هل تريد حذف الدور؟')) return;

            fetch(`/roles/${role.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf() }
            }).then(() => {
                this.roles = this.roles.filter(r => r.id !== role.id);
                if(this.selectedRole?.id === role.id){
                    this.selectedRole = null;
                    this.selectedPermissions = [];
                }
            });
        },

        addPermission(){
            const name = prompt('اسم الصلاحية:');
            if(!name) return;

            fetch('/permissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({ display_name: name })
            })
            .then(r => r.json())
            .then(p => this.permissions.push(p));
        },

        deletePermission(perm){
            if(!confirm('هل تريد حذف الصلاحية؟')) return;

            fetch(`/permissions/${perm.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf() }
            }).then(() => {
                this.permissions = this.permissions.filter(p => p.id !== perm.id);
                this.selectedPermissions =
                    this.selectedPermissions.filter(id => id !== perm.id);
            });
        },

       savePermissions() {
    if (!this.selectedRole) return;

    fetch(`/roles/${this.selectedRole.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf()
        },
        body: JSON.stringify({
            permissions: this.selectedPermissions
        })
    })
    .then(r => {
        if (!r.ok) throw new Error('حدث خطأ أثناء الحفظ');
        return r.json();
    })
    .then(data => {
        // تحديث الصلاحيات داخل الكائن المحدد حالياً
        this.selectedRole.permissions = this.permissions.filter(p => 
            this.selectedPermissions.includes(p.id)
        );

        // تحديث الكائن الرئيسي داخل القائمة
        let index = this.roles.findIndex(r => r.id === this.selectedRole.id);
        if (index !== -1) {
            this.roles[index].permissions = [...this.selectedRole.permissions];
        }

        alert('تم حفظ الصلاحيات بنجاح');
    })
    .catch(err => {
        alert(err.message || 'فشل الاتصال بالسيرفر');
    });
}
    }));
});
</script>
@endsection
