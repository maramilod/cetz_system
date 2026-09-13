<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'degree_type',
        'semester_number',
        'start_date',
        'end_date',
        'term_type',
                'active',
                'approved'

    ];

    // علاقة مع جدول course_offerings
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}
