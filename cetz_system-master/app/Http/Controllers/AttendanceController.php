<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeachingAssignment;
use App\Models\CourseOffering;
use App\Models\Teacher;



class AttendanceController extends Controller
{
    public function index()
{
    $classes = TeachingAssignment::with([
        'courseOffering.course',
        'courseOffering.section',
        'courseOffering.semester',
        'teacher'
    ])->get();
       $enrollments = \App\Models\Enrollment::with(['student'])
        ->where('attempt', 1)
        ->where('status', 'in_progress')
        ->get();

    $maxDates = \App\Models\Semester::selectRaw(
        'MAX(start_date) as max_start, MAX(end_date) as max_end'
    )->first();

    $semesters = \App\Models\Semester::where('start_date', $maxDates->max_start)
        ->where('end_date', $maxDates->max_end)
        ->get();

    return view(
        'registration.attendance-form',
        compact('classes', 'semesters','enrollments')
    );
}
    
}
