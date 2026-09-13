<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmploymentStatus extends Model
{
    use HasFactory;

    protected $table = 'employment_statuses';

    protected $fillable = [
        'name',
    ];

    /**
     * جميع الأساتذة الذين مرّوا بهذا الوضع الوظيفي
     */
    public function teacherEmploymentStatuses()
    {
        return $this->hasMany(TeacherEmploymentStatus::class);
    }
}
