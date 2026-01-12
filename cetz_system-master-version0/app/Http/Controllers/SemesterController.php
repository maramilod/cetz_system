<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
public function index()
{
    $today = date('Y-m-d');

    // جلب جميع السيمسترات التي لم تنتهي بعد
    $semesters = Semester::where('end_date', '>=', $today)
        ->orderBy('id', 'asc')
        ->get();

    // تجميع السيمسترات حسب الحزمة: degree_type + start_date
    $packages = $semesters->groupBy(function($item){
        return $item->degree_type . '|' . $item->start_date;
    });

    return view('semesters.index', compact('packages'));
}



public function store(Request $request)
{
    $request->validate([
        'degree_type' => 'required|in:بكالوريوس,دبلوم',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after_or_equal:start_at',
        'term_type' => 'required|in:خريفي,ربيعي', // تحقق من اختيار المستخدم
    ]);

    $degree_type = $request->degree_type;
    $start = $request->start_at;
    $end = $request->end_at;
    $term_type = $request->term_type; // النوع المختار من المستخدم

    // تحقق من تداخل التواريخ مع أي سيمستر موجود في نفس البرنامج
    $conflict = Semester::where('degree_type', $degree_type)
        ->where('end_date', '>=', $start)
        ->where('start_date', '<=', $end)
        ->exists();

    if ($conflict) {
        return redirect()->back()->withErrors(['start_at' => 'تاريخ البداية يتداخل مع حزمة موجودة.']);
    }

    // أسماء السيمسترات حسب البرنامج
    if ($degree_type === 'بكالوريوس') {
        $names = ['العام','الثاني','الثالث','الرابع','الخامس','السادس','السابع','مشروع التخرج'];
    } else {
        $names = ['العام','الثاني','الثالث','الرابع','الخامس','مشروع التخرج'];
    }

    foreach ($names as $i => $name) {
        Semester::create([
            'name' => $name,
            'semester_number' => $i + 1,
            'degree_type' => $degree_type,
            'start_date' => $start,
            'end_date' => $end,
            'term_type' => $term_type, // حفظ كما اختاره الموظف
        ]);
    }

    return redirect()->back()->with('success', 'تم إضافة الحزمة بنجاح.');
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


}
