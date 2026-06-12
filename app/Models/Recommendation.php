<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $table      = 'RECOMMENDATIONS';
    protected $primaryKey = 'RECOMMENDATION_ID';
    public $timestamps    = false;

    protected $fillable = ['STUDENT_ID', 'INTERNSHIP_ID', 'MATCH_SCORE'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'INTERNSHIP_ID', 'INTERNSHIP_ID');
    }
}