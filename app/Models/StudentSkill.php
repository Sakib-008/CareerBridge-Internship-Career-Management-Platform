<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkill extends Model
{
    protected $table      = 'STUDENT_SKILLS';
    protected $primaryKey = 'STUDENT_SKILL_ID';
    public $timestamps    = false;

    protected $fillable = [
        'STUDENT_ID',
        'SKILL_ID',
        'PROFICIENCY',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'SKILL_ID', 'SKILL_ID');
    }
}