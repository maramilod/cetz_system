<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Enrollment;
use App\Models\CourseOffering;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

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
            // المادة مسجلة مسبقًا، لا يمكن إعادة التسجيل
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


}
