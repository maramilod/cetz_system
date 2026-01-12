<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Department;
use App\Models\Semester;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class MaterialDownloadController extends Controller
{
    public function index()
    {
        // جلب الطلاب مع القسم والشعبة
    $students = Student::with(['department', 'section'])
    ->get()
    ->map(function ($s) {
        $enrollments = Enrollment::where('student_id', $s->id)
            ->whereIn('status', ['in_progress', 'passed', 'failed'])
            ->with('courseOffering.course')
            ->get()
            ->map(function($e) {
                return [
                    'id' => $e->id,
                    'status' => $e->status,
                    'course' => [
                        'name' => $e->courseOffering?->course?->name,
                        'code' => $e->courseOffering?->course?->course_code,
                        'hours' => $e->courseOffering?->course?->hours,
                        'units' => $e->courseOffering?->course?->units,
                    ]
                ];
            });

        return [
            'id' => $s->id,
            'number' => $s->student_number ?? $s->manual_number,
            'name' => $s->full_name,
            'department' => $s->department?->name,
            'section_name' => $s->section?->name,
            'section_id' => $s->section?->id,
            'enrollments' => $enrollments->isNotEmpty() ? $enrollments : null,
            'current_status'=> $s->current_status,
        ];
    });



        // الأقسام النشطة
        $departments = Department::where('is_active', 1)->get();

    $now = Carbon::now();

// فقط السيمسترات الحالية
$semesters = Semester::where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now)
                     ->get()
                     ->map(function ($s) {
                         return [
                             'id'    => $s->id,
                             'label' => $s->name
                         ];
                     });

        $offerings = CourseOffering::with(['course', 'semester', 'section'])->get();

         // جلب السيمستر الحالي
    $currentSemester = Semester::where('start_date', '<=', $now)
        ->where('end_date', '>=', $now)
        ->first();

    if (!$currentSemester) {
        return view('materials.download', [
            'students' => collect(),
            'departments' => Department::where('is_active', 1)->get(),
            'semesters' => collect(),
            'materials' => collect(),
        ]);
    }

$materials = CourseOffering::with(['course', 'section', 'semester'])->get()->map(function($offering){
    return [
        'id'          => $offering->id,
        'section_id'  => $offering->section_id,
        'semester_id' => $offering->semester_id,
        'code'        => $offering->course->course_code,
        'name'        => $offering->course->name,
        'status'=> $offering->status, 
        'units'       => $offering->course->units,
        'hours'       => $offering->course->hours,
        'section_name'=> $offering->section?->name,
        'semester_name'=> $offering->semester?->name,
    ];
});


 return view('materials.download', [

    'students'    => $students,
    'departments' => $departments,
    'semesters'   => $semesters,
    'materials'   => $materials,
]);

    }





}
