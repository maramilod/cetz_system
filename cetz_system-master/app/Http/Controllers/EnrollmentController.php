<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Enrollment;
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

    if (
        Enrollment::where('student_id', $request->student_id)
            ->where('course_offering_id', $request->course_offering_id)
            ->exists()
    ) {
        return response()->json([
            'success' => false,
            'message' => 'المادة مسجلة مسبقًا لهذا الطالب'
        ], 422);
    }

    $enrollment = Enrollment::create([
        'student_id' => $request->student_id,
        'course_offering_id' => $request->course_offering_id,
        'attempt' => 1,
        'status' => 'in_progress',
        'result_date' => null,
    ]);

    return response()->json([
        'success' => true,
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
