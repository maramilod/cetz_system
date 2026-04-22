<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_offering_id',
        'role',
    ];

    public function courseOffering()
    {
        return $this->belongsTo(
            CourseOffering::class,
            'course_offering_id', // اسم العمود في teaching_assignments
            'id'                  // المفتاح في course_offerings
        );
    }

    public function teacher()
    {
        return $this->belongsTo(
            Teacher::class,
            'teacher_id',
            'id'
        );
    }
}
