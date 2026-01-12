<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_offering_id',
        'section_name',
        'capacity',
    ];

    // علاقة مع CourseOffering
    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class);
    }
}
