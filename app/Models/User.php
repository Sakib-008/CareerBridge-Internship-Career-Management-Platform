<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table      = 'USERS';
    protected $primaryKey = 'USER_ID';
    public $timestamps    = false;

    protected $fillable = [
        'EMAIL',
        'PASSWORD_HASH',
        'ROLE',
        'IS_ACTIVE',
    ];

    protected $hidden = ['PASSWORD_HASH'];

    // Override for Laravel Auth
    public function getAuthPassword()
    {
        return $this->PASSWORD_HASH;
    }

    // Relationships
    public function student()
    {
        return $this->hasOne(Student::class, 'USER_ID', 'USER_ID');
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'USER_ID', 'USER_ID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'USER_ID', 'USER_ID');
    }

    // Role helpers
    public function isStudent(): bool  { return $this->ROLE === 'student'; }
    public function isCompany(): bool  { return $this->ROLE === 'company'; }
    public function isAdmin(): bool    { return $this->ROLE === 'admin'; }
    public function isActive(): bool   { return $this->IS_ACTIVE == 1; }
}