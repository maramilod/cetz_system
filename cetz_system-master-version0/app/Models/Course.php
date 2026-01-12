<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'has_practical',
        'course_code',
        'hours',
        'units', 
        'prerequisite_course_id',
        'course_type',
    ];

    // علاقة مع قاعدة grading rules
    public function gradingRules()
    {
        return $this->hasOne(CourseGradingRule::class);
    }

    // علاقة مع course_offerings
    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }




    // علاقة عكسية للمواد التي تعتمد على هذا الكورس
    public function dependentCourses()
    {
        return $this->hasMany(Course::class, 'prerequisite_course_id');
    }
}
