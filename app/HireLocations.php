<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HireLocations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'hire_services_locations';
    protected $fillable = [
        'location_id',
        'hire_services_id'
    ];
}
