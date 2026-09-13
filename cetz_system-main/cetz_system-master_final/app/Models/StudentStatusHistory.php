<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'student_status_history';

    protected $fillable = [
        'student_id',
        'status',
        'from_date',
        'to_date',
        'reason',
    ];


}