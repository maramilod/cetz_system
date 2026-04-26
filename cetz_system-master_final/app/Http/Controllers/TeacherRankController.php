<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherRank;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TeacherEmploymentStatus;
;

class TeacherRankController extends Controller
{
    public function store(Request $request, Teacher $teacher)
{
    $request->validate([
        'academic_rank_id'     => 'required|exists:academic_ranks,id',
        'employment_status_id' => 'required|exists:employment_statuses,id',
        'from_date'            => 'required|date',
    ]);

    /** =======================
     * 1️⃣ إنهاء الرتبة الحالية
     ======================= */
    TeacherRank::where('teacher_id', $teacher->id)
        ->whereNull('to_date')
        ->update([
            'to_date' => Carbon::parse($request->from_date)->subDay()
        ]);

    /** =======================
     * 2️⃣ إنشاء رتبة جديدة
     ======================= */
    TeacherRank::create([
        'teacher_id'       => $teacher->id,
        'academic_rank_id' => $request->academic_rank_id,
        'from_date'        => $request->from_date,
        'to_date'          => null,
    ]);

    /** =======================
     * 3️⃣ إنهاء الوضع الوظيفي الحالي
     ======================= */
    TeacherEmploymentStatus::where('teacher_id', $teacher->id)
        ->whereNull('to_date')
        ->update([
            'to_date' => Carbon::parse($request->from_date)->subDay()
        ]);

    /** =======================
     * 4️⃣ إنشاء وضع وظيفي جديد
     ======================= */
    TeacherEmploymentStatus::create([
        'teacher_id'            => $teacher->id,
        'employment_status_id'  => $request->employment_status_id,
        'from_date'             => $request->from_date,
        'to_date'               => null,
    ]);

    return redirect()
        ->route('teachers.index')
        ->with('success', 'تمت ترقية الأستاذ وتحديث وضعه الوظيفي بنجاح');
}
}
