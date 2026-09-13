<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
 use Illuminate\Support\Facades\DB;



use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // جلب كل المستخدمين بصيغة JSON
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    // عرض صفحة المستخدمين
    public function showPage()
    {
        $roles = Role::with('permissions')->get();
        return view('data_management.users', compact('roles'));
    }


public function store(Request $request)
{
    // تحويل القيمة إلى مصفوفة في حال تم إرسال دور واحد كـ integer/string
    if ($request->has('roles') && !is_array($request->roles)) {
        $request->merge(['roles' => [$request->roles]]);
    }

    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'email'     => 'required|string|email|max:255|unique:users,email',
        'password'  => 'required|string|min:6',
        'roles'     => 'required|array|min:1',
        'roles.*'   => 'exists:roles,id',
        'is_active' => 'required|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    return DB::transaction(function () use ($request) {
        $username = Str::slug($request->full_name, '-', 'ar') ?: 'user';
        $username .= rand(100, 999);

        while (User::where('username', $username)->exists()) {
            $username = Str::slug($request->full_name, '-', 'ar') ?: 'user';
            $username .= rand(100, 999);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'username'  => $username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => $request->is_active,
        ]);

        // ربط الأدوار
        $user->roles()->sync($request->roles);

        return response()->json($user->load('roles'), 201);
    });
}

    // تفعيل/إيقاف المستخدم
    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json(['status' => 'success', 'is_active' => $user->is_active]);
    }

    // حذف المستخدم
    public function destroy(User $user)
    {
        $user->roles()->detach(); // فك ارتباط الأدوار
        $user->delete();

        return response()->json(['status' => 'success']);
    }

public function showChangePasswordForm()
{
    $user = Auth::user(); // هذا يجب أن يكون كائن User
    return view('data_management.change-password', compact('user'));
}

public function updatePassword(Request $request)
{
    $user = Auth::user(); // كائن User
    if (!$user instanceof User) {
        abort(500, 'المستخدم الحالي غير موجود أو غير صالح.');
    }

    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ], [
        'password.confirmed' => 'تأكيد كلمة السر غير مطابق.',
    ]);

    $user->password = Hash::make($request->password);
    $user->save(); // هذا يعمل الآن
    return redirect()->route('data_management.change-password')
        ->with('success', 'تم تحديث كلمة السر بنجاح.');
}

}
