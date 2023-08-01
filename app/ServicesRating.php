<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesRating extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_rating';
    protected $fillable = [
        'service_id',
        'user_id',
        'time',
        'communication',
        'skills',
        'quality_of_work',
        'professionalism',
        'star_rating'
    ];
}
