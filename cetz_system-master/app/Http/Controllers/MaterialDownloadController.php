<?php

namespace App\Http\Controllers;
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
    return view('materials.download');
}
public function searchAutocomplete(Request $request)
{
    $query = $request->query('query');

    return Student::where('current_status', '!=', 'منقطع') // استبعاد المنقطعين
        ->where(function ($q) use ($query) {
            $q->where('full_name', 'like', "%$query%")
              ->orWhere('student_number', 'like', "%$query%");
        })
        ->limit(10)
        ->get(['id', 'full_name', 'student_number']);
}
public function search(Request $request)
{
    $query = trim($request->input('query'));

    // 1️⃣ البحث عن الطالب وتضمين القسم والتسجيلات
    $student = Student::where('current_status', 'تم التجديد')
        ->where(function ($q) use ($query) {
            $q->where('student_number', $query)
              ->orWhere('manual_number', $query)
              ->orWhere('full_name', 'like', "%{$query}%");
        })
        ->with([
            'section',
            'section.department',
            'enrollments' => function ($q) {
                $q->whereIn('status', ['in_progress', 'passed'])
                  ->with('courseOffering.course', 'courseOffering.semester');
            }
        ])
        ->first();

    // إذا لم يوجد الطالب
    if (!$student) {
        return response()->json([
            'student' => null,
            'available_semesters' => [],
            'available_materials' => [],
        ]);
    }

    // 2️⃣ قائمة المواد المسجلة بالفعل للطالب
    $blockedCourseIds = $student->enrollments
        ->pluck('courseOffering.course_id')
        ->filter()
        ->unique();

    // 3️⃣ المواد المتاحة للطالب حسب شعبة الطالب والفصول الفعالة
    $availableMaterials = CourseOffering::with(['course', 'semester'])
        ->where('status', 'active')
        ->where('section_id', $student->section_id)
        ->whereHas('semester', fn($q) => $q->where('active', 1))
        ->when($blockedCourseIds->isNotEmpty(), function ($q) use ($blockedCourseIds) {
            $q->whereNotIn('course_id', $blockedCourseIds);
        })
        ->get();

    // 4️⃣ الفصول المتاحة (فقط الفصول التي تحتوي على مواد)
    $availableSemesters = $availableMaterials
        ->pluck('semester')
        ->unique('id')
        ->values()
        ->map(fn($s) => [
            'id' => $s->id,
            'label' => $s->name,
            'start_date' => date('Y', strtotime($s->start_date)),
            'term_type' => $s->term_type,
        ]);

    // 5️⃣ تجهيز المواد للواجهة
    $materials = $availableMaterials->map(fn($o) => [
        'id' => $o->id,
        'semester_id' => $o->semester_id,
        'code' => $o->course?->course_code,
        'name' => $o->course?->name,
        'units' => $o->course?->units,
        'hours' => $o->course?->hours,
        'status' => $o->status
    ]);

    // 6️⃣ تجهيز تسجيلات الطالب الحالية
    $enrollments = $student->enrollments->map(fn($e) => [
        'id' => $e->id,
        'status' => $e->status,
        'course_offering_id' => $e->courseOffering?->id,
        'semester_id' => $e->courseOffering?->semester_id,
        'course' => [
            'code' => $e->courseOffering?->course?->course_code,
            'name' => $e->courseOffering?->course?->name,
            'units' => $e->courseOffering?->course?->units,
            'hours' => $e->courseOffering?->course?->hours,
        ],
    ]);

    // 7️⃣ الإرجاع كـ JSON جاهز للواجهة
    return response()->json([
        'student' => [
            'id' => $student->id,
            'full_name' => $student->full_name,
            'student_number' => $student->student_number,
            'section_id' => $student->section_id,
            'section_name' => $student->section?->name,
            'department_name' => $student->section?->department?->name,
            'enrollments' => $enrollments,
        ],
        'available_semesters' => $availableSemesters,
        'available_materials' => $materials,
    ]);
}



}
