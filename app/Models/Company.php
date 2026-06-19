<?php

namespace App\Models;

use App\Models\BaseModel;

class Company extends BaseModel
{
    protected $table      = 'COMPANIES';
    protected $primaryKey = 'COMPANY_ID';
    public $timestamps    = false;

    protected $fillable = [
        'USER_ID', 'COMPANY_NAME', 'INDUSTRY', 'COMPANY_SIZE',
        'LOCATION', 'WEBSITE', 'DESCRIPTION',
        'CONTACT_PERSON', 'CONTACT_EMAIL',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'COMPANY_ID', 'COMPANY_ID');
    }
}