<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesCategory extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_category';

    protected $fillable = [
        'name',
        'picture'
    ];


}
