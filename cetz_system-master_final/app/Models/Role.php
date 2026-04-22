<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;       
use App\Models\Permission; 

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /* ================= Relationships ================= */

    /**
     * Users that belong to this role
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Permissions that belong to this role
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }



    /* ================= Authorization Helpers ================= */

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->exists();
    }
}