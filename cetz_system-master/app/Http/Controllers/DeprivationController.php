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
public function index()
{
    // جلب كل الفصول الدراسية المفعلّة
    $activeSemesterIds = Semester::where('active', 1)->pluck('id');

    // جلب الطلاب الذين لديهم enrollments غير محرومة ومرتبطين بأي فصل مفعل
    $studentsEnrollments = Enrollment::with(['student', 'courseOffering.course', 'courseOffering.semester'])
        ->where('is_deprived', 0)
        ->whereHas('courseOffering', function($q) use ($activeSemesterIds) {
            $q->whereIn('semester_id', $activeSemesterIds);
        })
        ->get();

    // جلب كل المحرومين الحاليين المرتبطين بأي فصل مفعل
    $deprivations = Deprivation::with([
        'enrollment.student', 
        'enrollment.courseOffering.course', 
        'enrollment.courseOffering.semester',
        'updatedBy'
    ])->whereHas('enrollment.courseOffering', function($q) use ($activeSemesterIds) {
        $q->whereIn('semester_id', $activeSemesterIds);
    })->get();

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
