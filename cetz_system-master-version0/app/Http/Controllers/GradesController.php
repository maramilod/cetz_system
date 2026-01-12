<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\CourseGradingRule;
use Illuminate\Support\Facades\DB;



class GradesController extends Controller
{
    public function index()
    {
        // جلب كل الانرولمنتس الحالية (in_progress) مع الطالب والمادة
      $enrollments = Enrollment::with(['student', 'courseOffering.course'])
                         ->where('status', 'in_progress')
                         ->get();

$grades = $enrollments->map(function($enrollment) {

    $grade = Grade::where('student_id', $enrollment->student_id)
                  ->where('course_id', $enrollment->courseOffering->course_id ?? null)
                  ->where('enrollment_id', $enrollment->id)
                  ->first();

    return [
        'id' => $grade->id ?? null,
        'student_id' => $enrollment->student->id,
        'student_name' => $enrollment->student->full_name,
        'student_number' => $enrollment->student->student_number ?? $enrollment->student->manual_number,

        'course_id' => $enrollment->courseOffering->course_id ?? null,
        'course_name' => $enrollment->courseOffering->course->name ?? 'غير محدد',
        'enrollment_id' => $enrollment->id,
        'theory_work' => $grade->theory_work ?? null,
        'theory_midterm' => $grade->theory_midterm ?? null,
        'theory_final' => $grade->theory_final ?? null,
        'practical_work' => $grade->practical_work ?? null,
        'practical_midterm' => $grade->practical_midterm ?? null,
        'practical_final' => $grade->practical_final ?? null,
        'total' => $grade->total ?? 0,
        'student_type' => $grade->student_type ?? 'مسجل',
        'is_second_chance' => $grade->is_second_chance ?? false,
                    'has_practical' =>  $enrollment->courseOffering->course->has_practical,

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

            'student_type'    => 'required|string',
            'is_second_chance'=> 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // جلب القاعدة الخاصة بالمادة
            $rule = CourseGradingRule::where('course_id', $request->course_id)->first();

            if (!$rule) {
                return response()->json([
                    'message' => 'لا توجد قواعد تقييم لهذه المادة.'
                ], 422);
            }

            // حساب الدرجة النهائية
            $total = 0;

            // الجزء النظري
            $total += ($request->theory_work ?? 0) * ($rule->theory_work_ratio / 100);
            $total += ($request->theory_midterm ?? 0) * ($rule->theory_midterm_ratio / 100);
            $total += ($request->theory_final ?? 0) * ($rule->theory_final_ratio / 100);

            // الجزء العملي إذا موجود
            if ($rule->hasPractical()) {
                $total += ($request->practical_work ?? 0) * ($rule->practical_work_ratio / 100);
                $total += ($request->practical_midterm ?? 0) * ($rule->practical_midterm_ratio / 100);
                $total += ($request->practical_final ?? 0) * ($rule->practical_final_ratio / 100);
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
                    'student_type'     => $request->student_type,
                    'is_second_chance' => $request->is_second_chance ?? false,
                ]
            );

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
}
