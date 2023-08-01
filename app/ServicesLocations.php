<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesLocations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_locations';
    protected $fillable = [
        'location_id',
        'service_id'
    ];
}
