<?php

namespace App\Http\Controllers;

use App\Services;
use App\User;
// use App\Users_services;
use App\ServicesRating;
// use App\ServicesCategory;
// use App\ServicesLocations;
use App\UsersCategories;
use App\ServicesReviews;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Throwable;
use Cache;
use Illuminate\Support\Facades\Storage;
use Geocoder;

class ServicesController extends Controller
{

    // SHOW USER AUTH SERVICES
    public function index(Request $request)
    {
        try {
            $id = $request->query('id');

            $status = $request->query('status');

            $Services = Services::with([
            'service_user',
            'service_category',
            'user_reviews',
            'user_reviews.review_sender_user'
            ])
            ->with(['service_user' => function ($query) {
                $query->withCount([
                        'user_rating AS total_service_user_rating' => function ($query) {
                            $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                        }
                ]);
            }])
            ->withCount('user_reviews')
            ->orderBy('id', 'DESC')
            ->where('user_id', auth()->user()->id);

            // IF REQUEST SERVICE BY ID
            if ($id) {
                $Services = $Services->where('id', $id);
            }

            // ACTIVE SERVICES GET
            $active_services = clone($Services);
            $active_services = $active_services->where([
                'services.service_status' => 'active',
            ]);
            $active_services = $active_services->get();
            $active_services_data_array = $active_services->toArray();


            // PAUSE SERVICES GET
            $pause_services = clone($Services);
            $pause_services = $pause_services->where([
                 'services.service_status' => 'pause',
            ]);
            $pause_services = $pause_services->get();
            $pause_services_data_array = $pause_services->toArray();

            $services_array = [
                'active' => $active_services_data_array,
                'pause' => $pause_services_data_array
            ];

            // IF GET BY ID
            if ($id) {
                $Services = $Services->first();
                $services_array = $Services->toArray();
            }

            // JSON RESPONSE
            $status_code = 200;
            $json_response = [
            'status_code' => $status_code,
            'status_message' => 'success',
            'data' => $services_array
           ];
        }
        // ERROR HANDLE
        catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // SHOW SERVICES WITH KEYWORD
    // PUBLIC URL
    public function search(Request $request)
    {
        try {
            // NEARBY POPULAR SERVICES


            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            $max_distance = $request->input('max_distance');

            $auth_user_id = $request->input('auth_user_id');

            $category_id = $request->input('category_id');

            $hourly_rate = $request->input('hourly_rate');

            $range = $request->input('range');

            if (!$max_distance) {
                $max_distance = 10;
            }

            // SELECT SERVICE WHERE BY LAT LONG
            $gr_circle_radius = 6371;

            $distance_select = sprintf(
                    " (%d * acos(cos(radians(%s)) " .
                " * cos(radians( latitude)) " .
                " * cos(radians( longitude) - radians(%s)) " .
                " + sin(radians(%s)) * sin(radians(latitude)) " .
                " ) " .
                " ) ",
                    $gr_circle_radius,
                    $latitude,
                    $longitude,
                    $latitude
                );


            // SEARCH SERVICES BY KEYWORD
            $keyword = $request->input('keyword');

            // split on 1+ whitespace & ignore empty (eg. trailing space)
            $searchValues = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

            // $searchValues = $keyword;

            // SERVICES RATING WISE
            /*
                 SEARCH SERVICE NAME
                 SEARCH SERVICE DISCRIPTION
                 SEARCH SERVICE ZIP CODE
                 SEARCH SERVICE CATEGORY NAME
            */

            $Services = Services::with([
                    'service_user',
                    'service_category',
                    'user_reviews',
                    'user_reviews.review_sender_user'
                ])
                ->where(function ($q) use ($searchValues) {
                    foreach ($searchValues as $value) {
                        $q->whereLike(['service_name', 'service_descp', 'zip_code', 'geo_address', 'service_category.name', 'service_user.name'], $value);
                    }
                })
                ->withCount('user_reviews');

            $Services = $Services->groupBy('id');
            if ($auth_user_id) {
                $Services = $Services->where('user_id', '!=', $auth_user_id);
            }

            if ($category_id) {
                if ($category_id) {
                    $Services = $Services->where('services_category_id', '=', $category_id);
                }
            }

            if ($hourly_rate) {
                if ($range == 'less') {
                    $Services = $Services->whereHas('service_user', function ($query) use ($hourly_rate) {
                        $query->where('hourly_rate', '<', $hourly_rate);
                    });
                }
                if ($range == 'greater') {
                    $Services = $Services->whereHas('service_user', function ($query) use ($hourly_rate) {
                        $query->where('hourly_rate', '>=', $hourly_rate);
                    });
                }
            }

            // IF LAT LONG EXISTS IN REQUEST
            if ($latitude && $longitude) {
                $Services = $Services->having(DB::raw($distance_select), '<=', $max_distance);
            }

            $Services = $Services->get();
            $services_data_array = $Services->toArray();

            // ALL DATA ARRAY TO JSON RESPONSE
            $status_code = 200;

            $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    // 'popular_nearby_services' => $nearby_popular_services_data_array,
                    'data' => $services_data_array,
                    // 'users' => $users_data_array
                ];

            if (empty($services_data_array)) {
                $status_code = 200;

                $json_response = [
                    'status_code' => 406,
                    'status_message' => 'error',
                    'message' => 'Services not Found!',
                    'data' => []
            ];
            }
        } catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }

        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // STORE SERVICE DATA
    public function store(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'service_name' => 'required',
            'picture' => 'required',
            'zip_code' => 'required',
            'services_category_id' => 'required',
            'willing_to_travel' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'service_descp' => 'required'
        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
        }
        // ELSE STORE DATA WITH CHECK MODEL
        else {

            // DB TRANSACTION BEGIN
            DB::beginTransaction();

            try {
                // REQUEST CATEGORY ID
                $services_category_id = $request->input('services_category_id');
                $check_services_category_availibility = checkCategories($services_category_id);

                if ($check_services_category_availibility) {

                    // CHECK IF PICTURE AVAILABLE
                    if ($request->has('picture')) {
                        // // IMAGE
                        // $file = $request->file('picture');
                        // $directory_name = "services";
                        // $image_name = imageUpload($file, $directory_name);

                        // STORE FILE
                        $file = $request->file('picture');
                        $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString().randomString(), $file, 'public');
                        $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/'.$image_name;
                    }

                  $latitude = $request->input('latitude');
                    $longitude = $request->input('longitude');
                    if (!$latitude && !$longitude) {
                        $latitude = 34.064911;
                        $longitude = 118.349949;
                    }
                    $address = Geocoder::getAddressForCoordinates($latitude, $longitude);
                    $geo_address = $address['formatted_address'];



                    // response status will be 'OK', if able to geocode given address


                    // STORE SERVICE DATA
                    $request_fields = array_merge($request->except('picture'), [
                        'picture' =>  $image_name,
                        'user_id' => Auth::user()->id,
                        'geo_address' => $geo_address
                     ]);

                    $service = Services::create($request_fields);

                    // STORE CATEGORIES DATA
                    $users_categories = new UsersCategories();
                    $users_categories->services_category_id = $request->input('services_category_id');
                    $users_categories->service_id = $service->id;
                    $users_categories->user_id = Auth::user()->id;
                    $users_categories->save();


                    // SUCCESS RESPONSE
                    $status_code = 200;
                    $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Data Successfully saved.',
                    'data' => $request_fields
                    ];
                }
                // IF SERVICES CATEGORY OR LOCATIONS ID NOT EXISTS
                else {
                    $status_code = 200;
                    $json_response = [
                    'status_code' => 406,
                    'status_message' => 'error',
                    'message' => 'Services Category not found|matched!',
                  ];
                }

                // COMMIT CHANGES
                DB::commit();
            } catch (\Throwable $exception) {

                // ROLLBACK ALL CHANGES
                DB::rollback();
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // UPDATE SERVICE DATA
    public function update(Request $request, $id)
    {

        // DB TRANSACTION BEGIN
        DB::beginTransaction();

        try {

            // REQUEST CATEGORY ID
            $services_category_id = $request->input('services_category_id');
            $check_services_category_availibility = checkCategories($services_category_id);

            if ($check_services_category_availibility) {

$latitude = $request->input('latitude');
$longitude = $request->input('longitude');
if (!$latitude && !$longitude) {
    $latitude = 34.064911;
    $longitude = 118.349949;
}
$address = Geocoder::getAddressForCoordinates($latitude, $longitude);
$geo_address = $address['formatted_address'];



                // response status will be 'OK', if able to geocode given address


                // UPDATING SERVICE
                $update_service = Services::findOrFail($id);
                $update_service->update(array_merge($request->except('picture'), [
                'user_id' => Auth::user()->id,
                'geo_address' => $geo_address
                ]));

                if ($request->has('picture')) {
                    // IMAGE
                    // $file = $request->file('picture');
                    // $directory_name = "services";
                    // $image_name = imageUpload($file, $directory_name);

                    // STORE FILE
                    $file = $request->file('picture');
                    $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString() . randomString(), $file, 'public');
                    $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/' . $image_name;


                    $update_service->picture = $image_name;
                    $update_service->update();
                }

                if ($services_category_id) {
                    // STORE CATEGORIES DATA
                    $users_categories = UsersCategories::where(['service_id' => $update_service->id])->first();
                    $users_categories->services_category_id = $services_category_id;
                    $users_categories->save();
                }

                // SUCCESS RESPONSE
                $status_code = 200;
                $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'message' => 'Successfully Updated',
                'data' => $update_service
            ];
            }

            // IF SERVICES CATEGORY ID NOT EXISTS
            else {
                $status_code = 200;
                $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'status_message' => 'Services Category not found|matched!',
            ];
            }
            // COMMIT CHANGES
            DB::commit();
        } catch (\Throwable $exception) {

                // ROLLBACK ALL CHANGES
            DB::rollback();
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // PUBLIC URL
    // VIEW ALL SERVICES BY LAT LONG, STATUS WISE, BY ID, BY SERVICES CATEGORY ID, BY USER ID
    public function allServices(Request $request)
    {
        try {
            $latitude = $request->query('latitude');
            $longitude = $request->query('longitude');
            $status = $request->query('status');
            $id = $request->query('id');
            $services_category_id = $request->query('services_category_id');
            $user_id = $request->query('user_id');

            $auth_user_id = $request->query('auth_user_id');


            $max_distance = $request->query('max_distance');

            if (!$max_distance) {
                $max_distance = 10;
            }

            if ($latitude && $longitude) {
                // SELECT SERVICE WHERE BY LAT LONG
                $gr_circle_radius = 6371;
                $distance_select = sprintf(
                    " (%d * acos(cos(radians(%s))" .
                " * cos(radians(latitude)) " .
                " * cos(radians(longitude) - radians(%s)) " .
                " + sin(radians(%s)) * sin(radians(latitude)) " .
                " ) " .
                " ) ",
                    $gr_circle_radius,
                    $latitude,
                    $longitude,
                    $latitude
                );
            }

            // GET SERVICES
            $Services = Services::with([
            'service_user',
            'service_category',
            'user_reviews',
            'user_reviews.review_sender_user'
            ])
            ->with(['service_user' => function ($query) {
                $query->withCount([
                        'user_rating AS total_service_user_rating' => function ($query) {
                            $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                        }
                    ]);
            }])
            ->withCount('user_reviews');

            // IF LAT LONG EXISTS IN REQUEST
            if ($latitude && $longitude) {
                $Services = $Services->having(DB::raw($distance_select), '<=', $max_distance);
            }

            // STATUS (ACTIVE/PAUSE)
            if ($status) {
                $Services = $Services->where([
                'services.service_status' => $status
                 ]);
            }

            // CATEGORY (id)
            if ($services_category_id) {
                $Services = $Services->where([
                'services.services_category_id' => $services_category_id
                ]);
            }

            // USER ID (id)
            if ($user_id) {
                $Services = $Services->where([
                'services.user_id' => $user_id
                 ]);
            }

            // IF REQUEST SERVICE BY ID
            if ($id) {
                if ($auth_user_id) {
                    $Services = $Services->where('user_id', '!=', $auth_user_id);
                }


                $Services = $Services->where('id', $id)->first();
                // INCREMENT SERVICE VIEW ON UNIQUE IP ADDRESS AFTER 24HR
                incrementServiceViews($Services, $request);
            }

            if (!$id) {
                $Services = $Services->orderBy('views', 'DESC');
                $Services = $Services->groupBy('id');

                if ($auth_user_id) {
                    $Services = $Services->where('user_id', '!=', $auth_user_id);
                }

                $Services = $Services->simplePaginate(10);
            }

            // DATA TO ARRAY
            $services_data_array = $Services->toArray();

            $status_code = 200;

            if ($id) {
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'data' => $services_data_array
                ];
            } else {
                $json_response_array = [
                'status_code' => 200,
                'status_message' => 'success'
             ];
                $json_response = array_merge($json_response_array, $services_data_array);
            }
        } catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // STORE SERVICES RATINGS
    public function servicesRating(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'time' => 'required|integer|between:1,5',
            'communication' => 'required|integer|between:1,5',
            'skills' => 'required|integer|between:1,5',
            'quality_of_work' => 'required|integer|between:1,5',
            'professionalism' => 'required|integer|between:1,5'

        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
        }
        // ELSE STORE DATA WITH CHECK MODEL
        else {

         // DB TRANSACTION BEGIN
            DB::beginTransaction();

            try {
                $latitude = $request->query('latitude');
                // GET SERVICE TOKEN
                $service = Services::where(['id' => $request->input('service_id')])->first();
                if ($service) {
                    $service_id = $service->id;

                    $time = $request->input('time');
                    $communication = $request->input('communication');
                    $skills = $request->input('skills');
                    $quality_of_work = $request->input('quality_of_work');
                    $professionalism = $request->input('professionalism');

                    $star_rating_average = (($time + $communication + $skills + $quality_of_work + $professionalism) / 25) * 5;

                    if (!ServicesRating::where(['service_id' =>  $service_id, 'user_id' => Auth::user()->id])->exists()) {

                // CREATE SERVICE RATING
                        $services_rating = new ServicesRating();
                        $services_rating->service_id = $service_id;
                        $services_rating->user_id = Auth::user()->id;
                        $services_rating->time = $time;
                        $services_rating->communication = $communication;
                        $services_rating->skills = $skills;
                        $services_rating->quality_of_work = $quality_of_work;
                        $services_rating->professionalism = $professionalism;
                        $services_rating->star_rating = $star_rating_average;

                        $services_rating->save();

                        // SUCCESS RESPONSE
                        $status_code = 200;
                        $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Rating successfully Done.',
                ];
                    } else {

                        // UPDATE SERVICE RATING
                        $services_rating = ServicesRating::where('service_id', $service_id)->first();
                        $services_rating->time = $time;
                        $services_rating->communication = $communication;
                        $services_rating->skills = $skills;
                        $services_rating->quality_of_work = $quality_of_work;
                        $services_rating->professionalism = $professionalism;
                        $services_rating->star_rating = $star_rating_average;
                        $services_rating->update();

                        // RATING ALREADY EXISTS WITH SAME SERIVE & USER RESPONSE
                        $status_code = 200;
                        $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Rating successfully Updated.',
                ];
                    }
                } else {
                    // SERVICES NOT FOUND RESPONSE
                    $status_code = 200;
                    $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => 'Services not found.',
            ];
                }
                // COMMIT CHANGES
                DB::commit();
            } catch (\Throwable $exception) {
                // ROLLBACK ALL CHANGES
                DB::rollback();
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // DELETE SERVICES RATINGS
    public function servicesRatingDelete(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'rating_id' => 'required'
        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 406;
            $json_response = [
                'status_code' => $status_code,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
        }
        // ELSE STORE DATA WITH CHECK MODEL
        else {

         // DB TRANSACTION BEGIN
            DB::beginTransaction();

            try {
                $rating_id = $request->query('rating_id');

                $rating = ServicesRating::where(['id' => $rating_id])->first();

                if ($rating) {
                    if (ServicesRating::where(['id' => $rating_id, 'user_id' => auth()->user()->id])->delete()) {
                        // SUCCESS RESPONSE
                        $status_code = 200;

                        $json_response = [
                    'status_code' => 200,
                    'status_message' => 'success',
                    'message' => 'Rating Deleted successfully.',
                ];
                    } else {
                        // UNAUTHORIZED USER
                        $status_code = 200;

                        $json_response = [
                    'status_code' => 401,
                    'status_message' => 'Unauthorized User',
                    'message' => 'Rating is not linked with Auth User.',
                ];
                    }
                } else {

            // Ratings NOT FOUND RESPONSE
                    $status_code = 200;

                    $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => 'Rating Id not found.',
            ];
                }
                // COMMIT CHANGES
                DB::commit();
            } catch (\Throwable $exception) {
                // ROLLBACK ALL CHANGES
                DB::rollback();
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // STORE SERVICE REVIEWS
    public function servicesReviews(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'review' => 'required'
        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
        }
        // ELSE STORE DATA WITH CHECK MODEL
        else {

         // DB TRANSACTION BEGIN
            DB::beginTransaction();

            try {

                // GET SERVICE TOKEN
                $service = Services::where(['id' => $request->input('service_id')])->first();
                if ($service) {
                    $service_id = $service->id;

                    if (!ServicesReviews::where(['service_id' =>  $service_id, 'user_id' => Auth::user()->id])->exists()) {

                       // CREATE SERVICE RATING
                        $services_review = new ServicesReviews();
                        $services_review->service_id = $service_id;
                        $services_review->user_id = Auth::user()->id;
                        $services_review->review = $request->input('review');
                        $services_review->save();

                        // SUCCESS RESPONSE
                        $status_code = 200;
                        $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Review successfully done.',
                ];
                    } else {

                         // UPDATE SERVICE RATING
                        $services_review = ServicesReviews::where('service_id', $service_id)->first();
                        $services_review->review = $request->input('review');
                        $services_review->update();

                        // RATING ALREADY EXISTS WITH SAME SERIVE & USER RESPONSE
                        $status_code = 200;
                        $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Review successfully Updated.',
                ];
                    }
                } else {

            // SERVICES NOT FOUND RESPONSE
                    $status_code = 200;
                    $json_response = [
                    'status_code' => 406,
                    'status_message' => 'error',
                    'message' => 'Services not found.',
                 ];
                }

                // COMMIT CHANGES
                DB::commit();
            } catch (\Throwable $exception) {
                // ROLLBACK ALL CHANGES
                DB::rollback();
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // DELETE SERVICES REVIEWS
    public function servicesReviewsDelete(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'review_id' => 'required'
        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
        }
        // ELSE STORE DATA WITH CHECK MODEL
        else {

         // DB TRANSACTION BEGIN
            DB::beginTransaction();

            try {
                $review_id = $request->query('review_id');

                $reviews = ServicesReviews::where(['id' => $review_id])->first();

                if ($reviews) {
                    if (ServicesReviews::where(['id' => $review_id, 'user_id' => auth()->user()->id])->delete()) {
                        // SUCCESS RESPONSE
                        $status_code = 200;
                        $json_response = [
                    'status_code' => 200,
                    'status_message' => 'success',
                    'message' => 'Review Deleted successfully.',
                ];
                    } else {
                        // UNAUTHORIZED USER
                        $status_code = 200;
                        $json_response = [
                    'status_code' => 401,
                    'status_message' => 'Unauthorized User',
                    'message' => 'Review is not linked with Auth User.',
                ];
                    }
                } else {
                    // Ratings NOT FOUND RESPONSE
                    $status_code = 200;
                    $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => 'Review Id not found.',
            ];
                }

                // COMMIT CHANGES
                DB::commit();
            } catch (\Throwable $exception) {
                // ROLLBACK ALL CHANGES
                DB::rollback();
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // DELETE SERVICE BY ID
    public function destroy($id)
    {

        // DB TRANSACTION BEGIN
        DB::beginTransaction();
        try {
            // SELECT SERVICE WHERE ID
            $delete_service = Services::where(['id' => $id, 'user_id' =>  Auth::user()->id])->delete();

            if ($delete_service) {
                // SUCCESS RESPONSE
                $status_code = 200;
                $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'message' => "Successfully Deleted",
            ];
            } else {
                // ERROR  RESPONSE
                $status_code = 200;

                $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => "Service Not Found.",
            ];
            }
            // COMMIT CHANGES
            DB::commit();
        } catch (\Throwable $exception) {
            // ROLLBACK ALL CHANGES
            DB::rollback();
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
