<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Services;
use App\UserRating;
use App\UsersWallet;
use Auth;
// use Laravel\Scout\Searchable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;
    // use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'users';

    protected $casts = [
    'email_verified_at' => 'datetime',
    'willing_to_travel' => 'integer',
    'zip_code' => 'integer',
    'longitude' => 'float',
    'latitude' => 'float',
    'phone_verified' => 'integer',
    'facebook_verified' => 'integer',
    'email_verified' => 'integer',
    'phone' => 'integer',
    'hourly_rate' => 'integer'
    ];


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'zip_code',
        'hourly_rate',
        'payment_customer_id',
        'willing_to_travel',
        'picture',
        'about',
        'longitude',
        'latitude',
        'geo_address',
        'user_id',
        'provider',
        'provider_id',
        'phone_verified',
        'facebook_verified',
        'email_verified',
        'designation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */


    public function user_rating()
    {
        return $this->hasMany(UserRating::class, 'user_id', 'id');
    }

    public function swap_invites()
    {

        return $this->hasMany(SwapServices::class, 'user_receiver_id', 'id');
    }

    public function active_swap_receive()
    {
        return $this->hasMany(SwapServices::class, 'user_receiver_id', 'id')
        ->where('service_status','pending')
        ->orWhere('service_status','active');
    }

    public function active_swap_sent()
    {
        return $this->hasMany(SwapServices::class, 'user_sender_id', 'id')
        ->where('service_status','pending')
        ->orWhere('service_status','active');
    }

    public function notifications()
    {
        return $this->hasMany(Notifications::class, 'user_receiver_id', 'id')->where('seen',0);
    }

    // VIEW SINGLE USER DETAIL
    public function users_services()
    {
        return $this->hasMany(Services::class, 'user_id', 'user_id');
    }

    public function users_categories()
    {
        return $this->hasManyThrough(
            ServicesCategory::class,
            UsersCategories::class,
            // key on users_categories table
             'user_id',
            // key on  services_category
             'id',
            // key on users table
             'id',
            //  key on users_categories table
             'services_category_id'
            );
    }

    public function user_services()
    {
        return $this->hasMany(
            Services::class,
            // key on  Services
            'user_id',
        );
    }

    public function certifications()
    {
        return $this->hasMany(
            Certifications::class
        );
    }

    // VIEW SINGLE USER DETAIL END


    public function AauthAcessToken()
    {
        return $this->hasMany('App\OauthAccessToken');
    }

     public function user_reviews()
    {
        return $this->hasOne(UserRating::class, 'user_id','id')->select('id','user_id','sender_user_id','feedback','created_at','updated_at')->latest();
    }

    public function user_wallet()
    {
        return $this->hasOne('App\UsersWallet');
    }

}
