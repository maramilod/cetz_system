<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'number',
        'code',
        'name',
        'units',
        'hours',
        'depends_on',
        'alternative_for',
        'user_name',
        'department_id',
    ];
}
