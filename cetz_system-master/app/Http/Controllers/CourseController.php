<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseGradingRule;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Section;
use App\Models\Enrollment;
use Illuminate\Validation\Rule;

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
    $courses = Course::all();
    $departments = Department::where('is_active', 1)->get();
    $sections = Section::where('is_active', 1)->get();

   $semesters = Semester::where('active', 1)
        ->get()
        ->map(function ($s) {
            return [
                'id'    => $s->id,
                'label' => $s->name
            ];
        });
       $materials = CourseOffering::with(['course', 'section', 'semester'])
        ->whereHas('semester', function($q) {
            $q->where('active', 1);
        })
        ->get()
        ->map(function($offering){
            return [
                'id'          => $offering->id,
                'course_id'   => $offering->course->id,
                'section_id'  => $offering->section_id,
                'semester_id' => $offering->semester_id,
                'status'      => $offering->status, 
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

    /*public function getSemestersByStartDate(Request $request)
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
*/
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
        'course_code' => 'required|string|max:50',
        'hours' => 'required|integer|min:0',
        'units' => 'required|integer|min:0',
        'has_practical' => 'nullable|boolean',
        'prerequisite_course_id' => 'nullable|exists:courses,id',
        'selectedSections' => 'required|json',
        'start_date' => 'required|date',
        'end_date'   => 'required|date|after_or_equal:start_date',
    ]);

    // 2. تحقق إذا المادة موجودة مسبقًا بنفس الكود (غير الـ course_id الحالي)
    $existingCourse = Course::where('course_code', $request->course_code)
                            ->when($request->course_id, fn($q) => $q->where('id', '!=', $request->course_id))
                            ->first();

if ($existingCourse) {
        // المادة موجودة مسبقًا → أرسل تنبيه لكن استمر في إضافة العروض الجديدة
        session()->flash('warning', "تنبيه: المادة موجودة مسبقًا، سيتم فقط إضافة العروض الجديدة للأقسام والفصول المختارة.");
        $course = $existingCourse; // نستخدم المادة الموجودة
    } else {
        // إنشاء المادة أو تحديثها
        $course = Course::updateOrCreate(
            ['id' => $request->course_id],
            [
                'name' => $request->name,
                'course_code' => $request->course_code,
                'has_practical' => $request->has_practical ? true : false,
                'prerequisite_course_id' => $request->prerequisite_course_id,
                'hours' => $request->hours,
                'units' => $request->units,
            ]
        );
    }

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
          CourseOffering::where('course_id', $course->id)->delete();

$sectionsData = json_decode($request->selectedSections, true);

foreach ($sectionsData as $sectionId => $sectionData) {
    if (!($sectionData['selected'] ?? false)) continue;

    foreach ($sectionData['semesters'] as $semesterNumber => $isSelected) {
        if (!$isSelected) continue;

        $semesters = Semester::where('semester_number', $semesterNumber)
            ->whereBetween('start_date', [$request->start_date, $request->end_date])
            ->get();

        foreach ($semesters as $semester) {
            CourseOffering::create([
                'course_id' => $course->id,
                'section_id' => $sectionId,
                'semester_id' => $semester->id,
            ]);
        }
    }
}
        }
        
    }
}
return redirect()->back()->with('success', 'تم إضافة الماد بنجاح');

    }

public function edit($id)
{
    // جلب المادة المطلوبة مع نسب التقييم والعروض
    $course = Course::with([ 'offerings.section', 'offerings.semester'])->findOrFail($id);

    // كل الأقسام والفصول والمواد (مثل index)
    $departments = Department::where('is_active', 1)->get();
    $sections = Section::where('is_active', 1)->get();

// جلب السيمسترات المفعلة فقط
$semesters = Semester::where('active', 1)
    ->get()
    ->map(function ($s) {
        return [
            'id'    => $s->id,
            'label' => $s->name
        ];
    });

// جلب المواد المرتبطة بالسيمسترات المفعلة فقط
$materials = CourseOffering::with(['course', 'section', 'semester'])
    ->whereHas('semester', function($q) {
        $q->where('active', 1);
    })
    ->get()
    ->map(function($offering){
        return [
            'id'            => $offering->id,
            'course_id'     => $offering->course->id,
            'section_id'    => $offering->section_id,
            'semester_id'   => $offering->semester_id,
            'status'        => $offering->status,
            'code'          => $offering->course->course_code,
            'name'          => $offering->course->name,
            'units'         => $offering->course->units,
            'hours'         => $offering->course->hours,
            'section_name'  => $offering->section?->name,
            'semester_name' => $offering->semester?->name,
        ];
    });


   // جلب الفصول المفعلة حاليًا
$currentSemesters = Semester::where('active', 1)->pluck('id');

// جلب المواد المرتبطة بالفصول المفعلة، مع استبعاد المادة نفسها
$allCourses = Course::where('id', '!=', $id)
    ->whereHas('offerings', function ($q) use ($currentSemesters) {
        $q->whereIn('semester_id', $currentSemesters);
    })
    ->get();


    // تمرير كل البيانات إلى نفس واجهة إنشاء المادة
    return view('courses.edit', compact(
        'course',       // المادة الحالية
        'allCourses',   // اختيار المادة السابقة
        'departments',
        'sections',
        'semesters',
        'materials'
    ));
}
public function update(Request $request, Course $course)
{
    $data = $request->all(); // هذا سيأخذ كل بيانات JSON

    $request->validate([
        'name' => 'required|string|max:255',
        'course_code' => ['required','string','max:50', Rule::unique('courses','course_code')->ignore($course->id)],
        'hours' => 'required|integer|min:0',
        'units' => 'required|integer|min:0',
        'has_practical' => 'nullable|boolean',
        'prerequisite_course_id' => 'nullable|exists:courses,id',

    ]);

    // تحديث المادة
    $course->update([
        'name' => $data['name'],
        'course_code' => $data['course_code'],
        'hours' => $data['hours'],
        'units' => $data['units'],
        'has_practical' => $data['has_practical'] ?? false,
        'prerequisite_course_id' => $data['prerequisite_course_id'] ?? null,
    ]);

 
    return response()->json([
        'success' => true,
        'message' => 'تم تحديث المادة بنجاح'
    ]);
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


    return response()->json(['message' => 'تم تحديث المادة بنجاح', 'course' => $course]);
}

}
