<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_offering_id',
        'attempt',
        'status',
        'result_date',
    ];

    // علاقة الطالب
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    // علاقة المادة + السيمستر (CourseOffering)
    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    // للحصول على السيمستر مباشرة
public function semester()
{
    return $this->courseOffering()->with('semester')->first()->semester ?? null;
}

    // حالة النجاح
    public function isPassed()
    {
        return $this->status === 'passed';
    }

    public function course()
{
    return $this->belongsTo(Course::class);
}
}
