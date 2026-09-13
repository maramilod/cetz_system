<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'is_general', 'is_active','updated_by'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Scope لجلب الأقسام النشطة فقط
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    // في نموذج Department.php
public function updatedByUser() {
    return $this->belongsTo(User::class, 'updated_by');
}

public function sections()
{
    return $this->hasMany(Section::class);
}

}

