<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services;
use App\HireServices;
// use App\HireLocations;
use App\UsersWallet;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;

// use Throwable;
class HireServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'service_hours_want' => 'required|not_in:0',
            'service_receiver_id' => 'required|exists:services,id',
            'offer_instructions' => 'required'
        ]);

        // IF VALIDATOR FAIL
        if ($validator->fails()) {
            $status_code = 200;
            $json_response = [
                'status_code' => 200,
                'status_message' => 'error',
                'error' => $validator->messages()
            ];
        }
        else {
            try {

                // GET INPUT VALUES
                $service_receiver_id = $request->service_receiver_id;
                $service_hours_want = $request->service_hours_want;

                // DB TRANSACTION BEGIN
                DB::beginTransaction();

                try {

                    // GET RECEIVER SERVICE DETAIL
                    $receiver_service_detail = Services::where(['id' => $service_receiver_id])->first();
                    // RECEIVER USER ID
                    $user_receiver_id = $receiver_service_detail->user_id;

                    // RECEIVER USER DETAIL
                    $user_receiver_detail = User::where(['id' => $user_receiver_id])->first();
                    $receiver_hourly_rate = $user_receiver_detail->hourly_rate;

                    $amount_to_be_paid = $receiver_hourly_rate * $service_hours_want;

                    // STORE SWAP SERVICES
                    $request_fields = array_merge(
                        $request->all(),
                        [
                            'user_sender_id' => auth()->user()->id,
                            'user_receiver_id' => $user_receiver_id,
                            'amount_to_be_paid' => $amount_to_be_paid
                        ]
                    );

                    // CREATE HIRE SERVICES
                    $hire_services = HireServices::create($request_fields);

                    // SEND NOTIFICATION
                    $type = 'swap';
                    $info = 'Hire Service Received.';
                    addNotification($user_receiver_id, $type, $info);


                    $status_code = 200;
                    $json_response = [
                        'status_code' => $status_code,
                        'status_message' => 'success',
                        'message' => 'Hire Services Created.',
                    ];

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
            // IF CATCH ERROR IN STORING DATA
            catch (\Throwable $exception) {
                $status_code = 200;
                $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
              ];
            }

            $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'message' => 'Hire Services Created.'
        ];
        }
        // ELSE END
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function show(Request $request)
    {

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
            try {

                // GET STATUS PARAMETER FROM API URL
                $id = $request->query('id');

                $type = $request->query('type');
                $status = $request->query('status');

                // HIRE CHECK AVAILABILITY
                // GET SINGLE SERVICE BY ID
                if ($id) {
                    $Hire_services_by_user_sender_id_check = HireServices::where('user_sender_id', auth()->user()->id)
                                                                ->where('id', $id)
                                                                ->exists();
                    $Hire_services_by_user_receiver_id_check = HireServices::where('user_receiver_id', auth()->user()->id)
                                                                ->where('id', $id)
                                                                ->exists();
                }
                // GET ALL SERVICES OF AUTH USER
                else {
                    $Hire_services_by_user_sender_id_check = HireServices::where('user_sender_id', auth()->user()->id)->exists();
                    $Hire_services_by_user_receiver_id_check = HireServices::where('user_receiver_id', auth()->user()->id)->exists();
                }


                // IF HIRE CHECK AVAILABLE

                if ($Hire_services_by_user_sender_id_check || $Hire_services_by_user_receiver_id_check) {


                // HIRE OFFERS
                    $hire_services = HireServices::with([
                    'sender_user',
                    'receiver_user',
                    'receiver_service',
                    'declined_by_user'
                    ])
                    ->withCount([
                        'service_ratings AS total_star_rating' => function ($query) {
                            $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_star_rating"));
                        },
                        'service_ratings'
                    ]);

                    // STATUS 'pending', 'active', 'completed', 'cancelled'
                    if ($status) {
                        $hire_services->where(['service_status' => $status]);
                    }

                    // OFFER
                    $hire_services_get = $hire_services;
                    // IF TYPE IS OFFER SENT OR OFFER RECEIVED
                    if ($type) {
                        if ($type == 'offersent') {
                            $hire_services_get = $hire_services_get->where('user_sender_id', auth()->user()->id);
                        } elseif ($type == 'offersreceived') {
                            $hire_services_get = $hire_services_get->where('user_receiver_id', auth()->user()->id);
                        }
                    }

                    // IF REQUEST SINGLE HIRE SERVICE BY ID
                    if ($id) {
                        $hire_services_get = $hire_services_get
                    ->where('id', $id)
                    ->first();
                    } else {
                        $hire_services_get = $hire_services_get
                        ->where(function ($query) {
                            $query->where('user_sender_id', auth()->user()->id)
                                  ->orWhere('user_receiver_id', auth()->user()->id);
                        })
                        ->orderBy('id', 'DESC')
                        ->simplePaginate(10);
                    }

                    $hire_data_array_sent = $hire_services_get->toArray();
                    $status_code = 200;
                    $json_response_array = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    ];

                    // WRAP SINGLE HIRE SEVICE IN DATA KEY
                    if ($id) {
                        $hire_data_array_sent = [
                    'data' => $hire_data_array_sent
                ];
                    }
                    $json_response = array_merge($json_response_array, $hire_data_array_sent);
                }

                // IF HIRE CHECK NOT AVAILABLE

                else {
                    $status_code = 200;
                    $json_response = [
                        'status_code' => 400,
                        'status_message' => 'error',
                        'message' => 'Hire Service not linked with Auth User!'
                ];
                }
            }
            // IF CATCH ERROR IN STORING DATA
            catch (\Throwable $exception) {
                $status_code = 200;
                $json_response = [
            'status_code' => 400,
            'status_message' => 'error',
            'message' => $exception->getMessage()
            ];
            }
        }
        // ELSE END

        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function update(Request $request, $id)
    {
        $service_status = $request->service_status;

        try {

            // DB TRANSACTION BEGIN
            DB::beginTransaction();
            try {

                // UPDATING hire SERVICE
                $update_hire_service = HireServices::findOrFail($id);

                // STORE hire SERVICES
                $request_fields = array_merge(
                    $request->all()
                );
                $update_hire_service->update($request_fields);

                // UPDATE STATUS
                $receiver_service_confirmed = $update_hire_service->receiver_service_confirmed;
                $sender_service_completed = $update_hire_service->sender_service_completed;
                $user_receiver_id = $update_hire_service->user_receiver_id;
                if($receiver_service_confirmed == '1') {
                    $service_status = 'active';

                    // SEND NOTIFICATION
                    $type = 'hire';
                    $info = 'Hire Service Status Updated To Active.';
                    addNotification($user_receiver_id,$type,$info);

                }
                if ($sender_service_completed == 1) {
                    $service_status = 'completed';
                    // SEND NOTIFICATION
                    $type = 'hire';
                    $info = 'Hire Service Status Updated To Completed.';
                    addNotification($user_receiver_id, $type, $info);

                }

                if ($service_status == 'cancelled' || $service_status == 'declined') {
                    $delete_by_user = auth()->user()->id;
                    // SEND NOTIFICATION
                    $type = 'hire';
                    $info = 'Hire Service Status Updated To Cancelled.';
                    addNotification($user_receiver_id, $type, $info);

                } else {
                    $delete_by_user = null;
                }

                $request_field =
                    [
                        'service_status' => $service_status,
                        'delete_by_user' => $delete_by_user
                    ];

                $update_hire_service->update($request_field);


                $status_code = 200;
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Hire Services Updated.'
                ];

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
            // CATCH END
        }
        // IF CATCH ERROR IN STORING DATA
        catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
              ];
        }
        // ELSE END
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function edit($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
