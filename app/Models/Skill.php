<?php

namespace App\Models;

use App\Models\BaseModel;

class Skill extends BaseModel
{
    protected $table      = 'SKILLS';
    protected $primaryKey = 'SKILL_ID';
    public $timestamps    = false;

    protected $fillable = ['SKILL_NAME', 'CATEGORY'];

    public function students()
    {
        return $this->belongsToMany(
            Student::class, 'STUDENT_SKILLS', 'SKILL_ID', 'STUDENT_ID'
        )->withPivot('PROFICIENCY');
    }

    public function internships()
    {
        return $this->belongsToMany(
            Internship::class, 'INTERNSHIP_SKILLS', 'SKILL_ID', 'INTERNSHIP_ID'
        )->withPivot('IS_MANDATORY');
    }
}