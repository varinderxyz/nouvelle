<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SwapLocations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'swap_services_locations';
    protected $fillable = [
        'location_id',
        'swap_services_id'
    ];
}
