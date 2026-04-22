<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Department;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\alert;

class GradesController extends Controller
{
    public function index()
    {
        // جلب كل الانرولمنتس الحالية (in_progress) مع الطالب والمادة
      $enrollments = Enrollment::with(['student', 'courseOffering.course'])
                         ->where('status', 'in_progress')
                          ->whereHas('student', function($query) {
                         $query->where('current_status', 'تم التجديد');
                     })
                         ->whereHas('courseOffering.semester', function($q) {
            $q->where('active', 1); // فقط السيمسترات المفعلة
        })
        ->get();

$grades = $enrollments->map(function($enrollment) {

      $grade = Grade::firstOrCreate(
            [
                'student_id'    => $enrollment->student_id,
                'course_id'     => $enrollment->courseOffering->course_id ?? null,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'theory_work'      => 0,
                'theory_midterm'   => 0,
                'theory_final'     => 0,
                'practical_work'   => 0,
                'practical_midterm'=> 0,
                'practical_final'  => 0,
                'total'            => 0,
                'student_type'     => 'مسجل',
            ]
        );

    return [
        'id' => $grade->id ?? null,
        'student_id' => $enrollment->student->id,
        'student_name' => $enrollment->student->full_name,
        'student_number' => $enrollment->student->student_number ?? $enrollment->student->manual_number,

        'course_id' => $enrollment->courseOffering->course_id ?? null,
        'course_name' => $enrollment->courseOffering->course->name ?? 'غير محدد',
        'enrollment_id' => $enrollment->id,
     'theory_work' => $grade->theory_work !== null ? $grade->theory_work : null,
        'theory_midterm' => $grade->theory_midterm !== null ? $grade->theory_midterm : null,
        'theory_final' => $grade->theory_final !== null ? $grade->theory_final : null,
        'practical_work' => $grade->practical_work !== null ? $grade->practical_work : null,
        'practical_midterm' => $grade->practical_midterm !== null ? $grade->practical_midterm : null,
        'practical_final' => $grade->practical_final !== null ? $grade->practical_final : null,
       
        'total' => $grade->total ?? 0,
        'student_type' => $grade->student_type ?? 'مسجل',
                    'has_practical' =>  $enrollment->courseOffering->course->has_practical,
                    'attempt' => $enrollment->attempt,

    ];
});

        // إرسال البيانات للـ Blade
        return view('study_exams.results', compact('grades'));
    }




    /**
     * حفظ أو تحديث درجة طالب مع الحساب التفصيلي
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'course_id'     => 'required|exists:courses,id',
            'enrollment_id' => 'required|exists:enrollments,id',

            'theory_work'      => 'nullable|numeric|min:0|max:100',
            'theory_midterm'   => 'nullable|numeric|min:0|max:100',
            'theory_final'     => 'nullable|numeric|min:0|max:100',
            'practical_work'   => 'nullable|numeric|min:0|max:100',
            'practical_midterm'=> 'nullable|numeric|min:0|max:100',
            'practical_final'  => 'nullable|numeric|min:0|max:100',

        ]);

        DB::beginTransaction();

        try {
            $total = 0;
$total += ($request->theory_work ?? 0);     
$total += ($request->theory_midterm ?? 0);   

$total += ($request->practical_work ?? 0);     
$total += ($request->practical_midterm ?? 0);  
$finalSum = ($request->theory_final ?? 0) + ($request->practical_final ?? 0);
if ($finalSum >= 30) {
    $total += ($request->theory_final ?? 0);
    $total += ($request->practical_final ?? 0);
}

        
            $grade = Grade::updateOrCreate(
                [
                    'student_id'    => $request->student_id,
                    'course_id'     => $request->course_id,
                    'enrollment_id' => $request->enrollment_id,
                ],
                [
                    'theory_work'      => $request->theory_work,
                    'theory_midterm'   => $request->theory_midterm,
                    'theory_final'     => $request->theory_final,
                    'practical_work'   => $request->practical_work,
                    'practical_midterm'=> $request->practical_midterm,
                    'practical_final'  => $request->practical_final,
                    'total'            => round($total,2),
                            ]
            );

            // الحالة الافتراضية

    $status = $total >= 50 ? 'passed' : 'failed';


Enrollment::where('id', $request->enrollment_id)
    ->update([
        'status' => $status
    ]);

            DB::commit();

            return response()->json([
                'message' => 'تم حفظ الدرجة بنجاح.',
                'grade' => $grade
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'حدث خطأ أثناء الحفظ.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
public function appeals(Request $request)
{
    $activeSemesterIds = Semester::where('active', 1)->pluck('id');

    $query = Enrollment::query()
        ->whereHas('courseOffering', function($q) use ($activeSemesterIds) {
            $q->whereIn('semester_id', $activeSemesterIds);
        })
        ->whereIn('status', ['passed', 'failed'])
        ->with([
            'student',
            'courseOffering.course',
            'grade' // 👈 حل مشكلة N+1
        ]);

    // 🔍 فلترة اختيارية (تسرع الأداء)
    if ($request->student_name) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->student_name . '%');
        });
    }

    // 🚀 Pagination بدل get()
    $enrollments = $query->paginate(50);

    // 🔄 map بطريقة صحيحة مع pagination
    $grades = collect($enrollments->items())->map(function ($enrollment) {

        $grade = $enrollment->grade;

        return [
            'id' => $grade->id ?? null,

            'student_id' => $enrollment->student->id,
            'student_name' => $enrollment->student->full_name,
            'student_number' => $enrollment->student->student_number ?? $enrollment->student->manual_number,

            'course_id' => $enrollment->courseOffering->course_id,
            'course_name' => $enrollment->courseOffering->course->name,

            'status'  => $enrollment->status,
            'enrollment_id' => $enrollment->id,

            'theory_work' => $grade->theory_work ?? null,
            'theory_midterm' => $grade->theory_midterm ?? null,
            'theory_final' => $grade->theory_final ?? null,

            'practical_work' => $grade->practical_work ?? null,
            'practical_midterm' => $grade->practical_midterm ?? null,
            'practical_final' => $grade->practical_final ?? null,

            'total' => $grade->total ?? 0,
            'attempt' => $enrollment->attempt,

            'has_practical' => $enrollment->courseOffering->course->has_practical,
        ];
    });

    return view('grades.appeals', [
        'grades' => $grades,
        'pagination' => $enrollments // 👈 نحتاجها للصفحات
    ]);
}

public function finalResults(Request $request)
{
    $filters = [
        'term_type' => trim((string) $request->query('term_type', '')),
        'year' => trim((string) $request->query('year', '')),
        'department_id' => trim((string) $request->query('department_id', '')),
        'semester_number' => trim((string) $request->query('semester_number', '')),
    ];

    $meta = $this->buildFinalResultsData($filters);

    return view('study_exams.final-results', $meta);
}

public function finalResultsPrint(Request $request)
{
    $filters = [
        'term_type' => trim((string) $request->query('term_type', '')),
        'year' => trim((string) $request->query('year', '')),
        'department_id' => trim((string) $request->query('department_id', '')),
        'semester_number' => trim((string) $request->query('semester_number', '')),
    ];

    $meta = $this->buildFinalResultsData($filters);

    return view('study_exams.final-results-print', $meta);
}

private function buildFinalResultsData(array $filters): array
{
    $departments = Department::active()->orderBy('name')->get(['id', 'name', 'is_general']);
    $semesterCatalog = Semester::query()
        ->whereNotNull('start_date')
        ->orderByDesc('start_date')
        ->get(['id', 'term_type', 'semester_number', 'start_date']);

    $termTypes = $semesterCatalog
        ->pluck('term_type')
        ->filter()
        ->unique()
        ->values();

    $years = $semesterCatalog
        ->map(fn ($semester) => $semester->start_date ? date('Y', strtotime($semester->start_date)) : null)
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    $semesterNumbersAll = $semesterCatalog
        ->where('semester_number', '!=', 99)
        ->pluck('semester_number')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $semesterNumbersGeneral = $semesterNumbersAll
        ->filter(fn ($number) => (int) $number === 1)
        ->values();

    $semesterNumbersNonGeneral = $semesterNumbersAll
        ->filter(fn ($number) => (int) $number >= 2 && (int) $number <= 8)
        ->values();

    $selectedDepartment = $departments->firstWhere('id', (int) $filters['department_id']);
    $isGeneralDepartmentSelected = $selectedDepartment && (int) $selectedDepartment->is_general === 1;

    if ($isGeneralDepartmentSelected) {
        // القسم العام لازم يكون الفصل 1 فقط
        $filters['semester_number'] = '1';
    } elseif ($filters['department_id'] !== '') {
        if ($filters['semester_number'] !== '' && (int) $filters['semester_number'] === 1) {
            $filters['semester_number'] = '';
        }
    }

    $semesterNumbers = $isGeneralDepartmentSelected
        ? $semesterNumbersGeneral
        : ($filters['department_id'] !== '' ? $semesterNumbersNonGeneral : $semesterNumbersAll);

    $hasRequiredFilters = $filters['term_type'] !== ''
        && $filters['year'] !== ''
        && $filters['department_id'] !== ''
        && $filters['semester_number'] !== '';

    if ($hasRequiredFilters) {
        $selectedSemesterIds = Semester::query()
            ->where('term_type', $filters['term_type'])
            ->whereYear('start_date', $filters['year'])
            ->where('semester_number', $filters['semester_number'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $selectedSemesters = Semester::query()
            ->whereIn('id', $selectedSemesterIds)
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'semester_number']);

        $selectedSemester = $selectedSemesters->first();

        $semesterOfferings = \App\Models\CourseOffering::query()
            ->with([
                'course:id,name,course_code',
                'section:id,department_id',
                'semester:id,term_type,semester_number,start_date,end_date',
            ])
            ->whereHas('section', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            })
            ->whereHas('semester', function ($q) use ($filters) {
                $q->where('term_type', $filters['term_type'])
                    ->whereYear('start_date', $filters['year'])
                    ->where('semester_number', '!=', 99);
            })
            ->get();

        $currentTermAllEnrollments = Enrollment::query()
            ->with([
                'student:id,full_name,student_number,manual_number',
                'grade:id,enrollment_id,total',
                'courseOffering:id,course_id,section_id,semester_id',
                'courseOffering.course:id,name,course_code',
                'courseOffering.section:id,department_id',
                'courseOffering.semester:id,term_type,semester_number,start_date,end_date',
            ])
            ->whereHas('courseOffering.section', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            })
            ->whereHas('courseOffering.semester', function ($q) use ($filters) {
                $q->where('term_type', $filters['term_type'])
                    ->whereYear('start_date', $filters['year'])
                    ->where('semester_number', '!=', 99);
            })
            ->where('status', '!=', 'withdrawn')
            ->get();

        $studentSemesterMap = $currentTermAllEnrollments
            ->groupBy('student_id')
            ->map(function ($enrollments) {
                return $enrollments
                    ->map(fn ($enrollment) => (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0))
                    ->max();
            });

        $stageOfferings = $semesterOfferings
            ->filter(function ($offering) use ($filters) {
                return (int) ($offering->semester?->semester_number ?? 0) === (int) $filters['semester_number'];
            })
            ->sortBy(fn ($offering) => $offering->course?->course_code ?? '')
            ->values();

        $courses = $stageOfferings
            ->map(function ($offering) {
                $course = $offering->course;

                return $course ? [
                    'id' => $course->id,
                    'name' => $course->name,
                    'code' => $course->course_code,
                ] : null;
            })
            ->filter()
            ->unique('id')
            ->values();

        $selectedStudentIds = $studentSemesterMap
            ->filter(fn ($semesterNumber) => (int) $semesterNumber === (int) $filters['semester_number'])
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $selectedStudentsTermEnrollments = $currentTermAllEnrollments
            ->whereIn('student_id', $selectedStudentIds->all())
            ->values();

        $selectedSemesterEnrollments = $selectedStudentsTermEnrollments
            ->filter(function ($enrollment) use ($filters) {
                return (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0) === (int) $filters['semester_number'];
            })
            ->values();

        $previousEnrollments = Enrollment::query()
            ->with([
                'grade:id,enrollment_id,total',
                'courseOffering:id,course_id,section_id,semester_id',
                'courseOffering.course:id,name,course_code',
                'courseOffering.section:id,department_id',
                'courseOffering.semester:id,start_date,semester_number',
            ])
            ->whereIn('student_id', $selectedStudentIds)
            ->whereHas('courseOffering.section', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            })
            ->whereHas('courseOffering.semester', function ($q) use ($selectedSemester) {
                if (!$selectedSemester?->start_date) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->where(function ($qq) use ($selectedSemester) {
                    $qq->whereDate('start_date', '<', $selectedSemester->start_date)
                        ->orWhere(function ($qqq) use ($selectedSemester) {
                            $qqq->whereDate('start_date', '=', $selectedSemester->start_date)
                                ->where('semester_number', '<', (int) $selectedSemester->semester_number);
                        });
                });
            })
            ->where('status', '!=', 'withdrawn')
            ->get();

        $rows = $selectedStudentIds
            ->map(function ($studentId) use ($selectedSemesterEnrollments, $selectedStudentsTermEnrollments, $previousEnrollments, $courses) {
                $studentEnrollments = $selectedSemesterEnrollments
                    ->where('student_id', (int) $studentId)
                    ->values();

                $student = $studentEnrollments->first()?->student
                    ?? $selectedStudentsTermEnrollments->firstWhere('student_id', (int) $studentId)?->student;

                $enrollmentsByCourse = $studentEnrollments
                    ->sortByDesc('updated_at')
                    ->groupBy(function ($enrollment) {
                        return (int) ($enrollment->courseOffering?->course_id ?? 0);
                    })
                    ->map(fn ($group) => $group->first());

                $totals = $studentEnrollments
                    ->filter(fn ($enrollment) => $enrollment->grade?->total !== null)
                    ->map(fn ($enrollment) => (float) $enrollment->grade->total)
                    ->values();

                $average = $totals->isNotEmpty() ? round($totals->avg(), 2) : 0;

                $courseGrades = $courses->map(function ($course) use ($enrollmentsByCourse) {
                    $courseId = (int) $course['id'];
                    $selectedEnrollment = $enrollmentsByCourse->get($courseId);

                    if (!$selectedEnrollment || !$selectedEnrollment->grade || $selectedEnrollment->grade->total === null) {
                        return [
                            'course_id' => $courseId,
                            'grade' => 'م',
                        ];
                    }

                    return [
                        'course_id' => $courseId,
                        'grade' => $this->formatResultGrade($selectedEnrollment),
                    ];
                });

                $carryCourses = $previousEnrollments
                    ->where('student_id', (int) $studentId)
                    ->sortByDesc(function ($enrollment) {
                        return sprintf(
                            '%s-%02d-%010d',
                            (string) ($enrollment->courseOffering?->semester?->start_date ?? '0000-00-00'),
                            (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0),
                            (int) $enrollment->attempt
                        );
                    })
                    ->groupBy(function ($enrollment) {
                        return (int) ($enrollment->courseOffering?->course_id ?? 0);
                    })
                    ->map(function ($group, $courseId) use ($selectedStudentsTermEnrollments) {
                        $latestPastEnrollment = $group->first();
                        $currentTermSameCourse = $selectedStudentsTermEnrollments
                            ->where('student_id', (int) $latestPastEnrollment->student_id)
                            ->filter(function ($enrollment) use ($courseId) {
                                return (int) ($enrollment->courseOffering?->course_id ?? 0) === (int) $courseId;
                            })
                            ->sortByDesc('updated_at')
                            ->first();

                        $latestStatus = $currentTermSameCourse?->status ?? $latestPastEnrollment->status;
                        if ($latestStatus !== 'failed') {
                            return null;
                        }

                        return [
                            'course_name' => $latestPastEnrollment->courseOffering?->course?->name ?? '-',
                            'grade' => $currentTermSameCourse && $currentTermSameCourse->grade?->total !== null
                                ? $this->formatResultGrade($currentTermSameCourse)
                                : $this->formatResultGrade($latestPastEnrollment),
                        ];
                    })
                    ->filter()
                    ->sortBy('course_name')
                    ->values();

                return [
                    'student_name' => $student?->full_name ?? '-',
                    'student_number' => $this->normalizeStudentNumber(
                        $student?->student_number ?: ($student?->manual_number ?? '-')
                    ),
                    'average' => $average,
                    'classification' => $this->gradeClassification($average),
                    'course_grades' => $courseGrades,
                    'carry_courses' => $carryCourses,
                ];
            })
            ->sortBy(function ($row) {
                return mb_strtolower(trim((string) ($row['student_name'] ?? '')));
            })
            ->values();
    } else {
        $courses = collect();
        $rows = collect();
    }

    $selectedDepartmentName = $departments->firstWhere('id', (int) $filters['department_id'])?->name ?? 'كل الأقسام';
    $selectedTermType = $filters['term_type'] !== '' ? $filters['term_type'] : 'كل الفصول';
    $selectedYear = $filters['year'] !== '' ? $filters['year'] : 'كل السنوات';
    $selectedSemesterNumber = $filters['semester_number'] !== '' ? $filters['semester_number'] : 'الكل';

    return [
        'filters' => $filters,
        'hasRequiredFilters' => $hasRequiredFilters,
        'departments' => $departments,
        'termTypes' => $termTypes,
        'years' => $years,
        'semesterNumbers' => $semesterNumbers,
        'semesterNumbersAll' => $semesterNumbersAll,
        'semesterNumbersGeneral' => $semesterNumbersGeneral,
        'semesterNumbersNonGeneral' => $semesterNumbersNonGeneral,
        'courses' => $courses,
        'rows' => $rows,
        'selectedDepartmentName' => $selectedDepartmentName,
        'selectedTermType' => $selectedTermType,
        'selectedYear' => $selectedYear,
        'selectedSemesterNumber' => $selectedSemesterNumber,
    ];
}

private function gradeClassification(float $average): string
{
    if ($average >= 85) {
        return 'ممتاز';
    }
    if ($average >= 75) {
        return 'جيد جدا';
    }
    if ($average >= 65) {
        return 'جيد';
    }
    if ($average >= 50) {
        return 'مقبول';
    }

    return 'ضعيف';
}

private function normalizeStudentNumber(?string $raw): string
{
    $raw = (string) ($raw ?? '');
    $digits = preg_replace('/\D+/', '', $raw);

    return $digits !== '' ? $digits : $raw;
}

private function formatResultGrade(Enrollment $enrollment): string
{
    $total = $enrollment->grade?->total;
    if ($total === null) {
        return '-';
    }

    return rtrim(rtrim(number_format((float) $total, 2, '.', ''), '0'), '.');
}

}  