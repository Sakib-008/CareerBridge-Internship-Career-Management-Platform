<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentSkillPivot extends Pivot
{
    protected $table      = 'STUDENT_SKILLS';
    public $timestamps    = false;

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value !== null) {
            return $value;
        }

        $lower = strtolower($key);
        if ($key !== $lower && array_key_exists($lower, $this->attributes)) {
            return parent::getAttribute($lower);
        }

        return $value;
    }
}