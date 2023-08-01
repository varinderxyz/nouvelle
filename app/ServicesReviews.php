<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesReviews extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services_reviews';
    protected $fillable = [
        'service_id',
        'user_id',
        'review'
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
