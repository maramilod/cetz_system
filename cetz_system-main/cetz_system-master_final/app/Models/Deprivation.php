<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deprivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'reason',
        'updated_by',
    ];

    // علاقة بالانرولمنت
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // إذا لديك جدول مستخدمين
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function updatedByUser()
{
    return $this->belongsTo(User::class, 'updated_by');
}
}
