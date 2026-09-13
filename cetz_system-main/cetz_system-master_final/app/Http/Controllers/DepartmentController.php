<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
   public function index()
{
    $departments = Department::with([
        'sections' => function($q) {
            $q->orderBy('name');
        },
        'students',
        'updatedByUser'
    ])->get()->map(function($d) {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'is_general' => $d->is_general,
            'is_active' => $d->is_active,
            'updated_by_name' => $d->updatedByUser->full_name ?? null,
            'students_count' => $d->students->count(),
            'sections' => $d->sections->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'is_active' => $s->is_active,
                    'updated_by_name' => $s->updatedBy->full_name ?? null,
                    'students_count' => $s->students->count(),
                ];
            }),
        ];
    });

    return view('departments.index', compact('departments'));
}

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $department = Department::create([
    'name' => $request->name,
    'is_general' => (int)$request->is_general, // تحويل القيمة إلى 0 أو 1
]);

 if ($department->is_general) {
        $department->sections()->create([
            'department_id' => $department->id,
            'name' => $department->name,
            'is_active' => 1,
        ]);
    }

    return redirect()->route('departments.index')
        ->with('success', 'تم إضافة القسم بنجاح');
}


    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $department->update($request->all());
        return redirect()->route('departments.index')->with('success', 'تم تعديل القسم بنجاح');
    }

public function destroy(Department $department)
{
    // تحقق إذا كان القسم يحتوي على طلاب
    if ($department->students()->count() > 0) {
        return redirect()->route('departments.index')
            ->with('error', 'لا يمكن حذف هذا القسم لأنه يحتوي على طلاب.');
    }

    $department->delete();

    return redirect()->route('departments.index')
        ->with('success', 'تم حذف القسم بنجاح.');
}
public function toggle(Department $department)
{
    $department->is_active = !$department->is_active;
$department->updated_by = Auth::id();
    $department->save();

    return redirect()->route('departments.index')
        ->with('success', 'تم تحديث حالة القسم بنجاح.');
}



}
