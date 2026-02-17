<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherEmploymentStatus extends Model
{
    use HasFactory;

    protected $table = 'teacher_employment_statuses';

    protected $fillable = [
        'teacher_id',
        'employment_status_id',
        'from_date',
        'to_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    /**
     * الوضع الوظيفي
     */
    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    /**
     * الأستاذ
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Scope: الوضع الوظيفي الحالي فقط
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('to_date');
    }
}
