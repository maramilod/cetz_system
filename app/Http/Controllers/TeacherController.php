<?php

// app/Http/Controllers/TeacherController.php
namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\AcademicRank;
use App\Models\TeacherRank;
use App\Models\EmploymentStatus;
use App\Models\TeacherEmploymentStatus;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
public function index()
{
    $teachers = Teacher::with([
        'currentRank.academicRank',
        'currentEmploymentStatus.employmentStatus',
        'teachingAssignments.courseOffering.course',
        'teachingAssignments.courseOffering.semester',
    ])->get();

   $teachersJson = $teachers->map(function($t) {
    $assignments = $t->teachingAssignments;

    $activeAssignments = $assignments->filter(function ($a) {
        return $a->courseOffering
            && $a->courseOffering->semester
            && $a->courseOffering->semester->active == 1;
    });

    return [
        'id' => $t->id,
        'full_name' => $t->full_name,
        'working_id' => $t->working_id,
        'email' => $t->email,
        'active' => $t->active,

        'academic_rank' => $t->currentRank->academicRank->name ?? null,
        'employment_status' => $t->currentEmploymentStatus->employmentStatus->name ?? null,

        'ranks' => $t->teacherRanks->map(fn($r) => [
            'academicRank' => $r->academicRank->name ?? '-',
            'from_date' => $r->from_date,
            'to_date' => $r->to_date,
        ]),

        'employment_statuses' => $t->teacherEmploymentStatuses->map(fn($s) => [
            'employmentStatus' => $s->employmentStatus->name ?? '-',
            'from_date' => $s->from_date,
            'to_date' => $s->to_date,
        ]),
        // كل الفصول
        'total_hours' => $assignments->sum(fn($a) => $a->courseOffering?->course->hours ?? 0),
'total_units' => $assignments->sum(fn($a) => $a->courseOffering?->course->units ?? 0),


        // ⭐ الفصل الحالي فقط
'current_semester_hours' => $activeAssignments->sum(fn($a) => $a->courseOffering?->course->hours ?? 0),
'current_semester_units' => $activeAssignments->sum(fn($a) => $a->courseOffering?->course->units ?? 0),


        'teachingAssignments' => $assignments->map(fn($a) => [
            'course_name' => $a->courseOffering->course->name ?? '-',
            'course_units' => $a->courseOffering->course->units ?? 0,
            'course_hours' => $a->courseOffering->course->hours ?? 0,
            'semester_name' => $a->courseOffering->semester->name ?? 'غير محدد',
        ]),
    ];
});


    return view('teachers.index', [
        'teachers' => $teachersJson,
        'academicRanks' => AcademicRank::all(),
        'employmentStatuses' => EmploymentStatus::all(),
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'full_name' => 'required|string|max:255',
        'working_id' => 'required|string|unique:teachers',
        'email' => 'nullable|email|unique:teachers',
        'academic_rank_id' => 'required|exists:academic_ranks,id',
        'employment_status_id' => 'required|exists:employment_statuses,id',
    ]);

    $teacher = Teacher::create([
        'full_name' => $request->full_name,
        'working_id' => $request->working_id,
        'email' => $request->email ?? null,
        'active' => true,
    ]);

    // حفظ الرتبة الأكاديمية
    $teacher->ranks()->create([
        'academic_rank_id' => $request->academic_rank_id,
        'from_date' => now(),
        'to_date' => null,
    ]);

    // حفظ الوضع الوظيفي
    $teacher->employmentStatuses()->create([
        'employment_status_id' => $request->employment_status_id,
        'from_date' => now(),
        'to_date' => null,
    ]);

    return redirect()->back()->with('success', 'تم إضافة الأستاذ مع الرتبة والوضع الوظيفي بنجاح');
}


    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'working_id' => 'required|string|unique:teachers,working_id,'.$teacher->id,
            'email' => 'required|email|unique:teachers,email,'.$teacher->id,
            'active' => 'boolean',
        ]);

        $teacher->update($request->all());

        return redirect()->back()->with('success', 'تم تعديل الأستاذ بنجاح');
    }

public function destroy(Teacher $teacher)
{
    // حذف الرتب
    $teacher->teacherRanks()->delete();

    // حذف الوضعيات الوظيفية
    $teacher->teacherEmploymentStatuses()->delete();

    // حذف التعيينات التدريسية
    $teacher->teachingAssignments()->delete();

    // أخيرًا حذف الأستاذ نفسه
    $teacher->delete();

    return redirect()->back()->with('success', 'تم حذف الأستاذ وكل البيانات المرتبطة به بنجاح');
}

    public function toggleActive(Request $request, Teacher $teacher)
{
    $request->validate([
        'active' => 'required|boolean',
    ]);

    $teacher->update(['active' => $request->active]);

    return response()->json(['success' => true, 'active' => $teacher->active]);
}
public function promotionForm(Teacher $teacher)
{
    // جلب الرتبة والوضع الحالي للأستاذ
    $teacher->load([
        'teacherRanks' => fn($q) => $q->current()->with('academicRank'),
        'teacherEmploymentStatuses' => fn($q) => $q->current()->with('employmentStatus'),
    ]);

    // بيانات الفورم
    $academicRanks = AcademicRank::all();
    $employmentStatuses = EmploymentStatus::all();

    return view('teachers.promotion', compact(
        'teacher',
        'academicRanks',
        'employmentStatuses'
    ));
}



}
