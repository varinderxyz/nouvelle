<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'locations';
    protected $fillable = [
        'name'
    ];
}
