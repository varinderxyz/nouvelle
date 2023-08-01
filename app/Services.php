<?php

namespace App;

use App\User;
// use App\ServicesLocation;
use App\Users_services;
use App\ServicesRating;
use App\ServicesCatg;
use App\ServicesReviews;
use App\UserRating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// use Laravel\Scout\Searchable;
// use Spatie\Searchable\Searchable;
// use Spatie\Searchable\SearchResult;

class Services extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'services';
    

    protected $fillable = [
        'user_id',
        'picture',
        'zip_code',
        'service_name',
        'services_category_id',
        'willing_to_travel',
        'longitude',
        'latitude',
        'geo_address',
        'swap',
        'hire',
        'cancellation_terms_hour',
        'service_descp',
        'video_url',
        'service_status',
        'featured',
        'views'
    ];

   
    public function service_user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function service_category()
    {
        return $this->hasOne(ServicesCategory::class, 'id', 'services_category_id');
    }

    public function service_ratings()
    {
        return $this->hasMany(ServicesRating::class, 'service_id');
    }

    public function services_reviews()
    {
        return $this->hasMany(ServicesReviews::class, 'service_id');
    }

    public function service_rating_calc()
    {
        return $this->service_ratings()->sum(DB::raw('star_rating'));
    }

    public function user_reviews()
    {
        return $this->hasOne(UserRating::class, 'user_id','user_id')->select('id','user_id','sender_user_id','feedback','created_at','updated_at')->latest();
    }

}
