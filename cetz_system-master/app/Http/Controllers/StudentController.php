<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;


class StudentController extends Controller
{
     // عرض جميع الطلاب
    public function index()
    {
        $students = Student::with('department')->get();
        return view('students.index', compact('students'));
    }
       // عرض صفحة تجديد القيد / إيقاف القيد
    public function enrollmentStop()
    {
        $students = Student::all()->toArray();
        return view('registration.enrollment-stop', compact('students'));
    }
    public function updateStatus(Request $request, $id)
{
    $student = Student::findOrFail($id);
    $student->current_status = $request->status;
    $student->save();

    return response()->json(['success' => true]);
}

    // صفحة إنشاء الطالب
 public function create()
{
    $departments = Department::where('is_active', 1)->get(); // فقط الأقسام النشطة
    $sections = Section::all(); 
    return view('students.create', compact('departments', 'sections'));
}


public function excel()
{
    $students = Student::with('department')->get();

    // سنوات التسجيل من قاعدة البيانات
    $years = Student::select('registration_year')->distinct()->orderBy('registration_year', 'desc')->pluck('registration_year');

    // الأقسام
    $departments = Department::pluck('name');

    $studentsForJs = $students->map(function ($s) {
        return [
            'id' => $s->id,
            'full_name' => $s->full_name,
            'mother_name' => $s->mother_name,
            'nationality' => $s->nationality,
            'gender' => $s->gender,
            'year' => $s->registration_year,
            'semester' => $s->academic_term,
            'student_number' => $s->student_number,
            'manual_number' => $s->manual_number,
            'national_id' => $s->national_id,
            'passport_number' => $s->passport_number,
            'bank_name' => $s->bank_name,
            'bank_account' => $s->account_number,
            'birth_date' => $s->dob,
            'family_record' => $s->family_record,
            'department' => $s->department?->name,
        ];
    });

    return view('students.excel', compact('studentsForJs', 'years', 'departments'));
}


    public function show(Student $student)
{
    // إذا كان هناك علاقة department
    $student->load('department');
    return view('students.show', compact('student'));
}
// حفظ الطالب
public function store(Request $request)
{
    
    $request->validate([
        'full_name'          => 'required|string|max:255',
        'nationality'       => 'required|string|max:100',
        'gender' => 'required|in:ذكر,انثى',
        'department_id'     => 'required|exists:departments,id',
        'registration_year' => 'required|digits:4|integer',
        'academic_term'     => 'nullable|string|max:50',
        'student_number'    => 'nullable|string|max:50|unique:students,student_number',
        'manual_number'     => 'nullable|string|max:50',
        'national_id'       => 'nullable|string|max:50|unique:students,national_id',
        'passport_number'   => 'nullable|string|max:50',
        'dob'               => 'required|date',
        'bank_name'         => 'nullable|string|max:100',
        'account_number'    => 'nullable|string|max:100',
        'mother_name'       => 'nullable|string|max:255',
        'family_record'     => 'nullable|string|max:50',
        'photo'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'section_id' => 'nullable|exists:sections,id',
    ]);

    $student = new Student();
$department = Department::findOrFail($request->department_id);

    // تعبئة الحقول النصية
    $student->full_name         = $request->full_name;
    $student->nationality       = $request->nationality;
    $student->gender            = $request->gender;
    $student->department_id = $department->id;
    $student->registration_year = $request->registration_year;
    $student->academic_term     = $request->academic_term;
    $student->student_number    = $request->student_number;
    $student->manual_number     = $request->manual_number;
    $student->national_id       = $request->national_id;
    $student->passport_number   = $request->passport_number;
    $student->dob               = $request->dob;
    $student->bank_name         = $request->bank_name;
    $student->account_number    = $request->account_number;
    $student->mother_name       = $request->mother_name;
    $student->family_record     = $request->family_record;
$student->section_id = ($department->is_general) ? 5 : $request->section_id;

    // رفع الصورة
if ($request->hasFile('photo')) {
    $filename = time().'_'.$request->file('photo')->getClientOriginalName();
    $request->file('photo')->storeAs('students', $filename, 'public'); // public disk
    $student->photo = $filename;
}

    $student->save();

    return redirect()
        ->route('students.create')
        ->with('success', 'تم إضافة الطالب بنجاح');
}
public function destroy($id)
{
    $student = Student::findOrFail($id);

    // حذف الصورة إن وُجدت
    if ($student->photo && Storage::disk('public')->exists('students/' . $student->photo)) {
        Storage::disk('public')->delete('students/' . $student->photo);
    }

    // حذف الطالب
    $student->delete();

    return redirect()->route('students.index')
        ->with('success', 'تم حذف الطالب بنجاح');
}
public function edit(Student $student)
{
    $departments = Department::all();
    $sections = Section::all();

    return view('students.edit', compact('student', 'departments', 'sections'));
}

public function update(Request $request, Student $student)
{
        $request->validate([
        'full_name'          => 'required|string|max:255',
        'nationality'       => 'required|string|max:100',
        'gender' => 'required|in:ذكر,انثى',
        'department_id'     => 'required|exists:departments,id',
        'registration_year' => 'required|digits:4|integer',
        'academic_term'     => 'nullable|string|max:50',
        'student_number'    => 'nullable|string|max:50|unique:students,student_number',
        'manual_number'     => 'nullable|string|max:50',
        'national_id'       => 'nullable|string|max:50|unique:students,national_id',
        'passport_number'   => 'nullable|string|max:50',
        'dob'               => 'required|date',
        'bank_name'         => 'nullable|string|max:100',
        'account_number'    => 'nullable|string|max:100',
        'mother_name'       => 'nullable|string|max:255',
        'family_record'     => 'nullable|string|max:50',
        'photo'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'section_id' => 'nullable|exists:sections,id',
    ]);

    /* تحديث الصورة إن وُجدت */
    if ($request->hasFile('photo')) {

        // حذف الصورة القديمة
        if ($student->photo && Storage::disk('public')->exists('students/'.$student->photo)) {
            Storage::disk('public')->delete('students/'.$student->photo);
        }

        $filename = time().'_'.$request->photo->getClientOriginalName();
        $request->photo->storeAs('students', $filename, 'public');
        $data['photo'] = $filename;
    }

    $student->update($data);

    return redirect()
        ->route('students.show', $student->id)
        ->with('success', 'تم تحديث بيانات الطالب بنجاح');
}

public function createCertificate()
{
    // جلب كل الطلبة
    $students = Student::select('student_number', 'full_name', 'department_id', 'national_id')
                ->with('department') // للحصول على اسم القسم
                ->get()
                ->map(function($s){
                    return [
                        'number' => $s->student_number,
                        'name' => $s->full_name,
                        'department' => $s->department?->name ?? '',
                        'nationalId' => $s->national_id
                    ];
                });

    $institute = [
        'name' => 'كلية التقنية الهندسية',
        'address' => 'زوارة - ليبيا',
        'phone' => '+218 21 1234567'
    ];

    return view('registration.student-certificate', compact('students', 'institute'));
}


}
