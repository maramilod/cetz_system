<?php

// app/Http/Controllers/TeacherController.php
namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::all();
        return view('teachers.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:teachers',
            'working_id' => 'required|string|unique:teachers',
            'email' => 'required|email|unique:teachers',
        ]);

        Teacher::create($request->all());

        return redirect()->back()->with('success', 'تم إضافة الأستاذ بنجاح');
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:teachers,national_id,'.$teacher->id,
            'working_id' => 'required|string|unique:teachers,working_id,'.$teacher->id,
            'email' => 'required|email|unique:teachers,email,'.$teacher->id,
            'active' => 'boolean',
        ]);

        $teacher->update($request->all());

        return redirect()->back()->with('success', 'تم تعديل الأستاذ بنجاح');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->back()->with('success', 'تم حذف الأستاذ بنجاح');
    }
    public function toggleActive(Request $request, Teacher $teacher)
{
    $request->validate([
        'active' => 'required|boolean',
    ]);

    $teacher->update(['active' => $request->active]);

    return response()->json(['success' => true, 'active' => $teacher->active]);
}

}
