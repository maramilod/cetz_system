<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Enrollment;
use App\Models\CourseOffering;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;
use PHPUnit\Event\Test\Failed;

class EnrollmentController extends Controller
{
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'course_offering_id' => 'required|exists:course_offerings,id',
    ]);

    if ($validator->fails()) {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422));
    }

    
    // جلب CourseOffering مع المادة
    $offering = CourseOffering::with('course')->findOrFail($request->course_offering_id);
    $course = $offering->course;

    // تحقق من المادة السابقة إذا موجودة
    if ($course->prerequisite_course_id) {
        $prerequisiteOffering = CourseOffering::where('course_id', $course->prerequisite_course_id)->get();

        // التأكد أن الطالب اجتاز المادة السابقة
        $passed = Enrollment::where('student_id', $request->student_id)
            ->whereIn('course_offering_id', $prerequisiteOffering->pluck('id'))
            ->where('status', 'passed')
            ->exists();

          if (!$passed && !$request->boolean('confirm_prerequisite')) {
        return response()->json([
            'success' => false,
            'type'    => 'prerequisite_warning',
            'message' => 'الطالب لم يجتز المادة السابقة، هل تريد المتابعة بالتسجيل؟'
        ], 409); 
    }
    }
    // البحث عن آخر تسجيل للمادة لهذا الطالب
     $studentEnrollments = Enrollment::where('student_id', $request->student_id)
        ->whereHas('courseOffering', fn($q) => $q->where('course_id', $course->id))
        ->orderBy('created_at', 'desc')
        ->first();

    if ($studentEnrollments) {
        if (in_array($studentEnrollments->status, ['in_progress', 'passed','equivalent' ])) {
            // المادة مسجلة مسبقًا، لا يمكن اعادة التسجيل
            return response()->json([
                'success' => false,
                'message' => 'المادة مسجلة مسبقًا لهذا الطالب (الحالة: ' . $studentEnrollments->status . ')'
            ], 422);
        }

        if ($studentEnrollments->status === 'failed') {
            // المادة مسجلة مسبقًا لكن الطالب راسب → زيادة attempt
            $attempt = $studentEnrollments->attempt + 1;

            $enrollment = Enrollment::create([
                'student_id' => $request->student_id,
                'course_offering_id' => $request->course_offering_id,
                'attempt' => $attempt,
                'status' => 'in_progress',
                'result_date' => null,
            ]);

            // رسالة توضح رقم المحاولة
            $attemptNames = [
                1 => 'الأولى',
                2 => 'الثانية',
                3 => 'الثالثة',
                4 => 'الرابعة'
            ];

            $attemptText = $attemptNames[$attempt] ?? "رقم $attempt";

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل المادة بنجاح (المحاولة $attemptText)",
                'enrollment' => $enrollment
            ]);
        }
    }

    // إذا لا يوجد تسجيل سابق
    $enrollment = Enrollment::create([
        'student_id' => $request->student_id,
        'course_offering_id' => $request->course_offering_id,
        'attempt' => 1,
        'status' => 'in_progress',
        'result_date' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تسجيل المادة بنجاح (المحاولة الأولى)',
        'enrollment' => $enrollment
    ]);
}

public function destroy($id)
{
    $enrollment = Enrollment::find($id);

    if (!$enrollment) {
        return response()->json(['message' => 'التسجيل غير موجود'], 404);
    }

    $enrollment->delete();

    return response()->json(['message' => 'تم حذف التسجيل بنجاح']);
}
private function determineStatus($offering, $student) {
    $existsInOtherSection = $student->enrollments->where('courseOffering.course_id', $offering->course_id)
        ->where('courseOffering.section_id', '!=', $student->section_id)
        ->isNotEmpty();

    return $existsInOtherSection ? 'equivalent' : 'in_progress';
}
/**
 * تعديل بيانات المادة المرتبطة بالتسجيل (الرمز والاسم)
 */
public function updateCourse(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'code' => 'required|string|max:50',
        'name' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $enrollment = Enrollment::with('courseOffering.course')->find($id);

    if (!$enrollment || !$enrollment->courseOffering || !$enrollment->courseOffering->course) {
        return response()->json([
            'success' => false,
            'message' => 'تسجيل المادة غير موجود'
        ], 404);
    }

    // تحديث بيانات المادة الأصلية
    $course = $enrollment->courseOffering->course;
    $course->update([
        'code' => $request->code,
        'name' => $request->name,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث بيانات المادة بنجاح',
        'course'  => $course
    ]);
}

/**
 * تبديل المادة بمادة أخرى متاحة
 */
public function swapCourse(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'new_course_offering_id' => 'required|exists:course_offerings,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $enrollment = Enrollment::find($id);

    if (!$enrollment) {
        return response()->json([
            'success' => false,
            'message' => 'التسجيل غير موجود'
        ], 404);
    }

    // تحديث العرض الخاص بالمادة (Course Offering)
    $enrollment->update([
        'course_offering_id' => $request->new_course_offering_id
    ]);

    return response()->json([
        'success'    => true,
        'message'    => 'تم تبديل المادة بنجاح',
        'enrollment' => $enrollment->load('courseOffering.course')
    ]);
}
public function autoRegister(Request $request)
{
    $studentId = $request->student_id;

    if (!$studentId) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم إرسال بيانات الطالب.',
            'count' => 0
        ]);
    }

    try {
        $student = Student::with(['enrollments.courseOffering.course', 'enrollments.courseOffering.semester'])
            ->findOrFail($studentId);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => "الطالب بالمعرف {$studentId} غير موجود.",
            'count' => 0
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "حدث خطأ أثناء جلب بيانات الطالب: " . $e->getMessage(),
            'count' => 0
        ]);
    }

    try {
        $latestEnrollments = $student->enrollments
            ->groupBy('courseOffering.course_id') // تجميع حسب المادة
            ->map(function($enrollments) {
                return $enrollments->sortByDesc('attempt')->first(); // آخر attempt فقط
            })
            ->values();

        $failedEnrollments = $latestEnrollments->filter(fn($e) => $e->status === 'failed');
        $failedCount = $failedEnrollments->count();


    $activeSemesters = Semester::where('active', 1)->orderBy('id')->get();
    $materialsToEnroll = collect();

        // إعادة تسجيل المواد الفاشلة
        foreach ($failedEnrollments as $enrollment) {
            if (!$enrollment->courseOffering) {
                return response()->json([
                    'success' => false,
                    'message' => "سجل الطالب لمادة تحتوي على course_offering مفقود.",
                    'count' => 0
                ]);
            }
            foreach ($activeSemesters as $semester) {
$failedEnrollmentsNewSem = CourseOffering::with('course')
    ->where('status', 'active')
    ->where('section_id', $student->section_id)
    ->where('semester_id', $semester->id)
    ->where('course_id', $enrollment->courseOffering->course_id)
    ->first(); // جلب أول نتيجة
            $attempt = $enrollment->attempt + 1;
           if ($failedEnrollmentsNewSem) {
    $materialsToEnroll->push([
        'course_offering_id' => $failedEnrollmentsNewSem->id,
        'attempt' => $attempt
    ]);
}
            }
        }

        // السيمستر القادم إذا عدد المواد الفاشلة <= 2
        // السيمستر القادم إذا عدد المواد الفاشلة <= 2
if ($failedCount < 3 || $student->current_status !== 'اعادة') {

// 1. المواد التي درسها في القسم "العام" أو سيمستر "عام" أو رقم 1
$generalCourseIds = $student->enrollments
    ->filter(function($enrollment) {
        $offering = $enrollment->courseOffering;
        if (!$offering) return false;
        $sectionId = $offering->section_id ?? null;
        $semesterNumber = optional($offering->semester)->semester_number ?? null;
        $semesterName = optional($offering->semester)->name ?? '';

        return $sectionId == 1 || $semesterNumber == 1 || $semesterName == 'عام' || $semesterName == 'العام';
    })
    ->pluck('courseOffering.course_id');

// 2. المضاف حديثاً: استخراج "كل" المواد التي نجح فيها الطالب سابقاً في أي قسم وأي سيمستر
$passedCourseIds = $student->enrollments
    ->filter(fn($e) => in_array($e->status, ['passed', 'equivalent']))
    ->pluck('courseOffering.course_id');

// 3. مواد الـ failed التي لم يتم تجاوزها بعد (لتجنب التكرار إذا كانت ستُسجل في "إعادة تسجيل الفاشلة")
$failedCourseIds = $failedEnrollments->pluck('courseOffering.course_id');

// الدمج النهائي: أي مادة (عامة) أو (ناجح فيها) أو (فاشلة) يجب ألا تظهر كمادة "جديدة"
$excludedCourseIds = $generalCourseIds
    ->merge($passedCourseIds)
    ->merge($failedCourseIds)
    ->filter()
    ->unique()
    ->values();


    // ... داخل مصفوفة السيمسترات المفعّلة ...

foreach ($activeSemesters as $semester) {
    // جلب مواد السيمستر المتاحة والتي لم تُستبعد
    $nextSemesterMaterials = CourseOffering::with('course')
        ->where('status', 'active')
        ->where('section_id', $student->section_id)
        ->where('semester_id', $semester->id)
        ->whereNotIn('course_id', $excludedCourseIds) 
        ->get();

    // فلترة المواد حسب المتطلبات السابقة
    $nextSemesterMaterials = $nextSemesterMaterials->filter(function($offering) use ($student, $latestEnrollments, $materialsToEnroll) {
        $course = $offering->course;
        if (!$course->prerequisite_course_id) return true;

        $isPassed = $latestEnrollments->whereIn('status', ['passed', 'equivalent'])
            ->where('courseOffering.course_id', $course->prerequisite_course_id)
            ->isNotEmpty();

        $isEquivalentNow = $materialsToEnroll->where('course_id', $course->prerequisite_course_id)
            ->where('status', 'equivalent')
            ->isNotEmpty();

        return $isPassed || $isEquivalentNow;
    });

    if ($nextSemesterMaterials->isNotEmpty()) {
        $addedInThisLoop = 0;

        foreach ($nextSemesterMaterials as $offering) {
            $status = $this->determineStatus($offering, $student); 

            $materialsToEnroll->push([
                'course_offering_id' => $offering->id,
                'course_id'          => $offering->course_id,
                'attempt'            => 1,
                'status'             => $status
            ]);

            if ($status === 'in_progress') {
                $addedInThisLoop++;
            }
        }
        
        // جلب إجمالي المواد الجديدة (in_progress) التي تم تجميعها من كل السيمسترات حتى الآن
        $totalNewCourses = $materialsToEnroll->where('status', 'in_progress')->count();

        // المنطق المحدث:
        // إذا كان إجمالي المواد الجديدة التي جمعناها حتى الآن وصل لـ 3 أو أكثر، هنا فقط نتوقف.
        // هذا يضمن أنه لو سيمستر فيه مادة واحدة، سيأخذها ويكمل للسيمستر اللي بعده ليجمع الـ 3 مواد.
        if ($totalNewCourses >= 3) {
            break; 
        }
    }
}
}
$enrollments = collect();

foreach ($materialsToEnroll as $material) {
    $materialStatus = 'in_progress';
    $oldGradeRecord = null; // لتخزين سجل الدرجات القديم بالكامل

    // البحث في سجلات الطالب السابقة
    foreach ($student->enrollments as $e) {
        $offeringCourseId = $e->courseOffering->course_id ?? null;
        $offeringSectionId = $e->courseOffering->section_id ?? null;

        if ($offeringCourseId == ($material['course_id'] ?? null)) {
            if ($offeringSectionId == $student->section_id) {
                $materialStatus = null; // سبق أخذها في نفس القسم
                break;
            } else {
                $materialStatus = 'equivalent';
                // جلب سجل الدرجات المرتبط بهذا التسجيل القديم
                $oldGradeRecord = $e->grade; 
                break;
            }
        }
    }

    if (!empty($materialStatus)) {
        try {
            // 1. إنشاء سجل التسجيل الجديد
            $newEnrollment = Enrollment::create([
                'student_id'         => $student->id,
                'course_offering_id' => $material['course_offering_id'],
                'attempt'            => $material['attempt'],
                'status'             => $materialStatus,
                'result_date'        => ($materialStatus === 'equivalent') ? now() : null,
            ]);

            // 2. إذا كانت المادة معادلة، ننسخ الدرجات إلى سجل جديد مربوط بالتسجيل الجديد
            if ($materialStatus === 'equivalent' && $oldGradeRecord) {
                // استخدام replicate لنسخ البيانات مع تغيير الحقول المرتبطة بالسجل الجديد
                $newGrade = $oldGradeRecord->replicate();
                $newGrade->enrollment_id = $newEnrollment->id; // ربط الدرجة بالتسجيل الجديد
                $newGrade->save();
            }

            $enrollments->push($newEnrollment);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل إنشاء تسجيل للطالب: " . $e->getMessage(),
            ]);
        }
    }
}

        return response()->json([
            'success' => true,
            'message' => $enrollments->isEmpty()
                ? "لا توجد مواد لإعادة التسجيل أو السيمستر القادم."
                : "تم تسجيل المواد تلقائيًا للطالب.",
            'enrollments' => $enrollments
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "حدث خطأ أثناء معالجة التسجيلات: " . $e->getMessage(),
        ]);
    }
}}


