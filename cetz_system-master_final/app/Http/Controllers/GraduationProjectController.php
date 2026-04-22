<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GraduationProject;
use App\Models\ProjectStudent;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GraduationProjectController extends Controller
{
    // قائمة المشاريع
    public function index(Request $request)
    {
        $query = GraduationProject::with(['students']);

        // البحث بالكود إذا تم إدخاله
        if ($request->filled('team_code')) {
            $query->where('team_code', 'like', '%' . $request->team_code . '%');
        }

        $projects = $query->latest()->get();
        $teachers = Teacher::all();

        return view('graduation-projects.index', compact('projects', 'teachers'));
    }

    // نموذج إنشاء مشروع جديد
    public function create()
    {
        $eligibleStudents = Student::whereHas('enrollments.courseOffering.course', function($q) {
            $q->where('name', 'مشروع تخرج');
        })->whereHas('enrollments', function($q) {
            $q->whereIn('status', ['in_progress']);
        })->get();

        $teachers = Teacher::all();

        return view('graduation-projects.create', compact('eligibleStudents', 'teachers'));
    }

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'supervisor_id' => 'required|exists:teachers,id',
        'student_ids' => 'required|array|min:1',
        'student_ids.*' => 'exists:students,id',
    ]);

    $teamCode = Str::upper(Str::random(6));

    DB::beginTransaction();
    try {
        $project = GraduationProject::create([
            'title' => $request->title,
            'team_code' => $teamCode,
            'status' => 'pending',
            'supervisor' => $request->supervisor_id,
        ]);

        foreach ($request->student_ids as $studentId) {
            ProjectStudent::create([
                'graduation_project_id' => $project->id,
                'student_id' => $studentId,
            ]);
        }

        DB::commit();

        // إعادة JSON بدل Redirect
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المشروع بنجاح',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'فشل إنشاء المشروع: ' . $e->getMessage(),
        ], 500);
    }
}


    // عرض نموذج تعديل المشروع
    public function edit(GraduationProject $project)
    {
        $eligibleStudents = Student::whereHas('enrollments.courseOffering.course', function ($q) {
            $q->where('name', 'مشروع تخرج');
        })->whereHas('enrollments', function ($q) {
            $q->whereIn('status', ['in_progress']);
        })->get();

        $teachers = Teacher::all();

        $project->load(['students']);
        $supervisor = $project->supervisor;

        return view('graduation-projects.edit', compact(
            'project',
            'eligibleStudents',
            'teachers',
            'supervisor'
        ));
    }

    // تحديث المشروع
    public function update(Request $request, GraduationProject $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:teachers,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            // تحديث بيانات المشروع والمشرف مباشرة
            $project->update([
                'title' => $request->title,
                'supervisor' => $request->supervisor_id,
            ]);

            // تحديث الطلاب
            $project->students()->sync($request->student_ids);

            DB::commit();

            return redirect()->route('graduation-projects.index')
                ->with('success', 'تم تحديث المشروع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'فشل التحديث: ' . $e->getMessage()
            ]);
        }
    }

    // حذف المشروع
    public function destroy(GraduationProject $project)
    {
        $project->delete();
        return redirect()->route('graduation-projects.index')
            ->with('success', 'تم حذف المشروع بنجاح');
    }

    // اعتماد المشروع
    public function pass($id)
    {
        $project = GraduationProject::findOrFail($id);
        $project->status = 'approved';
        $project->save();

        return redirect()->route('graduation-projects.index')
                         ->with('success', 'تم اعتماد المشروع بنجاح.');
    }
}
