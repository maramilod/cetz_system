<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseOffering;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Teacher;

class AttendanceController extends Controller
{
public function index(Request $request)
{
    // جلب جميع الفصول المفعّلة
    $activeSemesterIds = Semester::where('active', 1)->pluck('id')->toArray();

    if (empty($activeSemesterIds)) {
        return redirect()->back()->with('error', 'لا يوجد فصل دراسي مفعّل حالياً.');
    }

    // جلب المواد المطروحة في جميع الفصول المفعّلة
    $courseOfferingsQuery = CourseOffering::with([
        'course',
        'section',
        'teachingAssignments.teacher',
        'enrollments.student'
    ])->whereIn('semester_id', $activeSemesterIds);

    // فلترة حسب المادة
    if ($request->filled('course_offering_id')) {
        $courseOfferingsQuery->where('id', $request->course_offering_id);
    }

    $courseOfferings = $courseOfferingsQuery->get();

    // تحضير بيانات الحضور
    $attendanceData = $courseOfferings->map(function($co) {

    $teachers = $co->teachingAssignments->map(function($ta) {
    return [
        'name' => $ta->teacher->full_name ?? 'غير محدد',
        'role' => $ta->role ?? 'غير محدد',
    ];
});


        $students = $co->enrollments
            ->where('status', 'in_progress')
            ->map(function($e) {
                return [
                    'id'   => $e->student->student_number,
                    'name' => $e->student->full_name,
                ];
            });

        return [
    'course_name'  => $co->course->name,
    'section_name' => $co->section->name,
    'teachers' => $teachers,
    'semester_name'=> $co->semester->name,
    'students'     => $students,
    'id'           => $co->id
];

    });

    // خيارات الفلتر
    $allCourseOfferings = CourseOffering::whereIn('semester_id', $activeSemesterIds)
        ->with('course')
        ->get();

    return view('registration.attendance-form', compact(
        'attendanceData',
        'allCourseOfferings'
    ));
}


}
