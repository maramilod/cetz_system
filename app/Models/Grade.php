<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $table = 'grades';

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_id',
        'theory_work',
        'theory_final',
       'total',
        'student_type',
        'is_second_chance',
        'previous_theory_work',
    'previous_theory_final',
    ];

    protected $casts = [
        'is_second_chance' => 'boolean',
        'theory_work'      => 'decimal:2',
        'theory_final'     => 'decimal:2',
       'total'            => 'decimal:2',
       'previous_theory_work'  => 'decimal:2',
    'previous_theory_final' => 'decimal:2',
    ];

    // علاقات
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
