<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersCategories extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_categories';

    protected $fillable = [
        'id',
        'services_category_id',
        'service_id',
        'user_id'
    ];
}
