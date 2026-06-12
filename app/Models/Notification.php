<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
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