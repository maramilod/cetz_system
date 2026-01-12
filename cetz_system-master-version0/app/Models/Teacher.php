<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'full_name',
        'national_id',
        'email',
        'working_id',
        'active', 
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
