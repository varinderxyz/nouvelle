<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SwapOfferLocations extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'swap_service_locations_offer';
    protected $fillable = [
        'location_id',
        'swap_services_id'
    ];
}
