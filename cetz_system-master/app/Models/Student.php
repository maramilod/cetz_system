<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'nationality',
        'gender',
        'department_id',
        'section_id',
        'registration_year',
        'student_number',
        'manual_number',
        'national_id',
        'passport_number',
        'dob',
        'academic_term',
        'bank_name',
        'account_number',
        'mother_name',
        'family_record',
        'current_status',
        'photo'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

  //  public function sections()
//{
 //   return $this->hasMany(Section::class);
//}
public function section()
{
    return $this->belongsTo(Section::class);
}

public function enrollments()
{
    return $this->hasMany(Enrollment::class, 'student_id', 'id');
}


}
