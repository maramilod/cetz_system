<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicRank extends Model
{
    use HasFactory;

    protected $table = 'academic_ranks';

    protected $fillable = [
        'name',
    ];

    /**
     * جميع الأساتذة الذين مرّوا بهذه الرتبة
     */
    public function teacherRanks()
    {
        return $this->hasMany(TeacherRank::class);
    }
}
