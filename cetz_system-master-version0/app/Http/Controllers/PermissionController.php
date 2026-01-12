<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // إضافة صلاحية جديدة
    public function store(Request $request)
    {
        $permission = Permission::create($request->only('display_name'));
        return response()->json($permission);
    }

    // حذف صلاحية
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
