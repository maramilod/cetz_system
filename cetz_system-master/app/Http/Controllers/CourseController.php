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
use Illuminate\Support\Facades\DB;

use App\Models\CourseOffering;



class CourseController extends Controller
{
public function alternatives($id)
{
    $offering = CourseOffering::findOrFail($id);

  
   // إحضار جميع العروض الأخرى لنفس الشعبة والمادة ونفس السنة الدراسية
    $alternatives = CourseOffering::where('section_id', $offering->section_id)
                        ->where('course_id', $offering->course_id)
                        ->whereHas('semester', function($q) use ($offering) {
                            $q->where('start_date', $offering->semester->start_date)
                              ->where('end_date', $offering->semester->end_date);
                        })
                        ->where('id', '!=', $id)
                        ->get();
    $result = $alternatives->map(function($o){
        return [
            'id' => $o->id,
            'section_name' => $o->section->name ?? 'غير معروف',
            'semester_name' => $o->semester->label ?? $o->semester->name ?? 'غير معروف',
            'course_name' => $o->course->name ?? 'غير معروف',   // ✅ اسم المادة
            'course_code' => $o->course->course_code ?? '',     // ✅ رمز المادة
        ];
    });

    return response()->json($result);
}



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
   public function destroy(Request $request, $id)
{
    return DB::transaction(function () use ($request, $id) {

        $offering = CourseOffering::with('course','semester')
            ->findOrFail($id);

        $enrollments = Enrollment::where('course_offering_id', $offering->id);

        if ($enrollments->count() > 0) {

            if (!$request->new_offering_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'يوجد طلاب مسجلين. يرجى اختيار عرض بديل.'
                ], 422);
            }

            $newOffering = CourseOffering::with('course','semester')
                ->findOrFail($request->new_offering_id);

            // 🛑 تحقق مهم: نفس المادة فقط
            if ($offering->course_id !== $newOffering->course_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن نقل التسجيلات إلى مادة مختلفة.'
                ], 422);
            }

            // 🛑 منع تكرار تسجيل الطالب
            foreach ($enrollments->get() as $enrollment) {

                $alreadyExists = Enrollment::where('student_id', $enrollment->student_id)
                    ->where('course_offering_id', $newOffering->id)
                    ->exists();

                if ($alreadyExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'بعض الطلاب مسجلين مسبقاً في العرض الجديد.'
                    ], 422);
                }
            }

            // 🔁 نقل التسجيلات
            $enrollments->update([
                'course_offering_id' => $newOffering->id
            ]);
        }

        $offering->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم نقل التسجيلات وحذف العرض بنجاح'
        ]);
    });
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
    // 1️⃣ التحقق من صحة البيانات
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

    // 2️⃣ تحقق إذا المادة موجودة مسبقًا بنفس الكود (غير الـ course_id الحالي)
    $existingCourse = Course::where('course_code', $request->course_code)
        ->when($request->course_id, fn($q) => $q->where('id', '!=', $request->course_id))
        ->first();

    if ($existingCourse) {
        session()->flash('warning', "تنبيه: المادة موجودة مسبقًا، سيتم فقط إضافة العروض الجديدة للأقسام والفصول المختارة.");
        $course = $existingCourse;
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

    // 3️⃣ قراءة الشعب المختارة من JSON
    $sectionsData = json_decode($request->selectedSections, true);

    // حذف عروض المادة القديمة للفترة الحالية لتجنب التكرار
    CourseOffering::where('course_id', $course->id)
        ->whereHas('semester', function ($q) use ($request) {
            $q->whereBetween('start_date', [$request->start_date, $request->end_date]);
        })
        ->delete();

    // 4️⃣ التعامل مع كل شعبة
    foreach ($sectionsData as $sectionId => $sectionData) {
        if (!($sectionData['selected'] ?? false)) continue;

        $section = Section::find($sectionId);
        if (!$section) continue;

        // ✅ القسم العام: شعبة واحدة وسيمستر واحد باسم "عام"
        if ($section->department->is_general) {
            $semester = Semester::firstOrCreate([
                'semester_number' => 1,
                'term_type' => 'عام',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            CourseOffering::create([
                'course_id' => $course->id,
                'section_id' => $sectionId,
                'semester_id' => $semester->id,
            ]);

            continue; // تخطي باقي الكود للشعب العادية
        }

        // ✅ الشعب العادية
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

    // 5️⃣ إعادة التوجيه برسالة نجاح
    return redirect()->back()->with('success', 'تم إضافة المادة بنجاح');
}

public function edit(Course $course)
{
    $departments = Department::where('is_active', 1)->get();
    $sections    = Section::where('is_active', 1)->get();
    $courses     = Course::where('id', '!=', $course->id)->get();

    $startDates = Semester::select('start_date')
        ->distinct()
        ->orderBy('start_date')
        ->pluck('start_date');

    $endDates = Semester::select('end_date')
        ->whereNotNull('end_date')
        ->distinct()
        ->orderBy('end_date')
        ->pluck('end_date');

    // 🔹 جلب العروض الحالية للمادة
    $offerings = CourseOffering::with('semester')
        ->where('course_id', $course->id)
        ->get();

    // 🔹 تحويلها لصيغة selectedSections
    $selectedSections = [];

    foreach ($offerings as $offering) {
        $sectionId = $offering->section_id;
        $semesterNumber = $offering->semester->semester_number;

        if (!isset($selectedSections[$sectionId])) {
            $selectedSections[$sectionId] = [
                'selected' => true,
                'semesters' => []
            ];
        }

        $selectedSections[$sectionId]['semesters'][$semesterNumber] = true;
    }

    return view('courses.edit', compact(
        'course',
        'courses',
        'departments',
        'sections',
        'startDates',
        'endDates',
        'selectedSections'
    ));
}

public function update(Request $request, $id)
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

    $course = Course::findOrFail($id);

    // 1️⃣ تحديث بيانات المادة
    $course->update([
        'name' => $request->name,
        'course_code' => $request->course_code,
        'hours' => $request->hours,
        'units' => $request->units,
        'has_practical' => $request->has_practical ? true : false,
        'prerequisite_course_id' => $request->prerequisite_course_id,
    ]);

    $sectionsData = json_decode($request->selectedSections, true);

    // 2️⃣ تحديث عروض المادة ضمن الفترة المحددة
    foreach ($sectionsData as $sectionId => $sectionData) {
        if (!($sectionData['selected'] ?? false)) continue;

        $section = Section::find($sectionId);
        if (!$section) continue;

        // القسم العام: شعبة واحدة وسيمستر واحد باسم "عام"
        if ($section->department->is_general) {
            $semester = Semester::updateOrCreate(
    [
        'semester_number' => 1,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ],
    [
        'term_type' => 'ربيعي',
        'name' => 'العام',
        'degree_type' => 'بكالوريوس'
    ]
);


            // تحديث أو إنشاء CourseOffering للقسم العام
            CourseOffering::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'section_id' => $sectionId,
                ],
                [
                    'semester_id' => $semester->id
                ]
            );

            continue;
        }

        // الشعب العادية: تحديث كل السيمسترات المختارة
        foreach ($sectionData['semesters'] as $semesterNumber => $isSelected) {
            if (!$isSelected) continue;

            $semesters = Semester::where('semester_number', $semesterNumber)
                ->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->get();

            foreach ($semesters as $semester) {
                CourseOffering::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'section_id' => $sectionId,
                        'semester_id' => $semester->id,
                    ],
                    [] // لا حاجة لتحديث أي عمود آخر، فقط نضمن وجود العرض
                );
            }
        }
    }

    return redirect()->back()->with('success', 'تم تعديل المادة بنجاح');
}

public function updateBasic(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'course_code' => 'required|string|max:50',
        'hours' => 'required|integer|min:0',
        'units' => 'required|integer|min:0',
        'has_practical' => 'nullable|boolean',
        'prerequisite_course_id' => 'nullable|exists:courses,id',
    ]);

    $course = Course::findOrFail($id);

    $course->update([
        'name' => $request->name,
        'course_code' => $request->course_code,
        'hours' => $request->hours,
        'units' => $request->units,
        'has_practical' => $request->has_practical ? true : false,
        'prerequisite_course_id' => $request->prerequisite_course_id,
    ]);

    return redirect()->back()->with('success', 'تم تحديث البيانات الأساسية فقط بنجاح');
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

public function updateFull(Request $request, Course $course)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'course_code' => ['required','string','max:50', Rule::unique('courses','course_code')->ignore($course->id)],
        'hours' => 'required|integer|min:0',
        'units' => 'required|integer|min:0',
        'has_practical' => 'nullable|boolean',
        'prerequisite_course_id' => 'nullable|exists:courses,id',
        'selectedSections' => 'required|json',
        'start_date' => 'required|date',
        'end_date'   => 'required|date|after_or_equal:start_date',
    ]);

    // 1️⃣ تحديث بيانات المادة
    $course->update([
        'name' => $request->name,
        'course_code' => $request->course_code,
        'hours' => $request->hours,
        'units' => $request->units,
        'has_practical' => $request->has_practical ?? false,
        'prerequisite_course_id' => $request->prerequisite_course_id ?? null,
    ]);

    // 2️⃣ حذف عروض المادة السابقة في الفترة المحددة (أو حسب اختيارك)
    CourseOffering::where('course_id', $course->id)
        ->whereHas('semester', fn($q) => $q->whereBetween('start_date', [$request->start_date, $request->end_date]))
        ->delete();

    // 3️⃣ إضافة العروض الجديدة لكل قسم وفصل
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

    return redirect()->back()->with('success', 'تم تحديث المادة وجميع العروض بنجاح');
}

public function updateSemesterAssignments(Request $request, Course $course)
{
    // تحقق من البيانات
    $request->validate([
        'semester_id' => 'required|exists:semesters,id',
        'sections'    => 'required|array',
    ]);

    $semesterId = $request->semester_id;
    $sectionsData = $request->sections;

    // حذف أي عروض سابقة لهذه المادة في السيمستر المحدد
    CourseOffering::where('course_id', $course->id)
        ->where('semester_id', $semesterId)
        ->delete();

    // إنشاء العروض الجديدة
    foreach ($sectionsData as $sectionId => $section) {
        if (!($section['selected'] ?? false)) {
            continue; // إذا لم يتم اختيار الشعبة، تجاهلها
        }

        foreach ($section['semesters'] as $semNum => $isSelected) {
            if (!$isSelected) continue;

            // إنشاء العرض
            CourseOffering::create([
                'course_id'   => $course->id,
                'section_id'  => $sectionId,
                'semester_id' => $semesterId,
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث توزيع المادة بنجاح للفصل الحالي'
    ]);
}


}
