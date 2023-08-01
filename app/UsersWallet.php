<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersWallet extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_wallet';

    protected $fillable = [
        'user_id',
        'wallet_balance'
    ];
}
