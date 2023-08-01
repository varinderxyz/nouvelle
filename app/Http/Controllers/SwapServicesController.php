<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SwapServices;
use App\Services;
use App\UsersWallet;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;

class SwapServicesController extends Controller
{
    public function store(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'service_hours_want' => 'required|not_in:0',
            'service_receiver_id' => 'required|exists:services,id|not_in:0'
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
            try {

                // SERVICE SENDER ID
                $service_sender_id = $request->service_sender_id;
                // SERVICE RECEIVER ID
                $service_receiver_id = $request->service_receiver_id;

                // HOURS OF SERVICE WANT
                $service_hours_want = $request->service_hours_want;

                // USER RECEIVER ID
                $user_receiver_id = Services::where(['id' => $service_receiver_id])->first();
                $receiver_service_hourly_rate = $user_receiver_id->hourly_rate;
                $user_receiver_id = $user_receiver_id->user_id;


                        // DB TRANSACTION BEGIN
                    DB::beginTransaction();
                    try {
                        $service_sender_id_explode = explode(",", $service_sender_id);

                        // CHECK SERVICES EXISTS
                        foreach ($service_sender_id_explode as $service_id_explode_data) {
                            $service_json_data = Services::where(['id' => $service_id_explode_data])->first();

                            $service_json_data_service_id = $service_json_data->id;


                            $swap_service_sender_count_in_sender = SwapServices::where('service_sender_id', $service_id_explode_data)->count();
                            $swap_service_sender_count_in_receiver = SwapServices::where('service_receiver_id', $service_id_explode_data)->count();

                            $swap_service_sender_count_total = $swap_service_sender_count_in_sender + $swap_service_sender_count_in_receiver;


                            $service_data_explode[] = [
                                    'service_id' => $service_json_data->id,
                                    'service_name' => $service_json_data->service_name,
                                    'picture' => $service_json_data->picture,
                                    'swap_service_time_count' => $swap_service_sender_count_total
                            ];
                        }

                        //Convert array to json
                        $service_data_explode_json = json_encode($service_data_explode);



                        $receiver_swap_service_sender_count_in_receiver = SwapServices::where('service_sender_id', $service_receiver_id)->count();
                        $receiver_swap_service_receiver_count_in_sender = SwapServices::where('service_receiver_id', $service_receiver_id)->count();

                        $receiver_swap_service_sender_count_total = $receiver_swap_service_sender_count_in_receiver + $receiver_swap_service_receiver_count_in_sender;



                        // COST OF SERVICE CALCULATE
                        // $sender_amount_to_be_paid = $receiver_service_hourly_rate * $service_hours_want;

                        // STORE SWAP SERVICES
                        $request_fields = array_merge(
                            $request->all(),
                            [
                                    'service_sender_id' => null,
                                    'multiple_service_sender_id' => $service_data_explode_json,
                                    'receiver_swap_service_time_count' => $receiver_swap_service_sender_count_total,
                                    'user_sender_id' => auth()->user()->id,
                                    'user_receiver_id' => $user_receiver_id
                            ]
                        );

                        // CREATE SWAP
                        $swap_services = SwapServices::create($request_fields);

                        // SEND NOTIFICATION
                        $type = 'swap';
                        $info = 'New Swap Received.';
                        addNotification($user_receiver_id,$type,$info);

                        $status_code = 200;
                        $json_response = [
                            'status_code' => $status_code,
                            'status_message' => 'success',
                            'message' => 'Swap Services Created.'
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
        }
        // ELSE END
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function show(Request $request)
    {

        // VALIDATION
        $validator = Validator::make($request->all(), [
            'id' => 'exists:swap_services,id|not_in:0',
            'status' => 'required'
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
            try {

                // GET STATUS PARAMETER FROM API URL
                $id = $request->query('id');

                $type = $request->query('type');

                $status = $request->query('status');

                // HIRE CHECK AVAILABILITY
                // GET SINGLE SERVICE BY ID

                if ($id) {
                    $swap_services_by_user_sender_id_check = SwapServices::where('user_sender_id', auth()->user()->id)
                        ->where('id', $id)
                        ->exists();
                    $swap_services_by_user_receiver_id_check = SwapServices::where('user_receiver_id', auth()->user()->id)
                        ->where('id', $id)
                        ->exists();
                }
                // GET ALL SERVICES OF AUTH USER
                else {
                    $swap_services_by_user_sender_id_check = SwapServices::where('user_sender_id', auth()->user()->id)->exists();
                    $swap_services_by_user_receiver_id_check = SwapServices::where('user_receiver_id', auth()->user()->id)->exists();
                }

                // IF HIRE CHECK AVAILABLE

                if ($swap_services_by_user_sender_id_check || $swap_services_by_user_receiver_id_check) {

                    // OFFER SENT
                    $swap_services = SwapServices::with([
                        'sender_user',
                        'sender_service',
                        'receiver_user',
                        'receiver_service',
                        // 'sender_user.user_rating',
                        // 'receiver_user.user_rating',
                        // 'swap_offer_locations',
                        // 'swap_locations',
                        'boot_assign_person_detail',
                        'declined_by_user'
                    ])
                    ->with(['sender_user' => function ($query) {
                        $query->withCount([
                        'user_rating AS total_service_user_rating' => function ($query) {
                            $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                        }
                    ]);
                    }])
                    ->with(['receiver_user' => function ($query) {
                        $query->withCount([
                        'user_rating AS total_service_user_rating' => function ($query) {
                            $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                        }
                    ]);
                    }])
                    ->orderBy('id', 'desc');

                    // STATUS 'pending', 'active', 'completed', 'cancelled'
                    if ($status) {
                        $swap_services->where(['service_status' => $status]);
                    }

                    // OFFER
                    $swap_services_get = $swap_services;
                    // IF TYPE IS OFFER SENT OR OFFER RECEIVED
                    if ($type) {
                        if ($type == 'offersent') {
                            $swap_services_get = $swap_services_get->where('user_sender_id', auth()->user()->id);
                        } elseif ($type == 'offersreceived') {
                            $swap_services_get = $swap_services_get->where('user_receiver_id', auth()->user()->id);
                        }
                    }

                    // IF REQUEST SINGLE SWAP SERVICE BY ID
                    if ($id) {
                        $swap_services_get = $swap_services_get
                        ->where('id', $id)
                        ->first();
                    } else {
                        $swap_services_get = $swap_services_get
                        ->where(function ($query) {
                            $query->where('user_sender_id', auth()->user()->id)
                                ->orWhere('user_receiver_id', auth()->user()->id);
                        })
                        ->simplePaginate(10);
                    }

                    $swap_data_array_sent = $swap_services_get->toArray();
                    $status_code = 200;
                    $json_response_array = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    ];

                    // WRAP SINGLE HIRE SEVICE IN DATA KEY
                    if ($id) {
                        $swap_data_array_sent = [
                       'data' => $swap_data_array_sent
                        ];
                    }
                    $json_response = array_merge($json_response_array, $swap_data_array_sent);
                }
                // IF HIRE CHECK NOT AVAILABLE
                else {
                    $status_code = 400;
                    $json_response = [
                        'status_code' => $status_code,
                        'status_message' => 'error',
                        'message' => 'Swap Service not linked with Auth User!'
                     ];
                }
            }
            // TRY END

            // IF CATCH ERROR IN STORING DATA
            catch (\Throwable $exception) {
                $status_code = 400;
                $json_response = [
                'status_code' => $status_code,
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


        // HOURS OF SERVICE WANT TO SWAP
        $service_hours_swap = $request->service_hours_swap;



        try {

            // SwapServices
            $receiver_swap_user = SwapServices::findOrFail($id);

            $receiver_swap_receiver_userid = $receiver_swap_user->user_receiver_id;

            $service_hours_want = $receiver_swap_user->service_hours_want;

            // HOURS OF SERVICE WANT TO SWAP
            $service_hours_want = $request->service_hours_want;
            $service_sender_id = $request->service_sender_id;



            // USER SENDER ID
            $user_sender_id = Services::where(['id' => $service_sender_id])->first();
            $sender_user_id = $user_sender_id->user_id;
            // return $sender_user_id;

            // SENDER USER DETAIL
            $user_sender_detail = User::where(['id' => $sender_user_id])->first();
            $sender_hourly_rate = $user_sender_detail->hourly_rate;

            // SENDER USER DETAIL
            $user_receiver_detail = User::where(['id' => $receiver_swap_receiver_userid])->first();
            $receiver_hourly_rate = $user_receiver_detail->hourly_rate;

            // DB TRANSACTION BEGIN
            DB::beginTransaction();
            try {

                 // COST OF SERVICE CALCULATE
                $receiver_amount_to_be_paid = $sender_hourly_rate * $service_hours_swap;
                $sender_amount_to_be_paid = $receiver_hourly_rate * $service_hours_want;

                // UPDATING SWAP SERVICE
                $update_swap_service = SwapServices::findOrFail($id);

                if ($service_sender_id) {
                    $user_sender_id = $update_swap_service->user_sender_id;
                    $user_receiver_id = $update_swap_service->user_receiver_id;

                    // BOOT CALCULATION
                    $boot_calculated = abs($sender_amount_to_be_paid - $receiver_amount_to_be_paid);

                    // BOOT ASSIGN TO SENDER/RECEIVER
                    if ($sender_amount_to_be_paid > $receiver_amount_to_be_paid) {
                        $boot_assign_person = $user_sender_id;
                    } else {
                        $boot_assign_person = $user_receiver_id;
                    }
                }

                // STORE SWAP SERVICES
                $request_fields = array_merge(
                    $request->all(),
                    [
                        'sender_amount_to_be_paid' => $sender_amount_to_be_paid,
                        'receiver_amount_to_be_paid' => $receiver_amount_to_be_paid,
                        'boot_calculate' =>  $boot_calculated,
                        'boot_assign_person' => $boot_assign_person,
                    ]
                );
                $update_swap_service->update($request_fields);

                // SEND NOTIFICATION
                $type = 'swap';
                $info = 'Swap Service Updated.';
                addNotification($user_receiver_id, $type, $info);

                // UPDATE STATUS
                $receiver_service_confirmed =  $update_swap_service->receiver_service_confirmed;
                $sender_service_completed =  $update_swap_service->sender_service_completed;
                $receiver_service_completed = $update_swap_service->receiver_service_completed;

                if(!$service_status){
                    $service_status = $update_swap_service->service_status;
                }
                if ($receiver_service_confirmed == '1') {
                    $service_status = 'active';
                    // SEND NOTIFICATION
                    $type = 'swap';
                    $info = 'Swap Service Status Updated To Active';
                    addNotification($user_receiver_id, $type, $info);

                }
                if($sender_service_completed == 1 && $receiver_service_completed == 1){
                    $service_status = 'completed';
                    // SEND NOTIFICATION
                    $type = 'swap';
                    $info = 'Swap Service Status Updated To Completed';
                    addNotification($user_receiver_id, $type, $info);

                }

                if ($service_status == 'cancelled' || $service_status == 'declined') {
                    $delete_by_user = auth()->user()->id;
                    // SEND NOTIFICATION
                    $type = 'swap';
                    $info = 'Swap Service Status Updated To Cancelled';
                    addNotification($user_receiver_id, $type, $info);

                }
                else{
                    $delete_by_user = null;
                }

                $request_field =
                    [
                        'service_status' => $service_status,
                        'delete_by_user' => $delete_by_user
                    ];
                $update_swap_service->update($request_field);



                $status_code = 200;
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => 'Swap Services Updated.'
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

    public function destroy($id)
    {
        $swap_service = SwapServices::where(['id' => $id]);
        $swap_service_get = $swap_service->first();
        if (!$swap_service_get) {
            $json_response = [
                'status_code' => 200,
                'status_message' => 'error',
                'message' => "Data doesn't exists.",
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }
        $swap_user_sender_id = $swap_service_get->user_sender_id;
        $swap_user_receiver_id = $swap_service_get->user_receiver_id;

        if ($swap_user_sender_id == auth()->user()->id || $swap_user_receiver_id == auth()->user()->id) {
            // SELECT WHERE ID
            $swap_service->delete();
            // SEND NOTIFICATION
            $type = 'swap';
            $info = 'Swap Service Deleted.';
            addNotification($swap_user_receiver_id, $type, $info);

        }
        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => "Successfully Deleted",
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }







}
