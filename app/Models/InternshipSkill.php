<?php

namespace App\Models;

use App\Models\BaseModel;

class internshipSkill extends BaseModel
{
    protected $table      = 'INTERNSHIP_SKILLS';
    protected $primaryKey = 'INTERNSHIP_SKILL_ID';
    public $timestamps    = false;

    protected $fillable = [
        'INTERNSHIP_ID',
        'SKILL_ID',
        'IS_MANDATORY',
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'INTERNSHIP_ID', 'INTERNSHIP_ID');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'SKILL_ID', 'SKILL_ID');
    }
}