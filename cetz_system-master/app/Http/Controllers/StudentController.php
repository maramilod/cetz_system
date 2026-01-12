<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Department;
use App\Models\Institution;

use App\Models\Section;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{


public function setGraduated(Student $student)
{
    $student->current_status = 'متخرج';
    $student->save();

    return redirect()->back()->with('success', 'تم تعيين الطالب كخريج بنجاح.');
}


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
    DB::transaction(function () use ($request, $id) {

        // 1️⃣ تحديث حالة الطالب
        $student = Student::findOrFail($id);
        $student->current_status = $request->status;
        $student->save();
    
    });

    return response()->json([
        'success' => true,
        'message' => 'تم تجديد القيد'
    ]);
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
    public function freezeAll()
{
    Student::where('current_status', 'تم التجديد')
        ->update(['current_status' => 'جاهز للتجديد']);

    return back()->with('success', 'تم إيقاف قيد جميع الطلبة بنجاح.');
}
public function studentEnrollments(Request $request)
{
    $studentNumber = $request->input('student_number');

    // جلب الطالب
    $student = Student::where('student_number', $studentNumber)
        ->orWhere('manual_number', $studentNumber)
        ->first();

    if (!$student) {
        return back()->with('error', 'الطالب غير موجود');
    }

    // جلب الانرولمنتس مع الكورس والفصل والدرجات
    $enrollments = Enrollment::with([
        'courseOffering.course',
        'courseOffering.semester',
        'grade'
    ])
    ->where('student_id', $student->id)
    ->whereHas('courseOffering.semester')
    ->get()
    ->sortByDesc(fn ($e) => $e->courseOffering->semester->start_date); // ترتيب تنازلي حسب تاريخ البداية

    // تجميع الانرولمنت حسب السنة + term_type
   $semesterEnrollments = $enrollments
    ->groupBy(function($e) {
        $semester = $e->courseOffering->semester;
        if (!$semester) return 0;
        return $semester->term_type . '_' . date('Y', strtotime($semester->start_date)) . '_' . $semester->id;
    })
    ->map(function($enrollmentsInSemester) {
        $semester = $enrollmentsInSemester->first()->courseOffering->semester;

        $enrollmentsData = $enrollmentsInSemester->map(function($e) {
            return [
                'id'          => $e->id,
                'course_name' => $e->courseOffering->course->name,
                'course_code' => $e->courseOffering->course->course_code,
                'units'       => $e->courseOffering->course->units,
                'hours'       => $e->courseOffering->course->hours,
                'status'      => $e->status,
                'attempt'     => $e->attempt,
                'total'       => $e->grade?->total,
                'grade'       => $e->grade,
            ];
        });

        // حساب المعدل التراكمي للفصل
  $totalPoints = $enrollmentsData->sum(function($e) {
    return $e['total'] ?? 0; // اجمع درجات المواد
});

$subjectsCount = $enrollmentsData->count(); // عدد المواد

$gpa = $subjectsCount ? round(($totalPoints / $subjectsCount), 2) : null;

        return [
            'semester_name' => $semester?->name ?? 'غير محدد',
            'semester_id'   => $semester?->id,
            'term_type'     => $semester?->term_type,
            'year'          => date('Y', strtotime($semester->start_date)),
            'start_date'    => $semester?->start_date,
            'end_date'      => $semester?->end_date,
            'active'        => $semester?->active ?? false,
            'enrollments'   => $enrollmentsData,
            'total_units'   => $enrollmentsData->sum('units'),
            'total_hours'   => $enrollmentsData->sum('hours'),
            'gpa'           => $gpa, // المعدل التراكمي للفصل
        ];
    })
    ->sortBy(fn($s) => strtotime($s['end_date']));


    // ترتيب المجموعات حسب السنة تنازلي ثم رقم الفصل
   // $semesterEnrollments = $semesterEnrollments->sortByDesc(fn($s) => $s['year'] * 100 + $s['semester_number']);

    return view('students.enrollments', compact('student', 'semesterEnrollments'));
}




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
$student->section_id =  $request->section_id;

    // رفع الصورة
if ($request->hasFile('photo')) {
    $filename = time().'_'.$request->file('photo')->getClientOriginalName();
    $request->file('photo')->storeAs('students', $filename, 'public'); // public disk
    $student->photo = $filename;
}

    // ======== حساب الرقم الجامعي تلقائياً ========
     $institution = Institution::first(); // نفترض جهة واحدة فقط
    $collegeOrInstitute = ($institution->type == 'كلية') ? '1' : '2'; // 1=كلية، 2=معهد
 // افتراض وجود is_institute في جدول الأقسام

    // 2. رقم تسلسل المؤسسة
    $instituteNumber = '09'; 

    // 3. السنة التقويمية (سنتان آخرتان)
    $year = substr($request->registration_year, -2);

    // 4. الكود حسب الفصل الدراسي
    $termMap = [
        'fall_n'  => 1,
        'spring_n'=> 2,
        'fall_o'  => 3,
        'spring_o'=> 4,
        'added'   => 5,
        'full_n'  => 6,
        'full_o'  => 7,
    ];
    $termCode = $termMap[$request->academic_term] ?? 0;

    // 5. رقم تسلسلي للطالب حسب تسجيله في نفس القسم والسنة والفصل
    $count = Student::where('registration_year', $request->registration_year)
        ->where('academic_term', $request->academic_term)
        ->count() + 1; // الطالب الحالي

    $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

    // دمج كل شيء
    $student->student_number = $collegeOrInstitute . $instituteNumber . $year . $termCode . $sequence;


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
        'nationality'        => 'required|string|max:100',
        'gender'             => 'required|in:ذكر,انثى',
        'department_id'      => 'required|exists:departments,id',
        'registration_year'  => 'required|digits:4|integer',
        'academic_term'      => 'nullable|string|max:50',
        'student_number'     => [
            'nullable', 'string', 'max:50',
            Rule::unique('students', 'student_number')->ignore($student->id),
        ],
        'manual_number'      => 'nullable|string|max:50',
        'national_id'        => [
            'nullable', 'string', 'max:50',
            Rule::unique('students', 'national_id')->ignore($student->id),
        ],
        'passport_number'    => 'nullable|string|max:50',
        'dob'                => 'required|date',
        'bank_name'          => 'nullable|string|max:100',
        'account_number'     => 'nullable|string|max:100',
        'mother_name'        => 'nullable|string|max:255',
        'family_record'      => 'nullable|string|max:50',
        'photo'              => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
 'section_id'         => [
        'nullable',
        'exists:sections,id',
        // شرط مخصص: إذا department = 5 و section = 5
        function($attribute, $value, $fail) use ($request) {
            if ($request->department_id == 5 && $value == 5) {
                // هنا يمكنك وضع الشرط الذي تريدين التحقق منه
                // مثال: إجبارياً أن يكون الاسم الكامل يحتوي على "X"
                if (empty($request->full_name)) {
                    $fail('بالنسبة للقسم 5 والشعبة 5، يجب تعبئة الاسم الكامل.');
                }
    }
    }
       ],
    ]);

    // جمع البيانات من الطلب
    $data = $request->only([
        'full_name',
        'nationality',
        'gender',
        'department_id',
        'registration_year',
        'academic_term',
        'student_number',
        'manual_number',
        'national_id',
        'passport_number',
        'dob',
        'bank_name',
        'account_number',
        'mother_name',
        'family_record',
        'section_id',
    ]);

    // معالجة الصورة
    if ($request->hasFile('photo')) {
        // حذف الصورة القديمة إذا كانت موجودة
        if ($student->photo && Storage::disk('public')->exists('students/'.$student->photo)) {
            Storage::disk('public')->delete('students/'.$student->photo);
        }

        $filename = time().'_'.$request->photo->getClientOriginalName();
        $request->photo->storeAs('students', $filename, 'public');
        $data['photo'] = $filename;
    }

    // تحديث الطالب
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
