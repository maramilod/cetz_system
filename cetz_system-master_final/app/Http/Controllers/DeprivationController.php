<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deprivation;
use App\Models\Enrollment;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;

class DeprivationController extends Controller
{
    /**
     * عرض صفحة المحرومين + قائمة الطلاب.
     */
public function index(Request $request)
{
    $activeSemesterIds = Semester::where('active', 1)->pluck('id');

    // 👇 المحرومين دايمًا يطلعوا
    $deprivations = Deprivation::with([
        'enrollment.student', 
        'enrollment.courseOffering.course', 
        'enrollment.courseOffering.semester',
        'updatedBy'
    ])
    ->whereHas('enrollment.courseOffering', function($q) use ($activeSemesterIds) {
        $q->whereIn('semester_id', $activeSemesterIds);
    })
    ->get();

    // 👇 الطلاب فاضي بالبداية
    $studentsEnrollments = collect();

    // 👇 يتحملوا بس لو فيه فلتر
    if ($request->filled('search') || $request->filled('course')) {

        $studentsEnrollments = Enrollment::with(['student', 'courseOffering.course', 'courseOffering.semester'])
            ->where('is_deprived', 0)
            ->whereHas('courseOffering', function($q) use ($activeSemesterIds) {
                $q->whereIn('semester_id', $activeSemesterIds);
            })

            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', function ($qq) use ($request) {
                    $qq->where('full_name', 'like', '%' . $request->search . '%')
                       ->orWhere('student_number', 'like', '%' . $request->search . '%')
                       ->orWhere('manual_number', 'like', '%' . $request->search . '%');
                });
            })

            ->when($request->course, function ($q) use ($request) {
                $q->whereHas('courseOffering.course', function ($qq) use ($request) {
                    $qq->where('name', 'like', '%' . $request->course . '%');
                });
            })

            ->paginate(20);
    }

    return view('deprivations.index', compact('studentsEnrollments', 'deprivations'));
}




    /**
     * إضافة محروم جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'reason' => 'required|string|max:255',
        ]);

        $enrollment = Enrollment::findOrFail($request->enrollment_id);

        // تغيير حالة enrollment إلى محروم
        $enrollment->is_deprived = 1;
        $enrollment->save();

        // إضافة سجل في جدول المحرومين
        $deprivation = Deprivation::create([
            'enrollment_id' => $enrollment->id,
            'reason' => $request->reason,
            'updated_by' => Auth::id(), // المستخدم الحالي
        ]);

        return redirect()->route('deprivations.index')
                         ->with('success', 'تم إضافة المحروم بنجاح.');
    }

    /**
     * حذف المحروم وإعادة حالة enrollment
     */
    public function destroy($id)
    {
        $deprivation = Deprivation::findOrFail($id);
        $enrollment = $deprivation->enrollment;

        // إعادة الحالة
        if ($enrollment) {
            $enrollment->is_deprived = 0;
            $enrollment->save();
        }

        $deprivation->delete();

        return redirect()->route('deprivations.index')
                         ->with('success', 'تم حذف المحروم بنجاح.');
    }
}
