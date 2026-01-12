<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    protected $fillable = [
        'course_id',
        'section_id',
        'semester_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Scopes (اختياري لكن احترافي)
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDropped($query)
    {
        return $query->where('status', 'dropped');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class, 'course_offering_id');
    }

    // المادة
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // الشعبة (قد تكون null / 0 في حالة العام)
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
   public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_offering_id');
    }
    // السيمستر
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
