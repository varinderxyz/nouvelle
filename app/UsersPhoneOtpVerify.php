<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersPhoneOtpVerify extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_phone_otp_verify';

    protected $fillable = [
        'user_id',
        'otp',
        'created_at'
    ];
}
