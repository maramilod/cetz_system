<?php

namespace App\Http\Controllers;

use App\Models\{
    Department,
    Semester,
    Student,
    Enrollment,
    Course,
    CourseOffering,
    Grade,
    Teacher,
    User
};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsController extends Controller
{
    public function index(){

        return response()->json([
            'students'     => $this->studentsStats(),
            'departments'  => $this->departmentsAndSectionsStats(),
            'courses'      => $this->coursesStats(),
            'teachers'     => $this->teachersStats(),
            'system_users' => $this->usersStats(),
            'extra'        => $this->extraStats(),
            'top_cards'    => $this->topCards(),
            'activeSemester' => $this->activeSemesterStats(),

        ]);
    }

    // View → Blade
    public function view()
    {
        return view('dashboard.index');
    }
private function semesterPackagesCount(): int
{
    return DB::table('semesters')
        ->select('degree_type', 'start_date')
        ->groupBy('degree_type', 'start_date')
        ->get()
        ->count();
}


 private function topCards()
{
    return [
        'students' => Student::count(),
        'teachers' => Teacher::count(),
        'courses'  => Course::count(),
        'semester_packages' => $this->semesterPackagesCount(),
    ];
}


    /* ===================== الطلبة ===================== */

    private function studentsStats(): array
    {
        return [
            'total'        => Student::count(),
            'active'       => Student::where('current_status', 'active')->count(),
            'suspended'    => Student::where('current_status', 'suspended')->count(),
            'graduated'    => Student::where('current_status', 'graduated')->count(),
        ];
    }

    /* ===================== الأقسام + الشعب ===================== */

    private function departmentsAndSectionsStats(): array
    {
        return Department::with(['sections' => function ($q) {
            $q->withCount([
                'students as total_students',
                'students as active_students' => function ($q) {
                    $q->where('current_status', 'active');
                },
                'students as suspended_students' => function ($q) {
                    $q->where('current_status', 'suspended');
                },
                'students as graduated_students' => function ($q) {
                    $q->where('current_status', 'graduated');
                },
            ]);
        }])->withCount('students')->get()->map(function ($dept) {
            return [
                'department_id'   => $dept->id,
                'department_name' => $dept->name,
                'total_students'  => $dept->students_count,
                'sections'        => $dept->sections,
            ];
        })->toArray();
    }

    /* ===================== المواد + نسب النجاح ===================== */

    private function coursesStats(): array
    {
        return Course::withCount([
            'offerings as total_offerings',
        ])->get()->map(function ($course) {

            $enrollments = Enrollment::whereHas('courseOffering', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            });

            return [
                'course_id'   => $course->id,
                'course_name' => $course->name,

                'enrollments' => [
                    'total'     => $enrollments->count(),
                    'passed'    => (clone $enrollments)->where('status', 'passed')->count(),
                    'failed'    => (clone $enrollments)->where('status', 'failed')->count(),
                    'deprived'  => (clone $enrollments)->where('is_deprived', true)->count(),
                ],

                'success_rate' => $this->percentage(
                    (clone $enrollments)->where('status', 'passed')->count(),
                    $enrollments->count()
                ),
            ];
        })->toArray();
    }

    /* ===================== الأساتذة ===================== */

    private function teachersStats(): array
    {
        return [
            'total'        => Teacher::count(),
            'active'       => Teacher::where('active', true)->count(),
            'inactive'     => Teacher::where('active', false)->count(),
        ];
    }

    /* ===================== مستخدمي النظام ===================== */

    private function usersStats(): array
    {
        return [
            'total'        => User::count(),
            'active'       => User::where('is_active', true)->count(),
            'inactive'     => User::where('is_active', false)->count(),
        ];
    }

    /* ===================== إحصائيات إضافية ===================== */

private function extraStats(): array
{
    $droppedCourses = CourseOffering::with('course')
        ->where('status', 'dropped')
        ->get();

    return [
        'dropped_courses' => $droppedCourses->count(),
        'dropped_courses_list' => $droppedCourses->map(function($offering) {
            return [
                'course_id' => $offering->course_id,
                'course_name' => $offering->course->name ?? 'غير معروف',
            ];
        })->toArray(),

        'active_semesters'=> DB::table('semesters')->where('active', true)->count(),

        'gender_distribution' => [
            'male'   => Student::where('gender', 'ذكر')->count(),
            'female' => Student::where('gender', 'انثى')->count(),
        ],
        'nationality_distribution' => Student::select('nationality', DB::raw('count(*) as total'))
    ->groupBy('nationality')
    ->pluck('total', 'nationality'),

    ];
    
}


    /* ===================== Helper ===================== */

    private function percentage(int $part, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($part / $total) * 100, 2);
    }



  public function activeView()
    {
        // جلب جميع الفصول المفعّلة
        $activeSemesterIds = Semester::where('active', 1)->pluck('id')->toArray();

        // عد الأساتذة المرتبطين بالمقررات في هذه الفصول
        $teachersCount = 0;
        if (!empty($activeSemesterIds)) {
            $teachersCount = DB::table('teaching_assignments as ta')
                ->join('course_offerings as co', 'ta.course_offering_id', '=', 'co.id')
                ->whereIn('co.semester_id', $activeSemesterIds)
                ->distinct('ta.teacher_id')
                ->count('ta.teacher_id');
        }

           // ===== عد الطلبة الذين بدأوا الدراسة =====
          $students = DB::table('enrollments as e')
            ->join('course_offerings as co', 'e.course_offering_id', '=', 'co.id')
            ->join('students as s', 'e.student_id', '=', 's.id')
            ->whereIn('co.semester_id', $activeSemesterIds)
            ->where('e.status', 'in_progress')
            ->select('s.id as student_id', 's.full_name', 's.section_id', 's.department_id', 'co.course_id')
            ->distinct('s.id') // لمنع التكرار
            ->get();

        // ===== الطلاب لكل شعبة =====
        $studentsPerSection = $students->groupBy('section_id')->map(function($group, $sectionId){
            return [
                'section_id' => $sectionId,
                'students_count' => $group->count(),
            ];
        });

        return view('dashboard.active', compact(
            'teachersCount',
            'students',
          
            'studentsPerSection'
        ));
    }
private function activeSemesterStats(): array
{
  // جلب الفصل الفعّال
$activeSemester = Semester::where('active', 1)->first();

if (!$activeSemester) {
    return [
        'semester_type' => 'غير معروف',
        'semester_dates' => 'غير محدد',
        'students_count' => 0,
        'students_per_section' => [],
    ];
}

// الآن يمكننا الوصول إلى start_date و end_date
$semesterType = $activeSemester->term_type ?? 'غير معروف';
$semesterDates = $activeSemester->start_date && $activeSemester->end_date
    ? date('d/m/Y', strtotime($activeSemester->start_date)) . ' - ' . date('d/m/Y', strtotime($activeSemester->end_date))
    : 'غير محدد';

// باقي الكود لجلب الطلاب
$students = DB::table('enrollments as e')
    ->join('course_offerings as co', 'e.course_offering_id', '=', 'co.id')
    ->join('students as s', 'e.student_id', '=', 's.id')
    ->where('co.semester_id', $activeSemester->id)
    ->where('e.status', 'in_progress')
    ->select('s.id as student_id', 's.section_id')
    ->distinct('s.id')
    ->get();

// أسماء الشعب
$sectionIds = $students->pluck('section_id')->unique()->toArray();
$sections = DB::table('sections')
    ->whereIn('id', $sectionIds)
    ->pluck('name', 'id'); // [id => name]

// عدد الطلاب لكل شعبة
$students_per_section = $students->groupBy('section_id')->map(function($group, $sectionId) use ($sections) {
    return [
        'section_id' => $sectionId,
        'section_name' => $sections[$sectionId] ?? 'غير معروف',
        'students_count' => $group->count(),
    ];
})->values()->toArray();

return [
    'semester_type' => $semesterType,
    'semester_dates' => $semesterDates,
    'students_count' => $students->count(),
    'students_per_section' => $students_per_section,
];

}


    
}
