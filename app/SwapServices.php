<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SwapServices extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'swap_services';

    protected $casts = [
        'multiple_service_sender_id' => 'array',
        'sender_service_confirmed' => 'integer',
        'receiver_service_confirmed' => 'integer',
        'sender_service_completed' => 'integer',
        'receiver_service_completed' => 'integer',
        'receiver_swap_service_time_count' => 'integer'
    ];

    protected $fillable = [
        'service_hours_swap',
        'service_hours_want',
        'multiple_service_sender_id',
        'receiver_swap_service_time_count',
        'user_sender_id',
        'service_sender_id',
        'user_receiver_id',
        'service_receiver_id',
        'offer_instructions',
        'service_offering_date',
        'service_recieving_date',
        'service_status',
        'sender_amount_to_be_paid',
        'receiver_amount_to_be_paid',
        'boot_calculate',
        'boot_assign_person',
        'amount_to_be_paid',
        'payment_status',
        'repeat_service_time',
        'sender_service_confirmed',
        'receiver_service_confirmed',
        'sender_service_completed',
        'receiver_service_completed',
        'delete_by_user'
    ];



    public function getMultipleServiceSenderIdAttribute($value)
    {
        return json_decode(json_decode($value));
    }

    public function sender_service()
    {
        return $this->hasOne(Services::class, 'id', 'service_sender_id');
    }

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

    public function boot_assign_person_detail()
    {
        return $this->hasOne(User::class, 'id', 'boot_assign_person');
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
