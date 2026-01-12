<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;


class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
    }

    public function showPage()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('data_management.roles', compact('roles', 'permissions'));
    }

 public function store(Request $request)
{
    $request->validate([
        'display_name' => 'required|string|max:255',
        'description'  => 'nullable|string',
    ]);

    $role = Role::create([
        'name' => Str::slug($request->display_name),
        'display_name' => $request->display_name,
        'description' => $request->description,
    ]);

    $role->load('permissions');

    return response()->json($role);
}


    public function update(Request $request, Role $role)
    {
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }
        return response()->json(['message' => 'تم تحديث الصلاحيات']);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'تم حذف الدور']);
    }
}
