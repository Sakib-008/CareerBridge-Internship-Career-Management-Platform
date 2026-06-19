<?php

namespace App\Models;

use App\Models\BaseModel;

class Student extends BaseModel
{
    protected $table      = 'STUDENTS';
    protected $primaryKey = 'STUDENT_ID';
    public $timestamps    = false;

    protected $fillable = [
        'USER_ID', 'FIRST_NAME', 'LAST_NAME', 'PHONE',
        'DATE_OF_BIRTH', 'UNIVERSITY', 'DEPARTMENT',
        'GPA', 'GRADUATION_YEAR', 'CV_FILE_PATH', 'PROFILE_SUMMARY',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }

    public function studentSkills()
    {
        return $this->hasMany(StudentSkill::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'STUDENT_SKILLS',
            'STUDENT_ID',
            'SKILL_ID'
        )->withPivot('PROFICIENCY', 'ADDED_AT');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function getFullNameAttribute(): string
    {
        return $this->FIRST_NAME . ' ' . $this->LAST_NAME;
    }
}