<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduation_project_id',
        'student_id',
    ];

    // المشروع الذي ينتمي إليه الطالب
    public function project()
    {
        return $this->belongsTo(GraduationProject::class, 'graduation_project_id');
    }

    // الطالب
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
