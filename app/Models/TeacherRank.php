<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherRank extends Model
{
    use HasFactory;

    protected $table = 'teacher_ranks';

    protected $fillable = [
        'teacher_id',
        'academic_rank_id',
        'from_date',
        'to_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    /**
     * الرتبة الأكاديمية
     */
    public function academicRank()
    {
        return $this->belongsTo(AcademicRank::class);
    }

    /**
     * الأستاذ
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Scope: الرتبة الحالية فقط
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('to_date');
    }
}
