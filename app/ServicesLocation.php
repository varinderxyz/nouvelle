<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesLocation extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_location';
    protected $fillable = [
        'services_location_name'
    ];
}
