<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Semester;

use App\Models\CourseOffering;

use Illuminate\Http\Request;

class StudentSpecializationController extends Controller
{
public function showSpecialization(Student $student)
{
    // تحميل بيانات الطالب مع القسم والشعبة
    $student->load('department', 'section');

    // جميع الأقسام الفعالة مع الشعب الفعالة
    $departments = Department::where('is_active', true)
        ->with(['sections' => fn($q) => $q->where('is_active', true)])
        ->get();
    $sections = Section::where('is_active', true)->get();

    // -----------------------------
    // 1️⃣ تاريخ الطالب: كل الانرولمنتات بشكل مستقل مع الحالة فقط
    // -----------------------------
    $enrollments = Enrollment::with([
        'courseOffering.course',
        'courseOffering.semester',
        'courseOffering.section',
    ])
    ->where('student_id', $student->id)
    ->orderBy('id', 'asc')
    ->get();

    $history = $enrollments->map(function($e) {
        return [
            'semester_name' => $e->courseOffering?->semester?->name ?? 'غير محدد',
            'section_name'  => $e->courseOffering?->section?->name ?? 'غير محدد',
            'course_name'   => $e->courseOffering?->course?->name ?? 'غير محدد',
            'course_code'   => $e->courseOffering?->course?->course_code ?? 'غير محدد',
            'status'        => $e->status, // فقط حقل الحالة
            'attempt'       => $e->attempt,
        ];
    });

    // -----------------------------
    // 2️⃣ مواد الشعبة الحالية للفصل الحالي بشكل مستقل
    // -----------------------------
 // جلب كل الفصول المفعلة
$currentSemesters = Semester::where('active', 1)->get();
$courseOfferings = collect();

if ($student->section && $currentSemesters->isNotEmpty()) {
    foreach ($currentSemesters as $semester) {
        $courseOfferings = $courseOfferings->merge(
            $student->section->courseOfferings()
                ->where('semester_id', $semester->id)
                ->with(['course', 'semester'])
                ->get()
        );
    }
}

// تشكيل بيانات المواد للشعبة الحالية
$courses = $courseOfferings->map(function ($co) {
    return [
        'course_name' => $co->course->name ?? 'غير محدد',
        'course_code' => $co->course->course_code ?? 'غير محدد',
        'units'       => $co->course->units ?? 0,
        'hours'       => $co->course->hours ?? 0,
        'semester_name' => $co->semester->name ?? 'غير محدد',
        'status'      => 'مقررة للشعبة',
    ];
});


    return view('students.specialization', compact(
        'student',
        'departments',
        'sections',
        'courses',
        'history' // أرسلنا التاريخ مع الحالة فقط
    ));
}

public function updateSection(Request $request, Student $student)
{
    $request->validate([
        'department_id' => 'required|exists:departments,id',
        'section_id'    => 'nullable|exists:sections,id',
    ]);

    // تحديث القسم والشعبة
    $student->update([
        'department_id' => $request->department_id,
        'section_id'    => $request->section_id,
    ]);

    // === معادلة المواد ===
    if ($student->section_id) {
        $newSectionId = $student->section_id;

        $newCourses = CourseOffering::where('section_id', $newSectionId)->get();

        $studentEnrollments = $student->enrollments()
            ->where('status', 'passed')
            ->get();

        foreach ($studentEnrollments as $enrollment) {
            $oldCourse = $enrollment->courseOffering;

            $matchingCourse = $newCourses->first(fn($co) => $co->course_id == $oldCourse->course_id);

            if ($matchingCourse) {
                $exists = Enrollment::where('student_id', $student->id)
                    ->where('course_offering_id', $matchingCourse->id)
                    ->exists();

                if (!$exists) {
                    Enrollment::create([
                        'student_id' => $student->id,
                        'course_offering_id' => $matchingCourse->id,
                        'status' => 'equivalent',
                        'attempt' => $enrollment->attempt,
                    ]);
                }
            }
        }
    }

    return response()->json([
        'message' => 'تم تحديث الشعبة ومعادلة المواد بنجاح',
        'department' => $student->department->name ?? '-',
        'section' => $student->section->name ?? '-',
    ]);
}


}
