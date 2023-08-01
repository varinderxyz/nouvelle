<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersTransactions extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_transactions';

    protected $fillable = [
        'sender_user_id',
        'receiver_user_id',
        'amount_paid'
    ];

    public function receiveMoneyFromUser()
    {
        return $this->hasOne(User::class, 'id', 'sender_user_id');
    }
    
    public function sendMoneyToUser()
    {
        return $this->hasOne(User::class, 'id', 'receiver_user_id');
    }

    
}
