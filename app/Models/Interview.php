<?php

namespace App\Models;

use App\Models\BaseModel;

class Interview extends BaseModel
{
    protected $table      = 'INTERVIEWS';
    protected $primaryKey = 'INTERVIEW_ID';
    public $timestamps    = false;

    protected $fillable = [
        'APPLICATION_ID',
        'SCHEDULED_DATE',
        'SCHEDULED_TIME',
        'INTERVIEW_MODE',
        'LOCATION_OR_LINK',
        'NOTES',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'APPLICATION_ID', 'APPLICATION_ID');
    }
}