<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;

class UiController extends Controller
{
    public function dashboard()
    {
        // بيانات تجريبية
        $stats = [
            'students' => 1240,
            'graduates' => 320,
            'teachers' => 82,
            'courses' => 148
        ];

        $latest = [
            ['student_number'=>'2025-001','name'=>'آمنة علي','department'=>'هندسة كهربائية','status'=>'active'],
            ['student_number'=>'2025-010','name'=>'محمد عمر','department'=>'علوم حاسوب','status'=>'active'],
            ['student_number'=>'2024-075','name'=>'سارة محمود','department'=>'هندسة ميكانيك','status'=>'graduated'],
        ];

        return view('ui.dashboard', compact('stats','latest'));
    }

    public function studentsIndex()
    {
        $departments = [
            ['id'=>1,'name'=>'هندسة كهربائية'],
            ['id'=>2,'name'=>'علوم حاسوب'],
            ['id'=>3,'name'=>'هندسة ميكانيك'],
        ];

        // بيانات موسعة لعرضها في الجدول (مطابقة للأعمدة في الصور)
        $students = [
            (object)[
                'student_number'=>'1091252001',
                'name'=>'آمنة علي',
                'department'=>'القسم العام',
                'nationality'=>'ليبيا',
                'gender'=>'أنثى',
                'dob'=>'2005-01-15',
                'passport'=>'P1234567',
                'status'=>'active',
                'photo'=>null
            ],
            (object)[
                'student_number'=>'1091252002',
                'name'=>'محمد عمر',
                'department'=>'القسم العام',
                'nationality'=>'ليبيا',
                'gender'=>'ذكر',
                'dob'=>'2004-12-10',
                'passport'=>'P9876543',
                'status'=>'active',
                'photo'=>null
            ],
            (object)[
                'student_number'=>'1091252003',
                'name'=>'سارة محمود',
                'department'=>'القسم العام',
                'nationality'=>'ليبيا',
                'gender'=>'أنثى',
                'dob'=>'2006-03-06',
                'passport'=>'P1122334',
                'status'=>'graduated',
                'photo'=>null
            ],
        ];

        // دمج أي تعديلات مؤقتة مخزنة في الجلسة (حسب id المبني على الفهرس idx+1)
        $updates = session('student_updates', []);
        foreach ($updates as $id => $data) {
            $idx = (int)$id - 1;
            if (isset($students[$idx]) && is_array($data)) {
                foreach ($data as $k => $v) {
                    $students[$idx]->{$k} = $v;
                }
            }
        }

        return view('ui.students.index', compact('students','departments'));
    }

    public function studentsCreate()
    {
        $departments = [
            (object)['id'=>1,'name'=>'هندسة كهربائية'],
            (object)['id'=>2,'name'=>'علوم حاسوب'],
            (object)['id'=>3,'name'=>'هندسة ميكانيك'],
        ];
        return view('ui.students.create', compact('departments'));
    }

    public function studentsShow($id)
{
    // البيانات التجريبية، لاحظ نختار الطالب حسب الـ id
    $students = [
        1 => (object)[
            'student_number'=>'2025-001',
            'name'=>'آمنة علي',
            'nationality'=>'ليبيا',
            'gender'=>'أنثى',
            'department'=>'هندسة كهربائية',
            'enrollment_year'=>'2025',
            'semester'=>'الاول',
            'manual_number'=>'001',
            'national_id'=>'LY123456',
            'passport'=>'P1234567',
            'dob'=>'2005-01-15',
            'bank'=>'بنك ليبيا',
            'account_number'=>'1234567890',
            'mother_name'=>'فاطمة علي',
            'registry_book'=>'1234'
        ],
        2 => (object)[
            'student_number'=>'2025-010',
            'name'=>'محمد عمر',
            'nationality'=>'مصر',
            'gender'=>'ذكر',
            'department'=>'علوم حاسوب',
            'enrollment_year'=>'2025',
            'semester'=>'الاول',
            'manual_number'=>'010',
            'national_id'=>'EG987654',
            'passport'=>'P9876543',
            'dob'=>'2004-12-10',
            'bank'=>'بنك مصر',
            'account_number'=>'9876543210',
            'mother_name'=>'أمينة محمود',
            'registry_book'=>'5678'
        ]
        // ... تضيف باقي الطلاب
    ];

    $student = $students[$id] ?? null;

    if (!$student) {
        return redirect()->route('students.index')->with('error', 'الطالب غير موجود');
    }

    return view('ui.students.show', compact('student'));
}

    public function studentsEdit($id)
    {
        // نفس مصدر البيانات المستخدم في studentsShow للتناسق
        $students = [
            1 => (object)[
                'student_number'=>'2025-001',
                'name'=>'آمنة علي',
                'nationality'=>'ليبيا',
                'gender'=>'أنثى',
                'department'=>'هندسة كهربائية',
                'registration_year'=>'2025',
                'semester'=>'الأول',
                'manual_number'=>'001',
                'national_id'=>'LY123456',
                'dob'=>'2005-01-15',
                'passport'=>'P1234567',
                'passport_number'=>'P1234567',
                'bank_name'=>'بنك ليبيا',
                'account_number'=>'1234567890',
                'mother_name'=>'فاطمة علي',
                'family_record'=>'1234',
                'status'=>'active',
            ],
            2 => (object)[
                'student_number'=>'2025-010',
                'name'=>'محمد عمر',
                'nationality'=>'مصر',
                'gender'=>'ذكر',
                'department'=>'علوم حاسوب',
                'registration_year'=>'2025',
                'semester'=>'الأول',
                'manual_number'=>'010',
                'national_id'=>'EG987654',
                'dob'=>'2004-12-10',
                'passport'=>'P9876543',
                'passport_number'=>'P9876543',
                'bank_name'=>'بنك مصر',
                'account_number'=>'9876543210',
                'mother_name'=>'أمينة محمود',
                'family_record'=>'5678',
                'status'=>'active',
            ],
        ];

        // دمج التعديلات المؤقتة إن وُجدت
        $updates = session('student_updates', []);
        if (isset($updates[$id])) {
            foreach ($updates[$id] as $k => $v) {
                $students[$id]->{$k} = $v;
            }
        }

        $student = $students[$id] ?? null;
        if (!$student) {
            return redirect()->route('students.index')->with('error', 'الطالب غير موجود');
        }

        // قوائم مساعدة للحقول
        $departments = ['هندسة كهربائية','علوم حاسوب','القسم العام','هندسة ميكانيك'];
        $genders = ['ذكر','أنثى'];

        return view('ui.students.edit', compact('student','id','departments','genders'));
    }

    public function studentsUpdate(\Illuminate\Http\Request $request, $id)
    {
        $data = $request->validate([
            'student_number'   => 'nullable|string|max:20',
            'name'             => 'required|string|max:255',
            'nationality'      => 'nullable|string|max:100',
            'gender'           => 'nullable|string|max:10',
            'department'       => 'nullable|string|max:255',
            'registration_year'=> 'nullable|integer',
            'semester'         => 'nullable|string|max:50',
            'manual_number'    => 'nullable|string|max:50',
            'national_id'      => 'nullable|string|max:50',
            'passport_number'  => 'nullable|string|max:50',
            'dob'              => 'nullable|date',
            'bank_name'        => 'nullable|string|max:100',
            'account_number'   => 'nullable|string|max:50',
            'mother_name'      => 'nullable|string|max:255',
            'family_record'    => 'nullable|string|max:50',
            'status'           => 'nullable|string|max:20',
        ]);

        // التوافق مع الجدول في الواجهة (يعرض passport وليس passport_number)
        if (isset($data['passport_number'])) {
            $data['passport'] = $data['passport_number'];
        }

        // خزّن التعديل في الجلسة لكي ينعكس على قائمة الطلاب مؤقتاً
        $updates = session('student_updates', []);
        $updates[$id] = $data;
        session(['student_updates' => $updates]);

        return redirect()->route('students.index')->with('success', 'تم تحديث بيانات الطالب');
    }
public function studentsAllRecords()
{
    // بيانات تجريبية لجميع الطلاب
    $students = [
        (object)['student_number'=>'2025-001','name'=>'آمنة علي','department'=>'هندسة كهربائية','semester'=>'الأول','enrollment_year'=>'2025'],
        (object)['student_number'=>'2025-010','name'=>'محمد عمر','department'=>'علوم حاسوب','semester'=>'الأول','enrollment_year'=>'2025'],
        (object)['student_number'=>'2024-075','name'=>'سارة محمود','department'=>'هندسة ميكانيك','semester'=>'الثاني','enrollment_year'=>'2024'],
        (object)['student_number'=>'2023-050','name'=>'علي حسن','department'=>'هندسة كهربائية','semester'=>'الثالث','enrollment_year'=>'2023'],
        // تضيف باقي الطلاب هنا
    ];

    // أقسام متاحة للفلترة
    $departments = ['هندسة كهربائية','علوم حاسوب','هندسة ميكانيك'];

    // جلب القيم من البحث GET
    $q = request('q');
    $departmentFilter = request('department');

    // فلترة البيانات
    $students = array_filter($students, function($s) use ($q, $departmentFilter) {
        $match = true;
        if($q) {
            $match = str_contains($s->name, $q) || str_contains($s->student_number, $q);
        }
        if($departmentFilter) {
            $match = $match && $s->department == $departmentFilter;
        }
        return $match;
    });

    return view('ui.students.allRecords', compact('students','departments','q','departmentFilter'));
}

public function excel()
{
    $students = [
        (object)['student_number'=>'2025-001','name'=>'مرام علي','department'=>'هندسة برمجيات','status'=>'active'],
        (object)['student_number'=>'2025-010','name'=>'لجين عمر','department'=>'هندسة نفط','status'=>'active'],
        (object)['student_number'=>'2024-075','name'=>'ندى محمود','department'=>'هندسة اتصالات','status'=>'graduated'],
    ];

    return view('students.excel', compact('students'));
}

}
