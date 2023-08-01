<?php

namespace App\Http\Controllers;

use App\User;
// use App\Users_services;
// use App\Services;
use App\EmailVerify;
// use App\ServicesLocation;
// use App\Locations;
// use App\UsersLocations;
// use App\ServicesCategory;
use App\UsersCategories;
use App\UsersPhoneOtpVerify;
use Auth;
use Illuminate\Http\Request;
// use File;
// use Image;
use App\Notifications\VerifyApiEmail;
use App\Notifications\VerifyApiEmailSuccess;
use Twilio\Rest\Client;
// use Twilio\Jwt\ClientToken;
use App\Notifications\ChangePassword;
use Hash;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Braintree;
use Braintree_Gateway;
use App\UsersWallet;
use Validator;
// use Throwable;
use Illuminate\Support\Facades\DB;
use Config;
use Illuminate\Support\Facades\Storage;
use Geocoder;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->gateway = new Braintree_Gateway([
            'environment' => 'sandbox',
            'merchantId' => Config::get('database.BRAINTREE_MERCHANT_ID'),
            'publicKey' => Config::get('database.BRAINTREE_PUBLIC_KEY'),
            'privateKey' => Config::get('database.BRAINTREE_PRIVATE_KEY')
        ]);
    }

    public function register(Request $request)
    {




        // VALIDATION
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|min:3|max:30|regex:/^[A-Za-z ]+$/',
                'email' => 'required|email|unique:users|max:64',
                'password' => 'required|min:6|max:30',
                // 'phone' => 'required|unique:users|min:4|max:15|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:8000',
                // 'hourly_rate' => 'required',
            ]
        );

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
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
                $services_category_id = $request->services_cat_id;
                $check_services_catg_availibility = checkCategories($services_category_id);



                if ($check_services_catg_availibility) {

            //   IMAGE UPLOAD
                    if ($request->has('picture')) {
                        // IMAGE
                        // $file = $request->file('picture');
                        // $directory_name = "profile_pictures";
                        // $image_name = imageUpload($file, $directory_name);

                        // STORE FILE
                        $file = $request->file('picture');
                        $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString() . randomString(), $file, 'public');
                        $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/' . $image_name;
                    }

                $latitude = $request->input('latitude');
                $longitude = $request->input('longitude');
                if(!$latitude && !$longitude){
                   $latitude = 34.064911;
                   $longitude = 118.349949;
                }
                  $address = Geocoder::getAddressForCoordinates($latitude, $longitude);
                  $geo_address = $address['formatted_address'];


                    // response status will be 'OK', if able to geocode given address


                    // $gateway = new Braintree_Gateway([
                    //     'environment' => 'sandbox',
                    //     'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                    //     'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
                    //     'privateKey' => env('BRAINTREE_PRIVATE_KEY')
                    // ]);

                    //Create Braintree Customer
                    $result = $this->gateway->customer()->create([
                'firstName' => $request->name,
                'email' => $request->email,
                // 'phone' => $request->phone
            ]);


                    // CREATE USER
                    $registration_data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                // 'phone' => $request->phone,
                'picture' => $image_name,
                // 'zip_code' => $request->zip_code,
                "payment_customer_id" => $result->customer->id,
                // 'willing_to_travel' => $request->willing_to_travel,
                // 'about' => $request->about,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'geo_address' => $geo_address,
                // 'hourly_rate' => $request->hourly_rate

            ]);


                    // SEPERATE IDS FROM COMMA
                    $service_categories_explodes = explode(",", $services_category_id);
                    // SEPERATE & STORE CATEGORIES DATA
                    foreach ($service_categories_explodes as $service_categories_explode) {
                        $users_categories = new UsersCategories();
                        $users_categories->services_category_id = $service_categories_explode;
                        $users_categories->user_id = $registration_data->id;
                        $users_categories->save();
                    }
                    // GENERATE PASSPORT TOKEN FOR API
                    try {
                        $generate_passport_token = $registration_data->createToken(rand(99999, 1000000))->accessToken;
                        // GET USER DETAIL
                        $user = User::where('email', $request->email)->first();

                        // GENERATE USER WALLET
                        $user_wallet = new UsersWallet();
                        $user_wallet->user_id = $registration_data->id;
                        $user_wallet->wallet_balance = 0;
                        $user_wallet->save();

                        // SUCCESS RESPONSE WITH TOKEN
                        $status_code = 200;
                        $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'token' => $generate_passport_token,
                "payment_customer_id" => $result->customer->id,
                ];
                    } catch (\RuntimeException $e) {

                         // ROLLBACK CREATED USER
                        // UsersLocations & UsersWallet Automatically Rollbacked (Primary Key)
                        User::where(['id' => $registration_data->id])->delete();

                        $status_code = 200;
                        $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'status_message' => 'Personal access client not found',
            ];
                    }
                    // CATCH END
                }
                // IF CHECK CATEGORIES & LOCATIONS END

                // IF SERVICES CATEGORY OR LOCATIONS ID NOT EXISTS
                else {
                    $status_code = 200;
                    $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => 'Services Category not found|matched!'
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
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function sendEmailVerifyOtp(Request $request)
    {
        try {
            // GET USER DATA WITH AUTH
            $users_data = User::where('email', auth()->user()->email)->first();

            // EmailVerify
            $email_Verify = EmailVerify::updateOrCreate(
                ['email' => $users_data->email],
                [
                'email' => $users_data->email,
                 'otp' => rand(100000, 999999),
             ]
            );

            // EMAIL NOTIFICATION
            $users_data->notify(new VerifyApiEmail($email_Verify->otp));

            // SUCCESS RESPONSE
            $response_code = 200;

            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'message' => 'Email Verify Otp has been sent on your email.',
                ];
        } catch (\Exception $e) {
            // ERROR RESPONSE
            $response_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return response()->json($json_response, $response_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'otp' => 'required|string'
        ]);

        try {
            // EMAIL VERIFY WITH OTP REQUEST
            $emailVerify = EmailVerify::where([
                ['otp', $request->otp],
                ['email', auth()->user()->email],
            ])
            ->first();

            // IF OTP NOT VALID
            if (!$emailVerify) {
                $json_response = [
        'status_code' => 406,
        'status_message' => 'fail',
        'error' => 'This otp is invalid',
    ];

                return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            // GET USER DATA TO VALIDATE RECORD EXISTS
            $user = User::where('email', $emailVerify->email)->first();
            if (!$user) {
                $json_response = [
        'status_code' => 406,
        'status_message' => 'fail',
        'error' => 'We cant find a user with that e-mail address.',
    ];
                return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            // DELETE EMAIL OTP RECORD FROM DATABASE TABLE
            $emailVerify->delete();

            // EMAIL NOTIFICATION FOR SUCCESS RESPONSE
            $user->notify(new VerifyApiEmailSuccess($emailVerify));

            // UPDATE RECORD IN USERS TABLE EMAIL VERIFIED TO 1
            $user->email_verified = "1";
            $user->save();

            // SUCCESS RESPONSE
            $response_code = 200;

            $json_response = [
    'status_code' => 200,
    'status_message' => 'success',
    'message' => 'Email Verified Successfully.',
];
        } catch (\Exception $e) {
            // ERROR RESPONSE
            $response_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return response()->json($json_response, $response_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function sendSms(Request $request)
    {
        // VALIDATION
        $validator = Validator::make(
            $request->all(),
            [
                'phone' => 'required|unique:users|min:4|max:15|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'
            ]
        );

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'error' => $validator->messages(),
            ];
            return response()->json($json_response, $status_code, []);
        }


        $accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
        $authToken  = config('app.twilio')['TWILIO_AUTH_TOKEN'];
        $appSid     = config('app.twilio')['TWILIO_APP_SID'];
        $client = new Client($accountSid, $authToken);
        try {
            // OTP VERIFY
            $otp_generate = mt_rand(100000, 999999);

            try {
                // Use the client to do fun stuff like send text messages!
                $m = $client->messages->create(
                // the number you'd like to send the message to
                $request->phone,
               array(
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => $appSid,
                    // the body of the text message you'd like to send
                    'body' => $otp_generate.' is the One Time Password(OTP) for activation of SeeSaw account. Valid for 5 Minutes.'
                )
           );
            $user = User::where('id', auth()->user()->id)->first();
            $user->phone = $request->phone;
            $user->phone_verified = '0';
            $user->update();

            } catch (Services_Twilio_RestException $e) {
                // Return and render the exception object, or handle the error as needed
                // ERROR  RESPONSE
                $status_code = 200;
                $json_response = [
                    'status_code' => 406,
                    'status_message' => 'error',
                    'message' => $e->getMessage(),
                ];
                return response()->json($json_response, $status_code, []);

            };

            $otp_store = new UsersPhoneOtpVerify();
            $otp_store->user_id = auth()->user()->id;
            $otp_store->otp = $otp_generate;
            $otp_store->save();

            // SUCCESS RESPONSE
            $status_code = 200;
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'message' => 'OTP Send Successfully.',
            ];
        } catch (Exception $e) {

            // ERROR  RESPONSE
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function phoneOtpVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|integer|digits_between:6,6'
        ]);

        $user_id = auth()->user()->id;
        $otp = $request->otp;

        if (UsersPhoneOtpVerify::where(['user_id' => $user_id, 'otp' => $otp])->exists()) {
            if (UsersPhoneOtpVerify::where('created_at', '>', Carbon::now('Asia/Kolkata')->subMinutes(5)->toDateTimeString())->first()) {
                // UPDATE STATUS IN USERS TABLE
                $user = User::where('id', $user_id)->first();
                $user->phone_verified = '1';
                $user->update();

                $status_code = 200;
                $json_response = [
                    'status_code' => 200,
                    'status_message' => 'success',
                    'message' => 'Otp Successfully Verified',
                ];
            } else {
                $status_code = 200;
                $json_response = [
                    'status_code' => 406,
                    'status_message' => 'fail',
                    'error' => 'OTP expired!'
                ];
            }
        } else {
            $status_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'fail',
                'error' => 'Wrong OTP or OTP doesnot exists!'
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function login(Request $request)
    {

        // VALIDATION
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|exists:users|max:64',
            ]
        );

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
            'status_code' => 400,
            'status_message' => 'error',
            'error' => "Email doesn't exist.",
         ];
            return response()->json($json_response, 200, []);
        }


        // GET DATA
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        // LOGIN ATTEMPT
        if (auth()->attempt($credentials)) {
            // IF LOGIN SUCCESS
            // GENERATE RANDOM PASSPORT TOKEN FOR API
            $token = auth()->user()->createToken(rand(99999, 1000000))->accessToken;
            // SUCCESS RESPONSE
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => $request->email,
                'token' => $token
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }
        // IF WRONG USERNAME OR PASSWORD
        else {

            // FAIL RESPONSE
            $json_response = [
                'status_code' => '401',
                'status_message' => 'fail',
                'error' => 'Wrong usename or password!'
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }
    }

    public function facebookLogin(Request $request)
    {
        $access_token = $request->query('access_token');
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->get('https://graph.facebook.com/me?fields=name,email,about,address,gender,hometown,location,picture&access_token=' . $access_token);
            $facebookUser = json_decode($res->getBody());

            // COLLECT USER DATA
            $user_name = $facebookUser->name;
            $user_email = $facebookUser->email;
            $user_hometown = $facebookUser->hometown->name;

            // USER PROFILE PIC 50X50
            // $user_picture = $facebookUser->picture->data->url;

            // IF USER ALREADY EXISTS - ONLY GENERATE NEW TOKEN
            if (User::where('email', $user_email)->exists()) {

                //Get the user
                $user = User::where('email', '=', $user_email)->first();

                //Now log in the user if exists
                if ($user != null) {
                    Auth::loginUsingId($user->id);

                    // GENERATE PASSPORT TOKEN FOR API
                    $generate_passport_token = auth()->user()->createToken(rand(99999, 1000000))->accessToken;

                    // JSON RESPONSE
                    $status = 200;
                    $json_response = [
                    'status_code' => 200,
                    'status_message' => 'success',
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'token' => $generate_passport_token
                ];
                }
            }
            // CREATE NEW USER ACCOUNT
            else {
                // CREATE NEW USER
                $registration_data = User::create([
                    'name' => $user_name,
                    'email' => $user_email,
                    'geo_address' => $user_hometown,
                    'facebook_verified' => '1',
                    'email_verified' => '1',
                ]);

                // GENERATE PASSPORT TOKEN FOR API
                $generate_passport_token = $registration_data->createToken(rand(99999, 1000000))->accessToken;

                // GET USER DETAIL
                $user = User::where('email', $user_email)->first();
                // JSON RESPONSE
                $status = 200;
                $json_response = [
                    'status_code' => 200,
                    'status_message' => 'success',
                    'id' => $user->id,
                    'name' => $user_name,
                    'email' => $user_email,
                    'token' => $generate_passport_token
                ];
            }
        } catch (RequestException $e) {
            $status = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'data' => 'Invalid Login Or Session is expired!'
            ];
        }

        return response()->json($json_response, $status, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'old_password'     => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $data = $request->all();

        $user = User::find(auth()->user()->id);

        if (Hash::check($data['old_password'], $user->password)) {
            $user->password = bcrypt($request->input('new_password'));
            $user->update();

            // PASSWORD CHANGE NOTIFY WITH EMAIL
            $user->notify(new ChangePassword());

            $response = [
                'status_code' => 200,
                'status_message' => 'success',
                'success' => 'Password Successfully Changed.'
            ];
        } else {
            $response = [
                'status_code' => 406,
                'status_message' => 'error',
                'error' => 'Old Password Not Matched!'
            ];
        }
        return response()->json($response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function logoutApi()
    {
        // IF LOGIN
        if (Auth::check()) {
            // DELETE PASSPORT API TOKEN FROM Aauth Acess Token TABLE FROM DATABASE
            Auth::user()->AauthAcessToken()->delete();
        }
        // SUCCESS RESPONSE
        $response = [
            'status_code' => 200,
            'status_message' => 'success',
            'error' => 'Logout Successfully'
        ];
        return response()->json($response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
