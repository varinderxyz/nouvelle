<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'notifications';
    protected $fillable = [
        'user_sender_id',
        'user_receiver_id',
        'type',
        'status',
        'info'
    ];

    public function sender_user()
    {
        return $this->hasOne(User::class, 'id', 'user_sender_id');
    }
}
