<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certifications extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'certifications';

    protected $fillable = [
        'certification_name',
        'university_name',
        'month_year',
        'user_id'
    ];
}
