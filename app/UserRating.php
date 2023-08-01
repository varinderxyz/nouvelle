<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users_rating';
    protected $casts = [
    'star_rating' => 'integer'
    ];
    protected $fillable = [
        'user_id',
        'sender_user_id',
        'time',
        'communication',
        'skills',
        'quality_of_work',
        'professionalism',
        'star_rating',
        'feedback'
    ];

    public function review_sender_user()
    {
        return $this->hasOne(User::class, 'id', 'sender_user_id')->select('id','name','picture');
    }
}
