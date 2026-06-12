<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table      = 'APPLICATIONS';
    protected $primaryKey = 'APPLICATION_ID';
    public $timestamps    = false;

    protected $fillable = [
        'INTERNSHIP_ID', 'STUDENT_ID', 'COVER_LETTER', 'STATUS',
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'INTERNSHIP_ID', 'INTERNSHIP_ID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'STUDENT_ID', 'STUDENT_ID');
    }

    public function interview()
    {
        return $this->hasOne(Interview::class, 'APPLICATION_ID', 'APPLICATION_ID');
    }
}