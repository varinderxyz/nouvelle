<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailVerify extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'emailverify';
    protected $fillable = [
        'email', 'otp'
    ];
}
