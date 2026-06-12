<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $table      = 'INTERNSHIPS';
    protected $primaryKey = 'INTERNSHIP_ID';
    public $timestamps    = false;

    protected $fillable = [
        'COMPANY_ID', 'TITLE', 'DESCRIPTION', 'LOCATION',
        'INTERNSHIP_TYPE', 'DURATION_MONTHS', 'STIPEND',
        'VACANCIES', 'APPLICATION_DEADLINE', 'STATUS',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'COMPANY_ID', 'COMPANY_ID');
    }

    public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'INTERNSHIP_SKILLS',
            'INTERNSHIP_ID',
            'SKILL_ID'
        )->withPivot('IS_MANDATORY');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'INTERNSHIP_ID', 'INTERNSHIP_ID');
    }

    public function isOpen(): bool
    {
        return $this->STATUS === 'Open';
    }
}