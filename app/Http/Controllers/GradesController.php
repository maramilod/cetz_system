<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Department;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Student;
use App\Models\CourseOffering;
use App\Models\Teacher;
use function Laravel\Prompts\alert;

class GradesController extends Controller
{
 public function index(Request $request)
{
    $searchStudent = $request->input('student_name');
    $searchCourse = $request->input('course_name');

    // 1. بناء الاستعلام الأساسي مع الفلاتر
    $query = Enrollment::with(['student', 'courseOffering.course'])
        ->where('status', 'in_progress')
        ->whereHas('student', function($q) use ($searchStudent) {
            $q->where('current_status', 'تم التجديد');
            if ($searchStudent) {
                $q->where('full_name', 'like', '%' . $searchStudent . '%');
            }
        })
        ->whereHas('courseOffering.semester', function($q) {
            $q->where('active', 1);
        });

    if ($searchCourse) {
        $query->whereHas('courseOffering.course', function($q) use ($searchCourse) {
            $q->where('name', 'like', '%' . $searchCourse . '%');
        });
    }

    // 2. استخدام paginate مع الحفاظ على مدخلات البحث عند التنقل بين الصفحات
    $enrollmentsPaginator = $query->paginate(50)->appends($request->query());

    // 3. معالجة السجلات الحالية في الصفحة الظاهرة فقط وإنشاء الدرجات المفقودة إن وجدت
    $enrollmentIds = $enrollmentsPaginator->getCollection()->pluck('id');

    $existingGrades = Grade::whereIn('enrollment_id', $enrollmentIds)
        ->get()
        ->keyBy('enrollment_id');

    $gradesDataToInsert = [];
    $enrollmentsPaginator->getCollection()->each(function($enrollment) use ($existingGrades, &$gradesDataToInsert) {
        if (!isset($existingGrades[$enrollment->id])) {
            $gradesDataToInsert[] = [
                'student_id'    => $enrollment->student_id,
                'course_id'     => $enrollment->courseOffering->course_id ?? null,
                'enrollment_id' => $enrollment->id,
                'theory_work'   => 0,
                'theory_final'  => 0,
                'total'         => 0,
                'student_type'  => 'مسجل',
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
    });

    if (!empty($gradesDataToInsert)) {
        Grade::insert($gradesDataToInsert);
        $existingGrades = Grade::whereIn('enrollment_id', $enrollmentIds)
            ->get()
            ->keyBy('enrollment_id');
    }

    // 4. تحويل التسجيلات إلى مصفوفة لواجهة Alpine.js
    $gradesArray = $enrollmentsPaginator->getCollection()->map(function($enrollment) use ($existingGrades) {
        $grade = $existingGrades->get($enrollment->id);

        return [
            'id'             => $grade->id ?? null,
            'student_id'     => $enrollment->student->id ?? null, 
            'student_name'   => $enrollment->student->full_name ?? "غير محدد",
            'student_number' => $enrollment->student->manual_number ?? $enrollment->student->student_number,
            'course_id'      => $enrollment->courseOffering->course_id ?? null,
            'course_name'    => $enrollment->courseOffering->course->name ?? 'غير محدد',
            'enrollment_id'  => $enrollment->id,
            'theory_work'    => $grade->theory_work !== null ? $grade->theory_work : null,
            'theory_final'   => $grade->theory_final !== null ? $grade->theory_final : null,
            'total'          => $grade->total ?? 0,
            'student_type'   => $grade->student_type ?? 'مسجل',
            'has_practical'  => $enrollment->courseOffering->course->has_practical,
            'attempt'        => $enrollment->attempt,
        ];
    })->values()->all();

    return view('study_exams.results', [
        'grades' => $gradesArray,
        'gradesPaginator' => $enrollmentsPaginator
    ]);
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
        'theory_work'   => 'nullable|numeric|min:0|max:100',
        'theory_final'  => 'nullable|numeric|min:0|max:100',
    ]);

    // 1. حساب المجموع قبل الدخول في الترانزاكشن للتحقق
    $work = $request->theory_work ?? 0;
    $final = $request->theory_final ?? 0;
    
if (($work + $final) > 100) {
    return response()->json([
        'message' => 'خطأ في إدخال الدرجات.',
        'error'   => 'لا يمكن أن يتجاوز مجموع الأعمال والنهائي 100 درجة.'
    ], 422);
}

    DB::beginTransaction();

    try {
        // 1. البحث عن السجل الموجود أولاً للتحقق من الدرجات القديمة
        $grade = Grade::where([
            'student_id'    => $request->student_id,
            'course_id'     => $request->course_id,
            'enrollment_id' => $request->enrollment_id,
        ])->first();

        // 2. منطق الحفظ في خانات الـ prev (إذا لم تكن محفوفة مسبقاً)
        $previousData = [];
        if ($grade) {
            // نحفظ الدرجة القديمة في الـ previous فقط إذا كانت الـ previous فارغة
            // هذا يضمن الاحتفاظ بأول درجة أصلية للطالب
            if (is_null($grade->previous_theory_work) && is_null($grade->previous_theory_final)) {
                $previousData['previous_theory_work'] = $grade->theory_work;
                $previousData['previous_theory_final'] = $grade->theory_final;
            }
        }

        // 3. حساب المجموع الجديد
        $total = 0;
        $total += ($request->theory_work ?? 0); 
        $finalSum = ($request->theory_final ?? 0);
        
        if ($finalSum >= 30) {
            $total += $finalSum;
        }

        // 4. تحديث السجل الحالي أو إنشاء سجل جديد
        $grade = Grade::updateOrCreate(
            [
                'student_id'    => $request->student_id,
                'course_id'     => $request->course_id,
                'enrollment_id' => $request->enrollment_id,
            ],
            array_merge([
                'theory_work'  => $request->theory_work,
                'theory_final' => $request->theory_final,
                'total'        => round($total, 2),
            ], $previousData) // دمج بيانات الـ prev إذا وُجدت
        );

        // 5. تحديث الحالة (Passed/Failed)
        $status = $total >= 50 ? 'passed' : 'failed';

        // منطق مشروع التخرج
        $course = Course::find($request->course_id);
        $isGraduationProject = $course && (
            str_contains($course->name, 'مشروع تخرج') ||
            str_contains($course->name, 'مشروع التخرج')
        );

        if ($isGraduationProject && $status === 'passed') {
            Student::where('id', $request->student_id)->update(['current_status' => 'متخرج']);
        }

        Enrollment::where('id', $request->enrollment_id)->update(['status' => $status]);

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
            'grade' 
        ]);

    // 🔍 الفلترة
    if ($request->student_name) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->student_name . '%');
        });
    }

    if ($request->course_name) {
        $query->whereHas('courseOffering.course', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->course_name . '%');
        });
    }

    if ($request->department_id) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('department_id', $request->department_id);
        });
    }

    // 🚀 Pagination
    $enrollments = $query->paginate(50);

    // 🔄 تحضير البيانات مع الحقول الجديدة (السابقة والحالية)
    $grades = collect($enrollments->items())->map(function ($enrollment) {
        $grade = $enrollment->grade;

        return [
            'id' => $grade->id ?? null,
            'student_id' => $enrollment->student->id ?? null,
            'student_name' => $enrollment->student->full_name ?? null,
            'student_number' => $enrollment->student->manual_number ?? $enrollment->student->student_number ?? null,
            'course_id' => $enrollment->courseOffering->course_id,
            'course_name' => $enrollment->courseOffering->course->name,
            'status'  => $enrollment->status,
            'enrollment_id' => $enrollment->id,

            'prev_theory_work' => $grade->previous_theory_work ?? $grade->theory_work ?? 0,
            'prev_theory_final' => $grade->previous_theory_final ?? $grade->theory_final ?? 0,

            'theory_work' => $grade->theory_work ?? 0,
            'theory_final' => $grade->theory_final ?? 0,
            
           

            'total' => $grade->total ?? 0,
            'attempt' => $enrollment->attempt,
            'has_practical' => $enrollment->courseOffering->course->has_practical,
        ];
    });

    // جلب الأقسام لعرضها في القائمة المنسدلة
    $departments = \App\Models\Department::all(); 

    return view('grades.appeals', [
        'grades' => $grades,
        'pagination' => $enrollments,
        'departments' => $departments
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

                $currentSemesterCourseIds = $courses
                    ->pluck('id')
                    ->map(fn ($courseId) => (int) $courseId)
                    ->values();

                $totals = $studentEnrollments
                    ->filter(fn ($enrollment) => $enrollment->grade?->total !== null)
                    ->map(fn ($enrollment) => (float) $enrollment->grade->total)
                    ->values();

                $average = $totals->isNotEmpty() ? round($totals->avg(), 2) : 0;

                $courseGrades = $courses->map(function ($course) use ($enrollmentsByCourse, $previousEnrollments, $studentId) {
                    $courseId = (int) $course['id'];
                    $selectedEnrollment = $enrollmentsByCourse->get($courseId);

                    if ($selectedEnrollment && $selectedEnrollment->grade && $selectedEnrollment->grade->total !== null) {
                        return [
                            'course_id' => $courseId,
                            'grade' => $this->formatResultGrade($selectedEnrollment),
                        ];
                    }

                    $wasPreviouslyPassed = $previousEnrollments
                        ->where('student_id', (int) $studentId)
                        ->contains(function ($enrollment) use ($courseId) {
                            return (int) ($enrollment->courseOffering?->course_id ?? 0) === (int) $courseId
                                && $enrollment->status === 'passed';
                        });

                    if ($wasPreviouslyPassed) {
                        return [
                            'course_id' => $courseId,
                            'grade' => 'م',
                        ];
                    }

                    if ($selectedEnrollment) {
                        return [
                            'course_id' => $courseId,
                            'grade' => '-',
                        ];
                    }

                    return [
                        'course_id' => $courseId,
                        'grade' => 'غ م',
                    ];
                });

                $carryCourses = $previousEnrollments
                    ->where('student_id', (int) $studentId)
                    ->groupBy(function ($enrollment) {
                        return (int) ($enrollment->courseOffering?->course_id ?? 0);
                    })
                    ->map(function ($group, $courseId) use ($selectedStudentsTermEnrollments, $currentSemesterCourseIds) {
                        if ($currentSemesterCourseIds->contains((int) $courseId)) {
                            return null;
                        }

                        $latestPastEnrollment = $group
                            ->sortByDesc(function ($enrollment) {
                                return sprintf(
                                    '%s-%02d-%010d-%010d',
                                    (string) ($enrollment->courseOffering?->semester?->start_date ?? '0000-00-00'),
                                    (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0),
                                    (int) $enrollment->attempt,
                                    (int) $enrollment->id
                                );
                            })
                            ->first();

                        $currentTermSameCourse = $selectedStudentsTermEnrollments
                            ->where('student_id', (int) $latestPastEnrollment->student_id)
                            ->filter(function ($enrollment) use ($courseId) {
                                return (int) ($enrollment->courseOffering?->course_id ?? 0) === (int) $courseId;
                            })
                            ->sortByDesc(function ($enrollment) {
                                return sprintf(
                                    '%s-%02d-%010d-%010d',
                                    (string) ($enrollment->courseOffering?->semester?->start_date ?? '0000-00-00'),
                                    (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0),
                                    (int) $enrollment->attempt,
                                    (int) $enrollment->id
                                );
                            })
                            ->first();

                        if ($currentTermSameCourse) {
                            return [
                                'course_name' => $currentTermSameCourse->courseOffering?->course?->name ?? '-',
                                'grade' => $currentTermSameCourse->grade?->total !== null
                                    ? $this->formatResultGrade($currentTermSameCourse)
                                    : $this->formatResultGrade($latestPastEnrollment),
                            ];
                        }

                        if (!$latestPastEnrollment || $latestPastEnrollment->status !== 'failed') {
                            return null;
                        }

                        return [
                            'course_name' => $latestPastEnrollment->courseOffering?->course?->name ?? '-',
                            'grade' => $this->formatResultGrade($latestPastEnrollment),
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

private function buildExamSheetsData(Request $request): array
{
    $filters = [
        'teacher_id' => trim((string) $request->query('teacher_id', '')),
        'course_id' => trim((string) $request->query('course_id', '')),
        'department_id' => trim((string) $request->query('department_id', '')),
    ];

    $activeSemesters = Semester::query()
        ->where('active', 1)
        ->orderByDesc('start_date')
        ->orderBy('semester_number')
        ->get(['id', 'name', 'semester_number', 'term_type', 'start_date']);

    $activeSemesterIds = $activeSemesters->pluck('id');

    $departments = Department::active()
        ->orderBy('name')
        ->get(['id', 'name', 'is_general']);

    $teachers = Teacher::query()
        ->whereHas('teachingAssignments.courseOffering', function ($q) use ($activeSemesterIds, $filters) {
            $q->whereIn('semester_id', $activeSemesterIds);
            if ($filters['department_id'] !== '') {
                $q->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('department_id', $filters['department_id']));
            }
        })
        ->orderBy('full_name')
        ->get(['id', 'full_name']);

    $courses = Course::query()
        ->whereHas('offerings', function ($q) use ($activeSemesterIds, $filters) {
            $q->whereIn('semester_id', $activeSemesterIds);
            if ($filters['department_id'] !== '') {
                $q->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('department_id', $filters['department_id']));
            }
        })
        ->orderBy('name')
        ->get(['id', 'name', 'course_code', 'has_practical']);

    $offeringsQuery = CourseOffering::query()
        ->with([
            'course:id,name,course_code,has_practical',
            'semester:id,name,semester_number,term_type,start_date,active',
            'section:id,name,department_id',
            'section.department:id,name',
            'teachingAssignments.teacher:id,full_name',
            'enrollments' => function ($q) {
                $q->where('status', 'in_progress')
                    ->with('student:id,full_name,student_number,manual_number,current_status');
            },
        ])
        ->whereIn('semester_id', $activeSemesterIds);

    if ($filters['department_id'] !== '') {
        $offeringsQuery->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('department_id', $filters['department_id']));
    }

    if ($filters['teacher_id'] !== '') {
        $offeringsQuery->whereHas('teachingAssignments', function ($q) use ($filters) {
            $q->where('teacher_id', $filters['teacher_id']);
        });
    }

    if ($filters['course_id'] !== '') {
        $offeringsQuery->where('course_id', $filters['course_id']);
    }

    $offerings = $offeringsQuery
        ->get()
        ->sortBy(fn ($offering) => implode('|', [
            (string) ($offering->course?->name ?? ''),
            (string) ($offering->section?->name ?? ''),
        ]))
        ->values();

    $courseIds = $offerings
        ->pluck('course_id')
        ->filter()
        ->unique()
        ->values();

    $currentEnrollments = Enrollment::query()
        ->with([
            'student:id,full_name,student_number,manual_number,current_status',
            'courseOffering:id,course_id,section_id,semester_id',
        ])
        ->where('status', '!=', 'withdrawn')
        ->whereHas('courseOffering', function ($q) use ($courseIds, $activeSemesterIds, $filters) {
            $q->whereIn('course_id', $courseIds)
                ->whereIn('semester_id', $activeSemesterIds);

            if ($filters['department_id'] !== '') {
                $q->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('department_id', $filters['department_id']));
            }
        })
        ->get();

    $studentIds = $currentEnrollments
        ->pluck('student_id')
        ->filter()
        ->unique()
        ->values();

    $previousFailedEnrollments = Enrollment::query()
        ->with('courseOffering:id,course_id')
        ->whereIn('student_id', $studentIds)
        ->where('status', 'failed')
        ->whereHas('courseOffering', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })
        ->get()
        ->groupBy(function ($enrollment) {
            return (int) $enrollment->student_id
                . '-'
                . (int) ($enrollment->courseOffering?->course_id ?? 0);
        });

    $offeringsByCourseSemester = $offerings
        ->groupBy(fn ($offering) => (int) $offering->course_id . '-' . (int) $offering->semester_id);

    $sheets = $currentEnrollments
        ->groupBy(function ($enrollment) {
            return (int) ($enrollment->courseOffering?->course_id ?? 0)
                . '-'
                . (int) ($enrollment->courseOffering?->semester_id ?? 0);
        })
        ->map(function ($enrollments, $groupKey) use ($offeringsByCourseSemester, $previousFailedEnrollments) {
        $group = $offeringsByCourseSemester->get($groupKey, collect());
        $offering = $group->first() ?? $enrollments->first()?->courseOffering;

        $teacherNames = $group
            ->flatMap(fn ($item) => $item->teachingAssignments->pluck('teacher.full_name'))
            ->filter()
            ->unique()
            ->values();

        $sectionNames = $group
            ->pluck('section.name')
            ->filter()
            ->unique()
            ->values();

        $departmentNames = $group
            ->pluck('section.department.name')
            ->filter()
            ->unique()
            ->values();

        $rows = $enrollments
            ->sortBy(fn ($enrollment) => mb_strtolower((string) ($enrollment->student?->full_name ?? '')))
            ->groupBy('student_id')
            ->map(fn ($studentEnrollments) => $studentEnrollments->sortByDesc('id')->first())
            ->values()
            ->map(function ($enrollment) use ($offering, $previousFailedEnrollments) {
                $student = $enrollment->student;
                $studentNumber = $student?->student_number ?: ($student?->manual_number ?? '');
                $carryKey = (int) $enrollment->student_id . '-' . (int) ($offering?->course_id ?? 0);
                $hasOlderFailedAttempt = $previousFailedEnrollments
                    ->get($carryKey, collect())
                    ->contains(fn ($failedEnrollment) => (int) $failedEnrollment->id < (int) $enrollment->id);

                return [
                    'student_name' => $student?->full_name ?? '-',
                    'student_number' => $this->normalizeStudentNumber($studentNumber),
                    'attempt' => (int) $enrollment->attempt,
                    'is_carry' => (int) $enrollment->attempt > 1 || $hasOlderFailedAttempt,
                    'status' => $enrollment->status,
                ];
            });

        $semester = $offering->semester;
        $semesterYear = $semester?->start_date ? date('Y', strtotime($semester->start_date)) : '';
        $semesterLabel = trim(implode(' - ', array_filter([
            $semester?->name,
            $semester?->term_type,
            $semester?->semester_number ? 'الفصل ' . $semester->semester_number : null,
            $semesterYear,
        ])));

        return [
            'offering_id' => $offering->id,
            'course_name' => $offering->course?->name ?? '-',
            'course_code' => $offering->course?->course_code ?? '',
            'has_practical' => (bool) ($offering->course?->has_practical),
            'semester_label' => $semesterLabel !== '' ? $semesterLabel : '-',
            'section_name' => $sectionNames->isNotEmpty() ? $sectionNames->implode('، ') : '-',
            'department_name' => $departmentNames->isNotEmpty() ? $departmentNames->implode('، ') : '-',
            'teacher_names' => $teacherNames,
            'teacher_label' => $teacherNames->isNotEmpty() ? $teacherNames->implode('، ') : '-',
            'rows' => $rows,
        ];
    })
        ->filter(fn ($sheet) => $sheet['rows']->isNotEmpty())
        ->values();

    return [
        'filters' => $filters,
        'departments' => $departments,
        'teachers' => $teachers,
        'courses' => $courses,
        'sheets' => $sheets,
        'activeSemesterCount' => $activeSemesterIds->count(),
    ];
}
public function examSheets(Request $request)
{
    $meta = $this->buildExamSheetsData($request);

    return view('study_exams.exam-sheets', $meta);
}
public function examSheetsPrint(Request $request)
{
    $meta = $this->buildExamSheetsData($request);

    return view('study_exams.exam-sheets-print', $meta);
}

}  