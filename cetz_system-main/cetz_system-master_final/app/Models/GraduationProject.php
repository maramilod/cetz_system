<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduationProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'team_code',
        'status',
         'supervisor',
    ];
public function supervisorRelation() {
    return $this->belongsTo(Teacher::class, 'supervisor');
}

     public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'project_students',
            'graduation_project_id',
            'student_id'
        );
    }

}
