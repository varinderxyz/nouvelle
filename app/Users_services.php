<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users_services extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_services';

    protected $fillable = [
        'user_id',
        'services_cat_id',
        'user_id'
    ];
    public function services()
    {
        return $this->belongsTo(Services::class, 'id', 'services_cat_id');
    }
}
