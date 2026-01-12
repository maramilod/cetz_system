<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'created_by',
    ];

    // علاقة بالموظف الذي أنشأ المهمة
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
