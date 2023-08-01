<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        // VALIDATE EMAIL
        $request->validate([
            'email' => 'required|string|email',
        ]);

        // GET USER DATA & VALIDATE USER EXISTS
        $user = User::where('email', $request->email)->first();

        // IF USER NOT FOUND - FAIL RESPONSE
        if (!$user) {
            $json_response = [
                'status_code' => 406,
                'status_message' => 'fail',
                'error' => 'We cant find a user with that e-mail address.',
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

        // CREATE PASSWORD RESET RECORD IN PASSWORD RESET TABLE WITH OTP AND USER ID
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                // GENERATE RANDOM OTP
                'otp' => rand(100000, 999999)
            ]
        );

        // IF USERS RECORDS EXISTS & PASSWORD RESET OTP GENERATED- SEND EMAIL NOTIFICATION
        if ($user && $passwordReset) {
            // EMAIL NOTIFY WITH OTP
            $user->notify(
                new PasswordResetRequest($passwordReset->otp)
            );
        }

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => 'We have e-mailed your password reset OTP!',
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'string|email',
            'otp' => 'string'
        ]);



       if($request->otp && !$request->password){
            // GET DB RECORD WHERE EMAIL & OTP VERIFY
            $passwordReset = PasswordReset::where([
                ['otp', $request->otp],
                ['email', $request->email],
            ])->first();

            // IF OTP NOT EXISTS
            if (!$passwordReset) {
                // FAIL RESPONSE
                $json_response = [
                    'status_code' => 406,
                    'status_message' => 'fail',
                    'error' => 'This password reset otp is invalid',
                ];
                return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }
            $json_response = [
                'status_code' => 200,
                'status_message' => 'fail',
                'message' => 'This password reset otp is Valid.',
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

        $passwordReset = PasswordReset::where([
            ['otp', $request->otp],
            ['email', $request->email],
        ])->first();


        // GET USER RECORD
        $user = User::where('email', $passwordReset->email)->first();

        // IF USER NOT EXISTS
        if (!$user) {
            // FAIL RESPONSE
            $json_response = [
                'status_code' => 406,
                'status_message' => 'fail',
                'error' => 'We cant find a user with that e-mail address.',
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

        // CREATE PASSWORD WITH HASHING
        $password_request = bcrypt($request->password);
        $user->password = $password_request;
        $user->save();

        // DELETE PASSWORD RESET OTP & USER IF FROM PASSWORD RESET TABLE IN DB
        $passwordReset->delete();

        // PASSWORD RESET NOTIFY WITH EMAIL
        $user->notify(new PasswordResetSuccess($passwordReset));


        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        // LOGIN
        if (auth()->attempt($credentials)) {
            // IF LOGIN SUCCESS
            // GENERATE RANDOM PASSPORT TOKEN FOR API
            $token = auth()->user()->createToken(rand(99999, 1000000))->accessToken;
            // SUCCESS RESPONSE
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'message' => "Password changed Successfully.",
                'id' => $user->id,
                'name' => $user->name,
                'email' => $request->email,
                'token' => $token
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

    }
}
