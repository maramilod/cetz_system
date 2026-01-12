<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubjectDistributionController extends Controller
{
    // عرض كل التوزيعات
    public function index()
    {
        // بيانات تجريبية
        $distributions = [
            (object)[
                'id'=>1,
                'department'=>'هندسة كهربائية',
                'subject_name'=>'الرياضيات',
                'subject_code'=>'MATH101',
                'teacher'=>'أ. أحمد علي',
                'semester'=>1
            ],
            (object)[
                'id'=>2,
                'department'=>'علوم حاسوب',
                'subject_name'=>'برمجة',
                'subject_code'=>'CS102',
                'teacher'=>'د. سارة محمود',
                'semester'=>2
            ],
        ];

        return view('ui.subject_distributions.index', compact('distributions'));
    }

    // صفحة إضافة توزيع جديد
    public function create()
    {
        $departments = [
            (object)['id'=>1,'name'=>'هندسة كهربائية'],
            (object)['id'=>2,'name'=>'علوم حاسوب'],
            (object)['id'=>3,'name'=>'هندسة ميكانيك'],
        ];

        $teachers = [
            'أ. أحمد علي', 'د. سارة محمود', 'أ. محمد عمر'
        ];

        return view('ui.subject_distributions.create', compact('departments','teachers'));
    }

    // تخزين البيانات (POST)
    public function store(Request $request)
    {
        // هنا ممكن تخزني في DB
        // مثال: $request->all();

        return redirect()->route('subject-distributions.index')->with('success', 'تمت إضافة التوزيع بنجاح!');
    }

    // صفحة تعديل
    public function edit($id)
    {
        $distribution = (object)[
            'id'=>$id,
            'department_id'=>1,
            'subject_name'=>'الرياضيات',
            'subject_code'=>'MATH101',
            'teacher'=>'أ. أحمد علي',
            'semester'=>1
        ];

        $departments = [
            (object)['id'=>1,'name'=>'هندسة كهربائية'],
            (object)['id'=>2,'name'=>'علوم حاسوب'],
            (object)['id'=>3,'name'=>'هندسة ميكانيك'],
        ];

        $teachers = [
            'أ. أحمد علي', 'د. سارة محمود', 'أ. محمد عمر'
        ];

        return view('ui.subject_distributions.edit', compact('distribution','departments','teachers'));
    }

    // تحديث البيانات (POST)
    public function update(Request $request, $id)
    {
        // تحديث في DB
        return redirect()->route('subject-distributions.index')->with('success','تم تعديل التوزيع بنجاح!');
    }

    // حذف التوزيع
    public function destroy($id)
    {
        // حذف من DB
        return redirect()->route('subject-distributions.index')->with('success','تم حذف التوزيع بنجاح!');
    }

    // طباعة
    public function print()
    {
        $distributions = [
            (object)['department'=>'هندسة كهربائية','subject_name'=>'الرياضيات','subject_code'=>'MATH101','teacher'=>'أ. أحمد علي','semester'=>1],
            (object)['department'=>'علوم حاسوب','subject_name'=>'برمجة','subject_code'=>'CS102','teacher'=>'د. سارة محمود','semester'=>2],
        ];

        return view('ui.subject_distributions.print', compact('distributions'));
    }

    public function show($id)
{
    // ممكن مجرد إعادة توجيه أو رسالة
    return redirect()->route('subject-distributions.index');
}

}
