<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function setGraduated(Student $student)
    {
        $student->current_status = 'متخرج';
        $student->save();

        return redirect()->back()->with('success', 'تم تعيين الطالب كخريج بنجاح.');
    }

   public function index(Request $request)
{
    $query = Student::with('department:id,name')
        ->select(
            'id',
            'full_name',
            'student_number',
            'manual_number',
            'department_id',
            'current_status',
            'nationality',
            'gender',
            'passport_number',
            'dob'
        );

    // 🔍 البحث
    if ($request->search) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%$search%")
              ->orWhere('student_number', 'like', "%$search%")
              ->orWhere('manual_number', 'like', "%$search%");
        });
    }

    // 🎯 فلتر القسم
    if ($request->department) {
        $query->whereHas('department', function ($q) use ($request) {
            $q->where('name', $request->department);
        });
    }

    $students = $query->paginate(50)->withQueryString();
        $departments = Department::pluck('name');

    return view('students.index', compact('students', 'departments'));

}

   public function enrollmentStop(Request $request)
{
    $query = Student::query();

    // 🔍 البحث
    if ($request->search) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%$search%")
              ->orWhere('student_number', 'like', "%$search%")
              ->orWhere('manual_number', 'like', "%$search%");
        });
    }

    // 🎯 فلتر الحالة
    if ($request->status) {
        $query->where('current_status', $request->status);
    }

    // 📄 pagination
    $students = $query
        ->select('id', 'full_name', 'student_number', 'manual_number', 'current_status')
        ->paginate(50)
        ->withQueryString();

    // 📊 الإحصائيات (من كل الداتا مش الصفحة)
    $stats = Student::selectRaw('current_status, COUNT(*) as total')
        ->groupBy('current_status')
        ->pluck('total', 'current_status');

    return view('registration.enrollment-stop', compact('students', 'stats'));
}
    public function updateStatus(Request $request, $id)
    {

      $student = Student::findOrFail($id);

      // ✅ حالة جعل الطالب منقطع
if (trim($request->status) === 'منقطع') {
    $student->current_status = 'منقطع';
    $student->save();

    return response()->json([
        'success' => true,
        'status' => 'منقطع',
        'message' => 'تم تحويل الطالب إلى منقطع.'
    ]);
}

// ✅ حالة إزالة الانقطاع
if (trim($request->status) === 'إزالة الانقطاع') {
    $student->current_status = 'جاهز للتجديد';
    $student->save();

    return response()->json([
        'success' => true,
        'status' => 'جاهز للتجديد',
        'message' => 'تمت إزالة حالة الانقطاع وأصبح الطالب جاهز لتجديد القيد.'
    ]);
}
         if (trim($request->status) === 'جاهز للتجديد') {
        $student->current_status = 'جاهز للتجديد';
        $student->save();

        return response()->json([
            'success' => true,
            'status' => 'جاهز للتجديد',
            'message' => 'تم إلغاء تجديد القيد .'
        ]);
    }
            if (trim($request->status) === 'تم التجديد') {
                $carryCoursesCount = $this->countOutstandingCarryCourses($student);
            $repeatedFailedCoursesCount = $this->countBlockedRepeatedCourses($student);

           if ($request->status === 'تم التجديد') {

    if ($carryCoursesCount > 3) {

        $student->current_status = 'اعادة';
        $student->save();

        return response()->json([
            'success' => true,
            'status' => 'إعادة',
            'message' => "تم تحويل الطالب إلى (إعادة) بسبب وجود {$carryCoursesCount} مواد مرحلة."
        ]);
    }

    if ($repeatedFailedCoursesCount > 0) {

        $student->current_status = 'إعادة';
        $student->save();

        return response()->json([
            'success' => true,
            'status' => 'إعادة',
            'message' => 'تم تحويل الطالب إلى (إعادة) لأنه أعاد مادة 3 مرات ولم ينجح.'
        ]);
    }
}
        }

        DB::transaction(function () use ($request, $id) {
    $student = Student::findOrFail($id);

    // لا نغير إذا تم تحويله لإعادة مسبقاً
    if ($student->current_status !== 'إعادة') {
        $student->current_status = $request->status;
        $student->save();
    }
});
        

        return response()->json([
            'success' => true,
            'message' => 'تم تجديد القيد',
        ]);
    }

    private function countOutstandingCarryCourses(Student $student): int
    {
        $latestCourseAttempts = $this->latestCourseAttempts($student);

        return $latestCourseAttempts
            ->filter(fn ($enrollment) => $enrollment->status === 'failed')
            ->count();
    }

    private function countBlockedRepeatedCourses(Student $student): int
    {
        $allAttempts = Enrollment::query()
            ->with([
                'courseOffering:id,course_id,semester_id',
                'courseOffering.semester:id,semester_number,start_date',
            ])
            ->where('student_id', $student->id)
            ->whereHas('courseOffering.semester', function ($query) {
                $query->where('semester_number', '!=', 99);
            })
            ->get()
            ->filter(fn ($enrollment) => $enrollment->courseOffering?->course_id)
            ->sortBy(function ($enrollment) {
                return sprintf(
                    '%s-%02d-%010d',
                    (string) ($enrollment->courseOffering?->semester?->start_date ?? '0000-00-00'),
                    (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0),
                    (int) $enrollment->attempt,
                );
            })
            ->groupBy(function ($enrollment) {
                return (int) $enrollment->courseOffering->course_id;
            });

        return $allAttempts
            ->filter(function ($group) {
                $latestAttempt = $group->last();
                $attemptsCount = $group->count();

                return $latestAttempt && $latestAttempt->status === 'failed' && $attemptsCount >= 3;
            })
            ->count();
    }

    private function latestCourseAttempts(Student $student)
    {
        return Enrollment::query()
            ->with([
                'courseOffering:id,course_id,semester_id',
                'courseOffering.semester:id,semester_number,start_date',
            ])
            ->where('student_id', $student->id)
            ->whereHas('courseOffering.semester', function ($query) {
                $query->where('semester_number', '!=', 99);
            })
            ->get()
            ->filter(fn ($enrollment) => $enrollment->courseOffering?->course_id)
            ->sortBy(function ($enrollment) {
                return sprintf(
                    '%s-%02d-%010d',
                    (string) ($enrollment->courseOffering?->semester?->start_date ?? '0000-00-00'),
                    (int) ($enrollment->courseOffering?->semester?->semester_number ?? 0),
                    (int) $enrollment->attempt,
                );
            })
            ->groupBy(function ($enrollment) {
                return (int) $enrollment->courseOffering->course_id;
            })
            ->map(fn ($group) => $group->last());
    }

    public function create()
    {
        $departments = Department::where('is_active', 1)->get();
        $sections = Section::all();

        return view('students.create', compact('departments', 'sections'));
    }
    
public function excel()
{
    // 1️⃣ جلب الطلاب النشطين فقط مع القسم والمواد والفصول
    $students = Student::with([
        'department:id,name',
        'enrollments:id,student_id,course_offering_id',
        'enrollments.courseOffering:id,semester_id',
        'enrollments.courseOffering.semester:id,start_date,term_type'
    ])
    ->where('current_status', '!=', 'منقطع')// استبعاد المنقطعين
    ->get([
        'id',
        'full_name',
        'mother_name',
        'nationality',
        'gender',
        'registration_year',
        'student_number',
        'manual_number',
        'national_id',
        'passport_number',
        'bank_name',
        'account_number',
        'dob',
        'family_record',
        'department_id',
    ]);

    // 2️⃣ سنوات التسجيل
    $years = $students->pluck('registration_year')->unique()->sortDesc()->values();

    // 3️⃣ الأقسام
    $departments = Department::orderBy('name')->pluck('name');

    // 4️⃣ الفصول المتاحة
    $availableTerms = $students->flatMap(function ($s) {
        return $s->enrollments->map(function ($e) {
            $semester = $e->courseOffering?->semester;
            if (!$semester) return null;
            $year = substr($semester->start_date, 0, 4);
            return $year . ' ' . $semester->term_type;
        });
    })->filter()->unique()->values();

    
    // 5️⃣ تحضير بيانات الجافاسكربت
    $studentsForJs = $students->map(function ($s) {
        $academicTerms = $s->enrollments->map(function($e) {
            $semester = $e->courseOffering?->semester;
            if (!$semester) return null;
            $year = substr($semester->start_date, 0, 4);
            return [
                'year' => $year,
                'term_type' => $semester->term_type ?? '',
            ];
        })->filter()->unique()->values();

        return [
            'id' => $s->id,
            'full_name' => $s->full_name,
            'mother_name' => $s->mother_name,
            'nationality' => $s->nationality,
            'gender' => $s->gender,
            'year' => $s->registration_year,
            'academic_term' => '',
            'student_number' => $s->student_number,
            'manual_number' => $s->manual_number,
            'national_id' => $s->national_id,
            'passport_number' => $s->passport_number,
            'bank_name' => $s->bank_name,
            'bank_account' => $s->account_number,
            'birth_date' => $s->dob,
            'family_record' => $s->family_record,
            'department' => $s->department?->name,
            'academic_terms' => $academicTerms,
        ];
    });

    return view('students.excel', compact(
        'studentsForJs',
        'availableTerms',
        'years',
        'departments'
    ));
}

    public function rank(Request $request)
    {
        $filters = [
            'term_type' => trim((string) $request->query('term_type', '')),
            'year' => trim((string) $request->query('year', '')),
            'department_id' => trim((string) $request->query('department_id', '')),
            'semester_number' => trim((string) $request->query('semester_number', '')),
        ];

        $departments = Department::active()
            ->orderBy('name')
            ->get(['id', 'name', 'is_general']);

        $generalDepartment = $departments->firstWhere('is_general', 1)
            ?? Department::query()->where('is_general', 1)->first(['id', 'name', 'is_general']);

        $isGeneralDepartmentSelected = $generalDepartment
            && $filters['department_id'] === (string) $generalDepartment->id;
        $isSemesterOneSelected = $filters['semester_number'] === '1';

        if ($isGeneralDepartmentSelected) {
            $filters['semester_number'] = '1';
        }

        $semesterCatalog = Semester::query()
            ->select('id', 'term_type', 'semester_number', 'start_date', 'end_date')
            ->whereNotNull('start_date')
            ->orderByDesc('start_date')
            ->get();

        $termTypes = $semesterCatalog->pluck('term_type')->filter()->unique()->values();
        $years = $semesterCatalog
            ->map(fn ($semester) => $semester->start_date ? date('Y', strtotime($semester->start_date)) : null)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
        $semesterNumbers = $semesterCatalog->pluck('semester_number')->filter()->unique()->sort()->values();

        $reportRows = collect();

        if ($filters['term_type'] !== '' || $filters['year'] !== '' || $filters['department_id'] !== '' || $filters['semester_number'] !== '') {
            $studentsQuery = Student::query()
                ->with([
                    'department:id,name',
                    'enrollments.grade:id,enrollment_id,total',
                    'enrollments.courseOffering.course:id,units',
                    'enrollments.courseOffering.section:id,name,department_id',
                    'enrollments.courseOffering.semester:id,name,term_type,semester_number,start_date,end_date',
                ]);

            if ($filters['department_id'] !== '') {
                $studentsQuery->whereHas('enrollments.courseOffering.section', function ($query) use ($filters) {
                    $query->where('department_id', $filters['department_id']);
                });
            }

            $studentsQuery->whereHas('enrollments', function ($query) {
                $query->where('status', '!=', 'equivalent');
            });

            $studentsQuery->whereHas('enrollments.courseOffering.semester', function ($query) use ($filters) {
                if ($filters['term_type'] !== '') {
                    $query->where('term_type', $filters['term_type']);
                }

                if ($filters['year'] !== '') {
                    $query->whereYear('start_date', $filters['year']);
                }

                if ($filters['semester_number'] !== '') {
                    $query->where('semester_number', $filters['semester_number']);
                }
            });

            $students = $studentsQuery->get([
                'id',
                'full_name',
                'student_number',
                'manual_number',
                'department_id',
            ]);

            $reportRows = $students
                ->map(function (Student $student) use ($filters, $departments, $generalDepartment, $isGeneralDepartmentSelected, $isSemesterOneSelected) {
                    $matchedEnrollments = $student->enrollments->filter(function ($enrollment) use ($filters) {
                        $semester = $enrollment->courseOffering?->semester;
                        $offeringDepartmentId = (string) ($enrollment->courseOffering?->section?->department_id ?? '');

                        if (!$semester) {
                            return false;
                        }

                        if ($enrollment->status === 'equivalent') {
                            return false;
                        }

                        if ($filters['term_type'] !== '' && $semester->term_type !== $filters['term_type']) {
                            return false;
                        }

                        if ($filters['year'] !== '' && (!$semester->start_date || date('Y', strtotime($semester->start_date)) !== $filters['year'])) {
                            return false;
                        }

                        if ($filters['semester_number'] !== '' && (string) $semester->semester_number !== $filters['semester_number']) {
                            return false;
                        }

                        if (
                            $filters['department_id'] !== ''
                            && $offeringDepartmentId !== ''
                            && $offeringDepartmentId !== $filters['department_id']
                        ) {
                            return false;
                        }

                        return true;
                    })->values();

                    

                    if ($matchedEnrollments->isEmpty()) {
                        return null;
                    }

                    if ($isGeneralDepartmentSelected && !$matchedEnrollments->contains(function ($enrollment) {
                        return (string) $enrollment->courseOffering?->semester?->semester_number === '1';
                    })) {
                        return null;
                    }

                    $cutoffDate = $matchedEnrollments
                        ->map(fn ($enrollment) => $enrollment->courseOffering?->semester?->end_date ?: $enrollment->courseOffering?->semester?->start_date)
                        ->filter()
                        ->max();

                    $cumulativeEnrollments = $student->enrollments->filter(function ($enrollment) use ($cutoffDate) {
                        $semester = $enrollment->courseOffering?->semester;

                        if (!$semester || !$enrollment->grade || $enrollment->status === 'equivalent') {
                            return false;
                        }

                        if (!$cutoffDate) {
                            return true;
                        }

                        $semesterDate = $semester->end_date ?: $semester->start_date;

                        return $semesterDate && $semesterDate <= $cutoffDate;
                    })->values();

                    $semesterLabels = $matchedEnrollments
                        ->map(function ($enrollment) {
                            $semester = $enrollment->courseOffering?->semester;

                            if (!$semester) {
                                return null;
                            }

                            $year = $semester->start_date ? date('Y', strtotime($semester->start_date)) : '';

                            return trim(($semester->term_type ?: '') . ' ' . $year . ' / سيمستر ' . $semester->semester_number);
                        })
                        ->filter()
                        ->unique()
                        ->values();

                    $matchedDepartmentNames = $matchedEnrollments
                        ->map(function ($enrollment) use ($departments) {
                            $departmentId = $enrollment->courseOffering?->section?->department_id;

                            return $departments->firstWhere('id', $departmentId)?->name;
                        })
                        ->filter()
                        ->unique()
                        ->values();

                    $displayDepartmentName = (($isSemesterOneSelected || $isGeneralDepartmentSelected) && $generalDepartment)
                        ? $generalDepartment->name
                        : ($matchedDepartmentNames->implode('، ') ?: ($student->department?->name ?: '-'));

                    return [
                        'id' => $student->id,
                        'full_name' => $student->full_name,
                        'student_number' => preg_replace('/\D+/', '', (string) ($student->student_number ?: $student->manual_number)),
                        'department_name' => $displayDepartmentName,
                        'matched_semesters' => $semesterLabels->implode('، '),
                        'term_average' => $this->calculateAverage($matchedEnrollments),
                        'cumulative_average' => $this->calculateAverage($cumulativeEnrollments),
                        'courses_count' => $matchedEnrollments->count(),
                    ];
                })
                ->filter()
                ->sortByDesc(fn ($row) => $row['term_average'] ?? -1)
                ->values();
        }

        return view('students.rank', [
            'filters' => $filters,
            'departments' => $departments,
            'termTypes' => $termTypes,
            'years' => $years,
            'semesterNumbers' => $semesterNumbers,
            'reportRows' => $reportRows,
            'studentsCount' => $reportRows->count(),
            'averageTermGpa' => $reportRows->isNotEmpty() ? round($reportRows->avg('term_average'), 2) : null,
            'averageCumulativeGpa' => $reportRows->isNotEmpty() ? round($reportRows->avg('cumulative_average'), 2) : null,
        ]);
    }

    private function calculateAverage($enrollments): ?float
    {
        $gradedEnrollments = collect($enrollments)->filter(fn ($enrollment) => $enrollment->grade);

        if ($gradedEnrollments->isEmpty()) {
            return null;
        }

        $totalUnits = $gradedEnrollments->sum(function ($enrollment) {
            return (float) ($enrollment->courseOffering?->course?->units ?? 0);
        });

        if ($totalUnits > 0) {
            $weightedTotal = $gradedEnrollments->sum(function ($enrollment) {
                $units = (float) ($enrollment->courseOffering?->course?->units ?? 0);
                $grade = (float) ($enrollment->grade?->total ?? 0);

                return $units * $grade;
            });

            return round($weightedTotal / $totalUnits, 2);
        }

        return round((float) $gradedEnrollments->avg(fn ($enrollment) => $enrollment->grade?->total ?? 0), 2);
    }

    public function show(Student $student)
    {
        $student->load('department');

        return view('students.show', compact('student'));
    }

    public function freezeAll()
    {
        Student::whereIn('current_status', ['تم التجديد', 'إعادة'])
            ->update(['current_status' => 'جاهز للتجديد']);

        return back()->with('success', 'تم إيقاف قيد جميع الطلبة بنجاح.');
    }

    public function studentEnrollments(Request $request)
    {
        $studentNumber = trim((string) $request->input('student_number', ''));

        if ($studentNumber === '') {
            return view('students.enrollments', [
                'student' => null,
                'semesterEnrollments' => collect(),
            ]);
        }

        $student = Student::where('student_number', $studentNumber)
            ->orWhere('manual_number', $studentNumber)
            ->first();

        if (!$student) {
            return view('students.enrollments', [
                'student' => null,
                'semesterEnrollments' => collect(),
            ])->with('error', 'الطالب غير موجود');
        }

        $enrollments = Enrollment::with([
            'courseOffering.course',
            'courseOffering.semester',
            'grade',
        ])
            ->where('student_id', $student->id)
            ->whereHas('courseOffering.semester')
            ->get()
            ->sortByDesc(fn ($enrollment) => $enrollment->courseOffering->semester->start_date);

        $semesterEnrollments = $enrollments
            ->groupBy(function ($enrollment) {
                $semester = $enrollment->courseOffering->semester;

                if (!$semester) {
                    return 0;
                }

                return $semester->term_type . '_' . date('Y', strtotime($semester->start_date)) . '_' . $semester->id;
            })
            ->map(function ($enrollmentsInSemester) {
                $semester = $enrollmentsInSemester->first()->courseOffering->semester;

                $enrollmentsData = $enrollmentsInSemester->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'course_name' => $enrollment->courseOffering->course->name,
                        'course_code' => $enrollment->courseOffering->course->course_code,
                        'units' => $enrollment->courseOffering->course->units,
                        'hours' => $enrollment->courseOffering->course->hours,
                        'status' => $enrollment->status,
                        'attempt' => $enrollment->attempt,
                        'total' => $enrollment->grade?->total,
                        'grade' => $enrollment->grade,
                    ];
                });

                $totalPoints = $enrollmentsData->sum(function ($enrollment) {
                    return $enrollment['total'] ?? 0;
                });

                $subjectsCount = $enrollmentsData->count();
                $gpa = $subjectsCount ? round(($totalPoints / $subjectsCount), 2) : null;

                return [
                    'semester_name' => $semester?->name ?? 'غير محدد',
                    'semester_id' => $semester?->id,
                    'term_type' => $semester?->term_type,
                    'year' => date('Y', strtotime($semester->start_date)),
                    'start_date' => $semester?->start_date,
                    'end_date' => $semester?->end_date,
                    'active' => $semester?->active ?? false,
                    'enrollments' => $enrollmentsData,
                    'total_units' => $enrollmentsData->sum('units'),
                    'total_hours' => $enrollmentsData->sum('hours'),
                    'gpa' => $gpa,
                ];
            })
            ->sortBy(fn ($semester) => strtotime($semester['end_date']));

        return view('students.enrollments', compact('student', 'semesterEnrollments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nationality' => 'required|string|max:100',
            'gender' => 'required|in:ذكر,انثى',
            'department_id' => 'required|exists:departments,id',
            'registration_year' => 'required|digits:4|integer',
            'academic_term' => 'nullable|string|max:50',
            'student_number' => 'nullable|string|max:50|unique:students,student_number',
            'manual_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50|unique:students,national_id',
            'passport_number' => 'nullable|string|max:50',
            'dob' => 'required|date',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'family_record' => 'nullable|string|max:50',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $student = new Student();
        $department = Department::findOrFail($request->department_id);

        $student->full_name = $request->full_name;
        $student->nationality = $request->nationality;
        $student->gender = $request->gender;
        $student->department_id = $department->id;
        $student->registration_year = $request->registration_year;
        $student->academic_term = $request->academic_term;
        $student->student_number = $request->student_number;
        $student->manual_number = $request->manual_number;
        $student->national_id = $request->national_id;
        $student->passport_number = $request->passport_number;
        $student->dob = $request->dob;
        $student->bank_name = $request->bank_name;
        $student->account_number = $request->account_number;
        $student->mother_name = $request->mother_name;
        $student->family_record = $request->family_record;
        $student->section_id = $request->section_id;

        if ($request->hasFile('photo')) {
            $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->storeAs('students', $filename, 'public');
            $student->photo = $filename;
        }

        $institution = Institution::first();
        $collegeOrInstitute = ($institution->type == 'كلية') ? '1' : '2';
        $instituteNumber = '09';
        $year = substr($request->registration_year, -2);

        $termMap = [
            'fall_n' => 1,
            'spring_n' => 2,
            'fall_o' => 3,
            'spring_o' => 4,
            'added' => 5,
            'full_n' => 6,
            'full_o' => 7,
        ];
        $termCode = $termMap[$request->academic_term] ?? 0;

        $count = Student::where('registration_year', $request->registration_year)
            ->where('academic_term', $request->academic_term)
            ->count() + 1;

        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        $student->student_number = $collegeOrInstitute . $instituteNumber . $year . $termCode . $sequence;

        $student->save();

        return redirect()
            ->route('students.create')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->photo && Storage::disk('public')->exists('students/' . $student->photo)) {
            Storage::disk('public')->delete('students/' . $student->photo);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'تم حذف الطالب بنجاح');
    }

    public function edit(Student $student)
    {
        $departments = Department::all();
        $sections = Section::all();

        return view('students.edit', compact('student', 'departments', 'sections'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nationality' => 'required|string|max:100',
            'gender' => 'required|in:ذكر,انثى',
            'department_id' => 'required|exists:departments,id',
            'registration_year' => 'required|digits:4|integer',
            'academic_term' => 'nullable|string|max:50',
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->ignore($student->id),
            ],
            'manual_number' => 'nullable|string|max:50',
            'national_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'national_id')->ignore($student->id),
            ],
            'passport_number' => 'nullable|string|max:50',
            'dob' => 'required|date',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'family_record' => 'nullable|string|max:50',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'section_id' => [
                'nullable',
                'exists:sections,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->department_id == 5 && $value == 5 && empty($request->full_name)) {
                        $fail('بالنسبة للقسم 5 والشعبة 5، يجب تعبئة الاسم الكامل.');
                    }
                },
            ],
        ]);

        $data = $request->only([
            'full_name',
            'nationality',
            'gender',
            'department_id',
            'registration_year',
            'academic_term',
            'student_number',
            'manual_number',
            'national_id',
            'passport_number',
            'dob',
            'bank_name',
            'account_number',
            'mother_name',
            'family_record',
            'section_id',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists('students/' . $student->photo)) {
                Storage::disk('public')->delete('students/' . $student->photo);
            }

            $filename = time() . '_' . $request->photo->getClientOriginalName();
            $request->photo->storeAs('students', $filename, 'public');
            $data['photo'] = $filename;
        }

        $student->update($data);

        return redirect()
            ->route('students.show', $student->id)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function createCertificate()
    {
        $students = Student::select('student_number', 'full_name', 'department_id', 'national_id')
            ->with('department')
            ->get()
            ->map(function ($student) {
                return [
                    'number' => $student->student_number,
                    'name' => $student->full_name,
                    'department' => $student->department?->name ?? '',
                    'nationalId' => $student->national_id,
                ];
            });

$institute = Institution::first();

if (!$institute) {
    $institute = (object)[
        'name' => 'غير محدد',
        'address' => '',
        'phone' => '',
    ];
}
        return view('registration.student-certificate', compact('students', 'institute'));
    }
}
