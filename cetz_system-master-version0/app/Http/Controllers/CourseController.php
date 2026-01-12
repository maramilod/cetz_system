<?php 
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseGradingRule;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Section;
use App\Models\Enrollment;

use App\Models\CourseOffering;



class CourseController extends Controller
{
    
public function drop($id)
{
    $offering = CourseOffering::findOrFail($id);

    $offering->update([
        'status' => 'dropped'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم إسقاط المادة'
    ]);
}

public function restore($id)
{
    $offering = CourseOffering::findOrFail($id);

    $offering->update([
        'status' => 'active'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم إلغاء إسقاط المادة'
    ]);
}
    public function destroy($id)
{
    $offering = CourseOffering::findOrFail($id);

    $offering->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف المادة بنجاح'
    ]);
}
    public function index()
{
    $courses = Course::with('gradingRules')->get();
    $departments = Department::where('is_active', 1)->get();
    $sections = Section::where('is_active', 1)->get();
        $now = Carbon::now();

$semesters = Semester::where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now)
                     ->get()
                     ->map(function ($s) {
                         return [
                             'id'    => $s->id,
                             'label' => $s->name
                         ];
                     });  
    $materials = CourseOffering::with(['course', 'section', 'semester'])->get()->map(function($offering){
    return [
        'id'          => $offering->id,
        'section_id'  => $offering->section_id,
        'semester_id' => $offering->semester_id,
                    'status'        => $offering->status, 
        'code'        => $offering->course->course_code,
        'name'        => $offering->course->name,
        'units'       => $offering->course->units,
        'hours'       => $offering->course->hours,
        'section_name'=> $offering->section?->name,
        'semester_name'=> $offering->semester?->name,
    ];
});


    return view('courses.index', compact('courses', 'departments', 'sections', 'semesters','materials'));
}

    public function create()
    {
          //$departments = Department::all();
           $departments = Department::where('is_active', 1)->get();

        // جلب المواد السابقة
        $courses = Course::all();

        // جلب الشعب
       // $sections = Section::all();
         $sections = Section::where('is_active', 1)->get();

        // جلب كل السيمسترات
        $semesters = Semester::all();
         $startDates = Semester::query()
        ->select('start_date')
        ->distinct()
        ->orderBy('start_date')
        ->pluck('start_date');

    $endDates = Semester::query()
        ->select('end_date')
        ->whereNotNull('end_date')
        ->distinct()
        ->orderBy('end_date')
        ->pluck('end_date');
        $courses = Course::all();
        return view('courses.create', compact(
            'courses',
            'startDates',
            'endDates',
            'departments', 
            'sections',
            'semesters',
        ));
    }

    public function getSemestersByStartDate(Request $request)
{
    // 1️⃣ التحقق من البيانات
    if (!$request->start_date) {
        return response()->json([
            'message' => 'لم يتم إرسال تاريخ البداية'
        ], 422);
    }

    // 2️⃣ جلب السيمسترات
    $semesters = Semester::where('start_date', $request->start_date)->get();

    // 3️⃣ التحقق من وجود بيانات
    if ($semesters->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد سيمسترات تبدأ بهذا التاريخ'
        ], 404);
    }

    // 4️⃣ إرجاع النتيجة
    return response()->json([
        'count' => $semesters->count(),
        'semesters' => $semesters
    ]);
}

public function getSemestersByDateRange(Request $request)
{
    // 1️⃣ التحقق من القيم
    if (!$request->start_date || !$request->end_date) {
        return response()->json([
            'message' => 'يجب اختيار تاريخ البداية وتاريخ النهاية'
        ], 422);
    }

    if ($request->start_date > $request->end_date) {
        return response()->json([
            'message' => 'تاريخ البداية لا يمكن أن يكون أكبر من تاريخ النهاية'
        ], 422);
    }

    // 2️⃣ جلب السيمسترات ضمن النطاق
    $semesters = Semester::where('start_date', '>=', $request->start_date)
        ->where('end_date', '<=', $request->end_date)
        ->orderBy('start_date')
        ->get();

    // 3️⃣ في حال عدم وجود نتائج
    if ($semesters->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد سيمسترات ضمن هذا النطاق الزمني',
            'semesters' => []
        ]);
    }

    // 4️⃣ إرجاع النتيجة
    return response()->json([
        'count' => $semesters->count(),
        'semesters' => $semesters
    ]);
}
   public function store(Request $request)
    {
        $request->validate([
    'name' => 'required|string|max:255',
    'course_code' => 'required|string|max:50|unique:courses,course_code',
    'hours' => 'required|integer|min:0',
    'units' => 'required|integer|min:0',
    'has_practical' => 'nullable|boolean',
    'prerequisite_course_id' => 'nullable|exists:courses,id',
    'theory_work_ratio' => 'required|integer|min:0|max:100',
    'theory_midterm_ratio' => 'required|integer|min:0|max:100',
    'theory_final_ratio' => 'required|integer|min:0|max:100',
    'practical_work_ratio' => 'nullable|integer|min:0|max:100',
    'practical_midterm_ratio' => 'nullable|integer|min:0|max:100',
    'practical_final_ratio' => 'nullable|integer|min:0|max:100',

'selectedSections' => 'required|json',
'start_date' => 'required|date',
'end_date'   => 'required|date|after_or_equal:start_date',

]);




        $course = Course::create([
    'name' => $request->name,
    'course_code' => $request->course_code,
    'has_practical' => $request->has_practical ? true : false,
    'prerequisite_course_id' => $request->prerequisite_course_id,
    'hours' => $request->hours,
    'units' => $request->units,
]);

// حفظ نسب التقييم
$gradingData = [
    'course_id' => $course->id,
    'theory_work_ratio' => $request->theory_work_ratio,
    'theory_midterm_ratio' => $request->theory_midterm_ratio,
    'theory_final_ratio' => $request->theory_final_ratio,
    'practical_work_ratio' => $request->practical_work_ratio ?? null,
    'practical_midterm_ratio' => $request->practical_midterm_ratio ?? null,
    'practical_final_ratio' => $request->practical_final_ratio ?? null,
];



CourseGradingRule::create($gradingData);


$sectionsData = json_decode($request->selectedSections, true);
foreach ($sectionsData as $sectionId => $sectionData) {

    if (!($sectionData['selected'] ?? false)) {
        continue;
    }

    foreach ($sectionData['semesters'] as $semesterNumber => $isSelected) {

        if (!$isSelected) {
            continue;
        }



         $semesters = Semester::where('semester_number', $semesterNumber)
    ->where('start_date', '>=', $request->start_date)
    ->where('end_date', '<=', $request->end_date)
    ->orderBy('start_date', 'asc') // ترتيب تصاعدي حسب تاريخ البداية
    ->get();


        foreach ($semesters as $semester) {
            CourseOffering::create([
                'course_id'   => $course->id,
                'section_id'  => $sectionId,
                'semester_id' => $semester->id,
            ]);
        }
    }
}


return redirect()->back()->with('success', 'تم إضافة المادة مع نسب التقييم بنجاح');

    }


    public function deleteEnrollment($id)
{
    $enrollment = Enrollment::find($id);
    if (!$enrollment) {
        return response()->json(['message' => 'التسجيل غير موجود'], 404);
    }

    $enrollment->delete();

    return response()->json(['message' => 'تم حذف التسجيل بنجاح']);
}

public function updateCourse(Request $request)
{
    $request->validate([
        'course_id' => 'required|exists:courses,id',
        'name' => 'required|string|max:255',
        'course_code' => 'required|string|max:50',
        'hours' => 'required|integer|min:0',
        'units' => 'required|integer|min:0',
    ]);

    $course = Course::find($request->course_id);
    $course->update([
        'name' => $request->name,
        'course_code' => $request->course_code,
        'hours' => $request->hours,
        'units' => $request->units,
    ]);

    // تحديث grading rule إذا تم إرسالها
    if ($request->grading_rule) {
        $course->gradingRules()->updateOrCreate(
            ['course_id' => $course->id],
            $request->grading_rule
        );
    }

    return response()->json(['message' => 'تم تحديث المادة بنجاح', 'course' => $course]);
}

}
