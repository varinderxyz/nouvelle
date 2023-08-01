<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
// use App\Users_services;
// use App\Services;
// use App\ServicesCategory;
// use App\ServicesLocations;
// use App\Certifications;
// use App\ServicesRating;
// use App\UsersLocations;
// use App\UsersCategories;
// use App\Locations;
// use File;
// use Image;
use App\UserRating;
use Illuminate\Support\Facades\DB;
use Geocoder;
// use GuzzleHttp\Client;
use App\UsersWallet;
use Validator;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function test2()
    {
        $access_token = 'EAAKiEZCREuwwBAHQoZChJPtBbfFT21OgqBoZCPWEfs5UshjlFVskKnmvVZAYA9vniCEKWoLbZB3n33WPlh7XdOZBvuJAlFlRPaWOTuZB5PDkkKYEUQ3fvf76IRjP5AEoePT8ELwyZAZC73ZCVQ1tfWmZBPYPDTBZCmx8zZCHnZBnftjh0veFZCCjKViLDQD0OBNKGCOPuKZBBJXpMib8ANbFqdbzsIbQ';

        $user_details = "https://graph.facebook.com/me?access_token=" . $access_token;

        $response = file_get_contents($user_details);
        $response = json_decode($response);
        return response()->json($response, 200);
    }

    public function test()
    {
        dd(distanceSelect(30.65195, 76.73604));
        $geo_coder_api = '{
   "results" : [
      {
         "formatted_address" : "277 Bedford Ave, Brooklyn, NY 11211, USA",
         "geometry" : {
            "location" : {
               "lat" : 40.7142205,
               "lng" : -73.9612903
            },
            "location_type" : "ROOFTOP",
            "viewport" : {
               "northeast" : {
                  "lat" : 40.71556948029149,
                  "lng" : -73.95994131970849
               },
               "southwest" : {
                  "lat" : 40.7128715197085,
                  "lng" : -73.9626392802915
               }
            }
         },
         "place_id" : "ChIJd8BlQ2BZwokRAFUEcm_qrcA",
         "types" : [ "street_address" ]
      }
   ],
   "status" : "OK"
}';

        $geo_coder_api_decode = json_decode($geo_coder_api, true);

        // foreach($geo_coder_api_decode['results'] as $results){
        //     $data = $results->formatted_address;
        // }

        // $geo_address = $geo_coder_api_decode['results'][0]['formatted_address'];
        $geo_address = $geo_coder_api_decode['results'][0]['formatted_address'];

        dd($geo_address);


        // $address = Geocoder::getCoordinatesForAddress('Samberstraat 69, Antwerpen, Belgium');
        $address = Geocoder::getAddressForCoordinates(40.714224, -73.961452);

        $lat = $address[0]->formatted_address;

        /*
          This function returns an array with keys
          "lat" => 40.7142205
          "lng" => -73.9612903
          "accuracy" => "ROOFTOP"
          "formatted_address" => "277 Bedford Ave, Brooklyn, NY 11211, USA",
          "viewport" => [
            "northeast" => [
              "lat" => 37.3330546802915,
              "lng" => -122.0294342197085
            ],
            "southwest" => [
              "lat" => 37.3303567197085,
              "lng" => -122.0321321802915
            ]
          ]
        */

        dd($lat);
    }


    //  VIEW USER DATA WHERE ID AUTH USER
    public function index()
    {
        // GET USER DATA
        $user = User::with([
            'users_categories',
            'user_services',
            'certifications',
            'user_wallet'
        ])
        ->withCount([
            'users_categories',
            'certifications',
            'user_services',
            'user_rating AS user_ratings' => function ($query){
                $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5) as user_ratings"));
            },
            'user_rating',
            'swap_invites',
            'active_swap_receive',
            'active_swap_sent',
            'notifications'
        ])
        ->where('id', auth()->user()->id)
        ->first();

        $user_data_array = $user->toArray();
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'data' => $user_data_array,
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // UPDATE USERS DATA
    public function update(Request $request)
    {
        // VALIDATION
        $this->validate(
            $request,
            [
                'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:8000',
            ]
        );

        // USER DETAIL UPDATE
        if (auth()->user()->role === "admin") {
            $user_detail = User::findOrFail($request->id);
        } else {
            $user_detail = User::findOrFail(auth()->user()->id);
        }

        // Update phone verified status
        if ($request->has('phone')){
            if ($request->phone != $user_detail->phone) {
                $user_detail->phone_verified = '0';
                $user_detail->update();
            }
        }

        // Update all inputs Requests
        $user_detail->update($request->all());

        if ($request->has('longitude') && $request->has('latitude')) {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            if (!$latitude && !$longitude) {
                $latitude = 34.064911;
                $longitude = 118.349949;
            }
            $address = Geocoder::getAddressForCoordinates($latitude, $longitude);
            $geo_address = $address['formatted_address'];
            $user_detail->geo_address = $geo_address;
            $user_detail->update();
        }

        if ($request->has('picture')) {
            // $file = $request->file('picture');
            // $directory_name = "profile_pictures";
            // $image_name = imageUpload($file, $directory_name);

            // STORE FILE
            $file = $request->file('picture');
            $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString() . randomString(), $file, 'public');
            $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/' . $image_name;

            $user_detail->picture = $image_name;
            $user_detail->update();
        }

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => 'Profile Successfully Updated.',
            'data' => $user_detail
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    //  VIEW USER DATA WHERE ID AUTH USER
    public function allUsers(Request $request)
    {
        $user_id = $request->query('user_id');
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');

        // SELECT SERVICE WHERE BY LAT LONG
        $gr_circle_radius = 6371;
        $max_distance = 10;
        $distance_select = sprintf(
            " (%d * acos(cos(radians(%s)) " .
                " * cos(radians(latitude)) " .
                " * cos(radians( longitude) - radians(%s)) " .
                " + sin(radians(%s)) * sin(radians(latitude)) " .
                " ) " .
                " ) ",
            $gr_circle_radius,
            $latitude,
            $longitude,
            $latitude
        );

        // GET USER DATA
        $user = User::with([
            'users_categories',
            'user_services',
            'certifications',
            'user_reviews',
            'user_reviews.review_sender_user'
        ])
        ->withCount([
            'users_categories',
            'certifications',
            'user_services',
            'user_reviews'
        ]);


        // IF LAT LONG EXISTS IN REQUEST
        if ($latitude && $longitude) {
            $user = $user->having(DB::raw($distance_select), '<=', $max_distance);
        }

        // USER ID (id)
        if ($user_id) {
            $user = $user->where([
                'users.id' => $user_id,
            ]);
            $user = $user->groupBy('users.id')->first();
        }

        if (!$user_id) {
            $user = $user->groupBy('users.id')->get();
        }

        $user_data_array = $user->toArray();
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'data' => $user_data_array,
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function walletBalance()
    {
        $wallet_balance = UsersWallet::where('user_id', auth()->user()->id)->first();

        // IF USER WALLET EXISTS
        if ($wallet_balance) {

            // WALLET DATA TO ARRAY
            $wallet_balance_array = $wallet_balance->toArray();

            // JSON RESPONSE
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'data' => $wallet_balance_array,
            ];
        }
        // IF USER WALLET NOT EXISTS CREATE WALLET
        else {

            // GENERATE USER WALLET
            $Wallet = new UsersWallet();
            $Wallet->user_id = auth()->user()->id;
            $Wallet->wallet_balance = 0;
            $Wallet->save();

            // WALLET DATA TO ARRAY
            $wallet_balance_array = $Wallet->toArray();

            // JSON RESPONSE
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'data' => $wallet_balance_array,
            ];
        }

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function userRating(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
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
                // GET SERVICE TOKEN
                $user = User::where(['id' => $request->input('user_id')])->first();
                if ($user) {
                    $user_id = $user->id;
                    $time = $request->input('time');
                    $communication = $request->input('communication');
                    $skills = $request->input('skills');
                    $quality_of_work = $request->input('quality_of_work');
                    $professionalism = $request->input('professionalism');
                    $feedback = $request->input('feedback');


                    $star_rating_average = (($time + $communication + $skills + $quality_of_work + $professionalism) / 25) * 5;

                    if (!UserRating::where(['user_id' =>  $user_id, 'sender_user_id' => auth()->user()->id])->exists()) {

                // CREATE SERVICE RATING
                        $services_rating = new UserRating();
                        $services_rating->user_id = $user_id;
                        $services_rating->sender_user_id = auth()->user()->id;
                        $services_rating->time = $time;
                        $services_rating->communication = $communication;
                        $services_rating->skills = $skills;
                        $services_rating->quality_of_work = $quality_of_work;
                        $services_rating->professionalism = $professionalism;
                        $services_rating->star_rating = $star_rating_average;
                        $services_rating->feedback = $feedback;


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
                        $services_rating = UserRating::where('user_id', $user_id)->first();
                        $services_rating->user_id = $user_id;
                        $services_rating->sender_user_id = auth()->user()->id;
                        $services_rating->time = $time;
                        $services_rating->communication = $communication;
                        $services_rating->skills = $skills;
                        $services_rating->quality_of_work = $quality_of_work;
                        $services_rating->professionalism = $professionalism;
                        $services_rating->star_rating = $star_rating_average;
                        $services_rating->feedback = $feedback;

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
                    // User NOT FOUND RESPONSE
                    $status_code = 200;
                    $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => 'User not found.',
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

    public function allReviews(Request $request)
    {
        try {
            $user_id = $request->query('user_id');
            if($user_id){
                $reviews = UserRating::with('review_sender_user')
                ->where('user_id', $user_id)->select('id','user_id','star_rating as rating','sender_user_id', 'feedback', 'created_at')
                 ->orderBy('id','desc')
                ->get();
            }
            if (!$user_id) {
                $reviews = UserRating::with('review_sender_user')
                ->select('id','user_id','star_rating as rating','sender_user_id', 'feedback', 'created_at')
                ->orderBy('id','desc')
                ->get();
            }
            $reviews_array = $reviews->toArray();

            // JSON RESPONSE
            $status_code = 200;
            $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'data' => $reviews_array,
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

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function singleUser($id)
    {
        $user = User::where('id',$id)->get();
        $wallet_balance = UsersWallet::where('user_id', $id)->first();
        $json_response = [
            'user' => $user,
            'wallet_balance' => $wallet_balance
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }


    /**
     * Delete the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function deleteUser($id)
    {
        $user = User::find($id);    
        $user->delete();
        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'data' => $user
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }


}
