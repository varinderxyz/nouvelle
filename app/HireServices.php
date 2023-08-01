<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class HireServices extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'hire_services';
    protected $fillable = [
        'service_hours_want',
        'user_sender_id',
        'user_receiver_id',
        'service_receiver_id',
        'offer_instructions',
        'service_status',
        'amount_to_be_paid',
        'payment_status',
        'sender_service_confirmed',
        'receiver_service_confirmed',
        'sender_service_completed',
        'delete_by_user'
    ];

   
    public function receiver_service()
    {
        return $this->hasOne(Services::class, 'id', 'service_receiver_id');
    }

    public function sender_user()
    {
        return $this->hasOne(User::class, 'id', 'user_sender_id');
    }

    public function receiver_user()
    {
        return $this->hasOne(User::class, 'id', 'user_receiver_id');
    }

    public function service_ratings()
    {
        return $this->hasMany(ServicesRating::class, 'service_id');
    }
    
    public function declined_by_user()
    {
        return $this->hasOne(User::class, 'id', 'delete_by_user');
    }
}
