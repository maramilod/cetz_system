<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Department;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::all();
        return view('classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        $departments = Department::all(); // لو حاب تربط الفصل بالقسم
        return view('classrooms.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        Classroom::create($request->all());
        return redirect()->route('classrooms.index')->with('success', 'تم إضافة الفصل بنجاح');
    }

    public function edit(Classroom $classroom)
    {
        $departments = Department::all();
        return view('classrooms.edit', compact('classroom', 'departments'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $classroom->update($request->all());
        return redirect()->route('classrooms.index')->with('success', 'تم تعديل الفصل بنجاح');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return redirect()->route('classrooms.index')->with('success', 'تم حذف الفصل بنجاح');
    }
}
