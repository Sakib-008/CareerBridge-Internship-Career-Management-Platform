<?php

namespace App\Models;

use App\Models\BaseModel;

class Notification extends BaseModel
{
    protected $table      = 'NOTIFICATIONS';
    protected $primaryKey = 'NOTIFICATION_ID';
    public $timestamps    = false;

    protected $fillable = ['USER_ID', 'MESSAGE', 'IS_READ'];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }
}