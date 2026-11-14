<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Department;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('subjects.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'nullable|integer',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'units' => 'nullable|integer|min:0|max:10',
            'hours' => 'nullable|integer|min:0|max:20',
            'depends_on' => 'nullable|string|max:255',
            'alternative_for' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        Subject::create($data);
        return redirect()->route('subjects.index')->with('success', 'تم إضافة المادة بنجاح');
    }

    public function edit(Subject $subject)
    {
        $departments = Department::all();
        return view('subjects.edit', compact('subject', 'departments'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'number' => 'nullable|integer',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'units' => 'nullable|integer|min:0|max:10',
            'hours' => 'nullable|integer|min:0|max:20',
            'depends_on' => 'nullable|string|max:255',
            'alternative_for' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $subject->update($data);
        return redirect()->route('subjects.index')->with('success', 'تم تعديل المادة بنجاح');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'تم حذف المادة بنجاح');
    }
}
