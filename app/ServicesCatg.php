<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesCatg extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_catg';

    protected $fillable = [
        'name',
    ];
    public function users()
    {
        return $this->belongsTo(Users_services::class, 'services_cat_id', 'id');
    }
}
