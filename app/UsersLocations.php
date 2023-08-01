<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersLocations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_locations';

    protected $fillable = [
        'location_id',
        'user_id'
    ];
}
