<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class GraduatedStudentsController extends Controller
{
public function index(Request $request)
{
    $query = Student::with(['section.department']) // جلب الشعبة والقسم مع الطالب
                    ->where('current_status', 'متخرج');

    // فلتر البحث
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('student_number', 'like', "%{$search}%");
        });
    }

    $students = $query->orderBy('full_name')->paginate(20);

    return view('students.graduated', compact('students'));
}

}
