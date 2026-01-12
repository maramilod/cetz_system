<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\TeachingAssignment;
use Carbon\Carbon;

class TeachingAssignmentController extends Controller
{
    public function create()
    {
          $today = Carbon::today();

        $departments = Department::where('is_active', 1)->get();
        $teachers = Teacher::all();

        // جلب CourseOfferings للفصول الحالية فقط
    $courseOfferings = CourseOffering::with('course', 'section', 'semester')
        ->whereHas('semester', function($q) use ($today) {
            $q->where('start_date', '<=', $today)
              ->where('end_date', '>=', $today);
        })
        ->get();

        return view('teaching-assignments.create', compact('departments', 'teachers', 'courseOfferings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'course_offering_id' => 'required|exists:course_offerings,id',
            'role' => 'required|in:نظري,عملي,مساعد',
        ]);

        TeachingAssignment::create([
            'teacher_id' => $request->teacher_id,
            'course_offering_id' => $request->course_offering_id,
            'role' => $request->role,
        ]);

        return redirect()->route('teaching-assignments.index')
                         ->with('success', 'تم إضافة توزيع المادة بنجاح');
    }


    public function index()
{
    $assignments = TeachingAssignment::with('teacher', 'courseOffering.course', 'courseOffering.section')->get();

$distributions = $assignments->map(function($a) {
    return (object) [
        'id' => $a->id,
        'department' => $a->courseOffering->section->name ?? '',
        'subject_name' => $a->courseOffering->course->name ?? '',
        'subject_code' => $a->courseOffering->course->course_code ?? '',
        'teacher' => $a->teacher->full_name ?? '',
        'semester' => $a->courseOffering->semester->semester_number ?? ''
    ];
});


    return view('teaching-assignments.index', compact('distributions'));
}


public function edit($id)
{
    $assignment = TeachingAssignment::with('teacher', 'courseOffering.course', 'courseOffering.section')->findOrFail($id);

    $today = Carbon::today();
    $departments = Department::where('is_active', 1)->get();
    $teachers = Teacher::all();

    // جلب CourseOfferings للفصول الحالية فقط
    $courseOfferings = CourseOffering::with('course', 'section', 'semester')
        ->whereHas('semester', function($q) use ($today) {
            $q->where('start_date', '<=', $today)
              ->where('end_date', '>=', $today);
        })
        ->get();

    return view('teaching-assignments.edit', compact('assignment', 'departments', 'teachers', 'courseOfferings'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'teacher_id' => 'required|exists:teachers,id',
        'course_offering_id' => 'required|exists:course_offerings,id',
        'role' => 'required|in:نظري,عملي,مساعد',
    ]);

    $assignment = TeachingAssignment::findOrFail($id);
    $assignment->update([
        'teacher_id' => $request->teacher_id,
        'course_offering_id' => $request->course_offering_id,
        'role' => $request->role,
    ]);

    return redirect()->route('teaching-assignments.index')
                     ->with('success', 'تم تعديل توزيع المادة بنجاح');
}

public function destroy($id)
{
    $assignment = TeachingAssignment::findOrFail($id);
    $assignment->delete();

    return redirect()->route('teaching-assignments.index')
                     ->with('success', 'تم حذف توزيع المادة بنجاح');
}
public function print()
{
    $assignments = TeachingAssignment::with('teacher', 'courseOffering.course', 'courseOffering.section')->get();

    $distributions = $assignments->map(function($a) {
        return (object) [
            'id' => $a->id,
            'department' => $a->courseOffering->section->name ?? '',
            'subject_name' => $a->courseOffering->course->name ?? '',
            'subject_code' => $a->courseOffering->course->course_code ?? '',
            'teacher' => $a->teacher->full_name ?? '',
            'semester' => $a->courseOffering->semester->semester_number ?? ''
        ];
    });

    return view('teaching-assignments.print', compact('distributions'));
}


}
