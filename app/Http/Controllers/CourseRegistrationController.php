<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseRegistrationController extends Controller
{
    private $registrations = [
        ['id'=>1, 'student_name'=>'آمنة علي', 'department'=>'هندسة كهربائية', 'subject'=>'رياضيات 1', 'semester'=>'ربيع 2025'],
        ['id'=>2, 'student_name'=>'محمد عمر', 'department'=>'علوم حاسوب', 'subject'=>'فيزياء 1', 'semester'=>'خريف 2024'],
        ['id'=>3, 'student_name'=>'سارة محمود', 'department'=>'هندسة ميكانيك', 'subject'=>'ميكانيكيات 1', 'semester'=>'ربيع 2025'],
        // أضف بيانات أكثر هنا
    ];

    public function index(Request $request)
    {
        $query = $request->input('query', '');

        $filtered = array_filter($this->registrations, function($item) use ($query) {
            return stripos($item['student_name'], $query) !== false
                || stripos($item['department'], $query) !== false
                || stripos($item['subject'], $query) !== false
                || stripos($item['semester'], $query) !== false;
        });

        return view('registration.courses', ['registrations' => $filtered, 'query' => $query]);
    }

    public function print(Request $request)
    {
        $query = $request->input('query', '');

        $filtered = array_filter($this->registrations, function($item) use ($query) {
            return stripos($item['student_name'], $query) !== false
                || stripos($item['department'], $query) !== false
                || stripos($item['subject'], $query) !== false
                || stripos($item['semester'], $query) !== false;
        });

        return view('registration.courses-print', ['registrations' => $filtered]);
    }
}
