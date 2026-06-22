<?php

namespace App\Models;

use App\Models\BaseModel;

class Internship extends BaseModel
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
        )
        ->using(InternshipSkillPivot::class)
        ->withPivot('INTERNSHIP_SKILL_ID', 'IS_MANDATORY');
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