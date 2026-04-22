<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\CourseOffering;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class SemesterController extends Controller
{
public function index()
{

    $package = DB::table('semesters')
        ->select(
            'degree_type',
            'term_type',
            'start_date',
            'end_date',
            'active',
            DB::raw('COUNT(*) as semesters_count'),
            DB::raw('MIN(created_at) as created_at')
        )
        ->groupBy(
            'degree_type',
            'term_type',
            'start_date',
            'end_date',
            'active'
        )
        ->orderBy('start_date', 'desc')
        ->get();
$activePackages = $package->where('active', 1);

    return view('semesters.index', compact('activePackages', 'package'));
}


public function activate(Request $request)
{
    $request->validate([
        'package' => 'required'
    ]);

    // فك القيم القادمة من select
    [$degree_type, $term_type, $start_date, $end_date] = explode('|', $request->package);

    // (اختياري لكن مُوصى به) إلغاء تفعيل جميع الحزم
    Semester::query()->update(['active' => 0]);

    // تفعيل الحزمة المختارة (كل فصولها)
    Semester::where('degree_type', $degree_type)
        ->where('term_type', $term_type)
        ->whereDate('start_date', $start_date)
        ->whereDate('end_date', $end_date)
        ->update(['active' => 1]);

    return redirect()->back()->with('success', 'تم تفعيل الحزمة بنجاح');
}


public function store(Request $request)
{
    $request->validate([
        'degree_type' => 'required|in:بكالوريوس,دبلوم',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after_or_equal:start_at',
        'term_type' => 'required|in:خريفي,ربيعي',
    ]);

    $degree_type = $request->degree_type;
    $start = $request->start_at;
    $end = $request->end_at;
    $term_type = $request->term_type;

    // تحقق من تداخل التواريخ
    $conflict = Semester::where('degree_type', $degree_type)
        ->where('end_date', '>=', $start)
        ->where('start_date', '<=', $end)
        ->exists();

    if ($conflict) {
        return redirect()->back()->withErrors(['start_at' => 'تاريخ البداية يتداخل مع حزمة موجودة.']);
    }

    // 1️⃣ أخذ السيمسترات النشطة
    $activeSemesters = Semester::where('active', 1)
        ->where('degree_type', $degree_type)
        ->get();

    // 2️⃣ أخذ course_offerings المرتبطة بالسيمسترات النشطة
    $coursesData = [];
    foreach ($activeSemesters as $semester) {
        $courses = CourseOffering::where('semester_id', $semester->id)->get();
        foreach ($courses as $course) {
            $coursesData[] = [
                'course_id' => $course->course_id,
                'section_id' => $course->section_id,
                'semester_id' => $semester->id,
                'status' => $course->status,
                'hours' => $course->hours,
                'units' => $course->units,
            ];
        }
    }

    // 3️⃣ إيقاف تفعيل كل السيمسترات النشطة
    Semester::where('active', 1)
        ->where('degree_type', $degree_type)
        ->update(['active' => 0]);

    // 4️⃣ إنشاء الحزمة الجديدة من السيمسترات
    if ($degree_type === 'بكالوريوس') {
        $names = ['العام','الثاني','الثالث','الرابع','الخامس','السادس','السابع','مشروع التخرج'];
    } else {
        $names = ['العام','الثاني','الثالث','الرابع','الخامس','مشروع التخرج'];
    }

    $newSemesters = [];
    foreach ($names as $i => $name) {
        $newSemesters[] = Semester::create([
            'name' => $name,
            'semester_number' => $i + 1,
            'degree_type' => $degree_type,
            'start_date' => $start,
            'end_date' => $end,
            'term_type' => $term_type,
            'active' => 1, // السيمستر الجديد نشط مباشرة
        ]);
    }

    // 5️⃣ إنشاء course_offerings جديدة مرتبطة بالسيمسترات الجديدة
// 5️⃣ إنشاء course_offerings جديدة مرتبطة بالسيمسترات الجديدة بنفس الاسم
foreach ($newSemesters as $newSemester) {
    $oldSemester = $activeSemesters->firstWhere('name', $newSemester->name);

if (!$oldSemester) {
    continue;
}

$matchingCourses = collect($coursesData)
    ->where('semester_id', $oldSemester->id);


    // إنشاء CourseOffering جديد فقط للكورسات المطابقة
    foreach ($matchingCourses as $course) {
        CourseOffering::create([
            'course_id' => $course['course_id'],
            'section_id' => $course['section_id'],
            'semester_id' => $newSemester->id,
            'status' => $course['status'],
            'hours' => $course['hours'] ?? 0,
            'units' => $course['units'] ?? 0,
        ]);
    }
}


    return redirect()->back()->with('success', 'تم إنشاء الحزمة والسيمسترات الجديدة مع الكورسات المرتبطة.');
}



public function updatePackage(Request $request)
{
    $request->validate([
        'degree_type' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'new_start_date' => 'required|date',
        'new_end_date' => 'required|date|after_or_equal:new_start_date',
    ]);

    $semesters = Semester::where('degree_type', $request->degree_type)
        ->where('start_date', $request->start_date)
        ->where('end_date', $request->end_date)
        ->get();

    foreach ($semesters as $semester) {
        $semester->update([
            'start_date' => $request->new_start_date,
            'end_date' => $request->new_end_date,
        ]);
    }

    return redirect()->back()->with('success', 'تم تعديل الحزمة بنجاح');
}

public function destroyPackage(Request $request)
{
    $request->validate([
        'degree_type' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ]);

    $hasEnrollments = Semester::where('degree_type', $request->degree_type)
        ->where('start_date', $request->start_date)
        ->where('end_date', $request->end_date)
        ->whereHas('courseOfferings', function($q){})
        ->exists();

    if ($hasEnrollments) {
        return redirect()->back()->withErrors(['delete' => 'لا يمكن حذف الحزمة لوجود طلاب مرتبطين بها.']);
    }

    Semester::where('degree_type', $request->degree_type)
        ->where('start_date', $request->start_date)
        ->where('end_date', $request->end_date)
        ->delete();

    return redirect()->back()->with('success', 'تم حذف الحزمة بنجاح.');
}

public function indexx()
{
    $semesters = DB::table('semesters')
        ->select(
            'degree_type',
            'term_type',
            'start_date',
            'end_date',
            'active',
            DB::raw('MIN(approved) as approved'), // أي فصل ضمن المجموعة غير معتمد = المجموعة غير معتمدة
            DB::raw('COUNT(*) as semesters_count')
        )
        ->groupBy(
            'degree_type',
            'term_type',
            'start_date',
            'end_date',
            'active'
        )
        ->orderBy('start_date', 'desc')
        ->get();

    return view('semesters.approved', compact('semesters'));
}

// دالة لتغيير حالة الاعتماد لكل مجموعة
public function toggleApprovalGroup(Request $request)
{
    $degree_type = $request->degree_type;
    $term_type = $request->term_type;
    $start_date = $request->start_date;
    $end_date = $request->end_date;

    // تحديث جميع السيمسترات ضمن هذه المجموعة
    DB::table('semesters')
        ->where('degree_type', $degree_type)
        ->where('term_type', $term_type)
        ->where('start_date', $start_date)
        ->where('end_date', $end_date)
        ->update([
            'approved' => DB::raw('NOT approved')
        ]);

    return redirect()->back()->with('success', 'تم تحديث حالة الاعتماد لكل السيمسترات في هذا الفصل');
}


}
