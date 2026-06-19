<?php

namespace App\Models;

use App\Models\BaseModel;

class AuditLog extends BaseModel
{
    protected $table      = 'AUDIT_LOG';
    protected $primaryKey = 'LOG_ID';
    public $timestamps    = false;

    protected $fillable = [
        'USER_ID', 'ACTION', 'TABLE_NAME',
        'RECORD_ID', 'OLD_VALUE', 'NEW_VALUE',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }
}