<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'updated_by',
    ];

    // الشعبة تنتمي إلى قسم
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // الشعبة فيها طلاب
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function updatedBy()
{
    return $this->belongsTo(User::class, 'updated_by');
}
public function courseOfferings() {
    return $this->hasMany(CourseOffering::class);
}

    
}
