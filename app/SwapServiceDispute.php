<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SwapServiceDispute extends Model
{
    protected $table = 'swap_services_dispute';
    protected $fillable = ['service_id'];
    
}
