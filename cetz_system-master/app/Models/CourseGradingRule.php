<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseGradingRule extends Model
{
    protected $table = 'course_grading_rules';

    protected $fillable = [
        'course_id',

        // الجزء النظري
        'theory_work_ratio',
        'theory_midterm_ratio',
        'theory_final_ratio',

        
        // الجزء العملي (اختياري)
        'practical_work_ratio',
        'practical_midterm_ratio',
        'practical_final_ratio',
    ];
 protected $casts = [
        'theory_work_ratio'       => 'decimal:2',
        'theory_midterm_ratio'    => 'decimal:2',
        'theory_final_ratio'      => 'decimal:2',
        'practical_work_ratio'    => 'decimal:2',
        'practical_midterm_ratio' => 'decimal:2',
        'practical_final_ratio'   => 'decimal:2',
    ];
    /**
     * العلاقة مع المادة
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * هل المادة تحتوي على عملي فعليًا؟
     */
    public function hasPractical(): bool
    {
        return !is_null($this->practical_work_ratio)
            || !is_null($this->practical_midterm_ratio)
            || !is_null($this->practical_final_ratio);
    }

}
