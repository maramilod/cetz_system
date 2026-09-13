<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'full_name',
        'national_id',
        'email',
        'working_id',
        'active', 
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function ranks()
{
    return $this->hasMany(TeacherRank::class);
}

public function currentRank()
{
    return $this->hasOne(TeacherRank::class)->whereNull('to_date');
}

public function employmentStatuses()
{
    return $this->hasMany(TeacherEmploymentStatus::class);
}

public function currentEmploymentStatus()
{
    return $this->hasOne(TeacherEmploymentStatus::class)
        ->whereNull('to_date');
}


public function teachingAssignments()
{
    return $this->hasMany(TeachingAssignment::class);
}

  public function teacherRanks()
    {
        return $this->hasMany(TeacherRank::class);
    }

    // 👇 العلاقة مع الوضعيات الوظيفية
    public function teacherEmploymentStatuses()
    {
        return $this->hasMany(TeacherEmploymentStatus::class);
    }
}
