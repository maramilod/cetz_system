<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Grade;
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
    public function appeals()
{
    $activeSemesterIds = Semester::where('active', 1)->pluck('id');

    $enrollments = Enrollment::whereHas('courseOffering', function($q) use ($activeSemesterIds) {
            $q->whereIn('semester_id', $activeSemesterIds);
        })
        ->whereIn('status', ['passed', 'failed']) // حسب حاجتك
        ->with([
            'student',
            'courseOffering.course',
        ])
        ->get();


    $grades = $enrollments->map(function ($enrollment) {

        $grade = Grade::where('student_id', $enrollment->student_id)
            ->where('course_id', $enrollment->courseOffering->course_id)
            ->where('enrollment_id', $enrollment->id)
            ->first();

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
                    'attempt' =>  $enrollment->attempt ,

            'has_practical' => $enrollment->courseOffering->course->has_practical,
        ];
    });

    return view('grades.appeals', compact('grades'));
}

}
