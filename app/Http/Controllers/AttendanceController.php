<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseOffering;
use App\Models\Semester;

class AttendanceController extends Controller
{
    // 1. عرض الصفحة الأساسية (فارغة وسريعة جداً)
    public function index()
    {
        return view('registration.attendance-form');
    }

public function search(Request $request)
{
    $term = $request->get('term');

    if (empty($term) || mb_strlen($term) < 3) {
        return response()->json([]);
    }

    $activeSemesterIds = Semester::where('active', 1)->pluck('id')->toArray();

    if (empty($activeSemesterIds)) {
        return response()->json([]);
    }

    // تفكيك نص البحث إلى كلمات منفصلة (بناءً على الفراغات)
    // مثلاً: "رياضيات حاسوب" تصبح مصفوفة تحتوي على ["رياضيات", "حاسوب"]
    $keywords = array_filter(explode(' ', $term));

    $courseOfferings = CourseOffering::with([
        'course',
        'section',
        'semester',
        'teachingAssignments.teacher',
        'enrollments' => function($q) {
            $q->where('status', 'in_progress')->with('student.department');
        }
    ])
    ->whereIn('semester_id', $activeSemesterIds);

    // تطبيق فلترة مركبة: يجب أن تطابق النتيجة "كل الكلمات المكتوبة" عبر العلاقات
    foreach ($keywords as $keyword) {
        $courseOfferings->where(function($query) use ($keyword) {
            $query->whereHas('course', function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%");
            })
            ->orWhereHas('section', function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%");
            })
            ->orWhereHas('teachingAssignments.teacher', function($q) use ($keyword) {
                $q->where('full_name', 'LIKE', "%{$keyword}%");
            });
        });
    }

    $results = $courseOfferings->get();

    // تحويل البيانات وصياغتها إلى مصفوفة JSON خفيفة الوزن
    $attendanceData = $results->map(function($co) {
        $teachers = $co->teachingAssignments->map(function($ta) {
            return [
                'name' => $ta->teacher->full_name ?? 'غير محدد',
                'role' => $ta->role ?? 'غير محدد',
            ];
        });

        $students = $co->enrollments->map(function($enrollment) {
            $student = $enrollment->student;
            return [
                'student_number'  => $student?->manual_number ?? $student?->student_number ?? '-',
                'name'            => $student?->full_name ?? '-',
                'department_name' => $student?->department->name ?? '-',
            ];
        });

        return [
            'course_name'   => $co->course->name,
            'section_name'  => $co->section->name,
            'teachers'      => $teachers,
            'semester_year' => $co->semester->start_date ? date('Y', strtotime($co->semester->start_date)) : '-',
            'semester_type' => $co->semester->term_type ?? '-',
            'students'      => $students,
        ];
    });

    return response()->json($attendanceData);
}
public function suggestions(Request $request)
{
    $term = $request->get('term');

    if (empty($term) || mb_strlen($term) < 1) {
        return response()->json([]);
    }

    $activeSemesterIds = Semester::where('active', 1)->pluck('id')->toArray();

    // جلب المواد المطابقة بناءً على الاسم أو القسم/الشعبة
    $suggestions = CourseOffering::with(['course', 'section'])
        ->whereIn('semester_id', $activeSemesterIds)
        ->where(function($query) use ($term) {
            $query->whereHas('course', function($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%");
            })
            ->orWhereHas('section', function($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%");
            });
        })
        ->limit(10) // تحديد عدد المقترحات بـ 10 فقط لتكون سريعة جداً
        ->get()
        ->map(function($co) {
            return [
                // النص الذي سيظهر للأستاذ في القائمة: "رياضيات 1 - شعبة حاسوب"
                'label' => $co->course->name . ' - ' . $co->section->name,
                // القيمة الفعلية للبحث عند الاختيار
                'value' => $co->course->name . ' ' . $co->section->name
            ];
        });

    return response()->json($suggestions);
}
}