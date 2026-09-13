<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'student_id',
        'sender_id',
        'sender_name',
    ];

    // علاقة الطالب (Student)
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
