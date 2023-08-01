<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SwapServices;
use App\HireServices;
use App\Services;
use App\UsersWallet;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;

class SwapHireServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // GET STATUS PARAMETER FROM API URL
        $id = $request->query('id');

        $offer = $request->query('offer');

        $type = $request->query('type');



        if ($id && $type == 'swap') {
            $swap_services_by_user_sender_id_check = SwapServices::where(
                [
                    'user_sender_id' => auth()->user()->id,
                    'id' => $id
                ]
            )
            ->exists();
        
            $swap_services_by_user_receiver_id_check = SwapServices::where(
                [
                    'user_receiver_id' => auth()->user()->id,
                    'id' => $id
                ]
            )
            ->exists();
        
            if (!($swap_services_by_user_sender_id_check || $swap_services_by_user_receiver_id_check)) {
                $json_response = [
                    'status_code' => 200,
                    'status_message' => 'error',
                    'message' => 'Swap Service are not linked with auth user!',
                ];
                return response()->json($json_response, 200, []);
            }
        }

        if ($id && $type == 'hire') {
            $hire_services_by_user_sender_id_check = HireServices::where(
                [
                    'user_sender_id' => auth()->user()->id,
                    'id' => $id,
                ]
            )
                ->exists();

            $hire_services_by_user_receiver_id_check = HireServices::where(
                [
                    'user_receiver_id' => auth()->user()->id,
                    'id' => $id,
                ]
            )
                ->exists();

            if (!($hire_services_by_user_sender_id_check || $hire_services_by_user_receiver_id_check)) {
                $json_response = [
                    'status_code' => 200,
                    'status_message' => 'error',
                    'message' => 'Hire Service are not linked with auth user!',
                ];
                return response()->json($json_response, 200, []);
            }
        }



        $status = $request->query('status');

        if ($id && $type) {
            $swap_service = SwapServices::where(['id' => $id, 'type' => 'swap'])
            ->first();
            $hire_service = HireServices::where(['id' => $id, 'type' => 'hire'])
            ->first();
            if (!$swap_service || !$hire_service) {
                $json_response = [
                'status_code' => 200,
                'status_message' => 'error',
                'message' => 'Swap or Hire Services are not found!',
            ];
                return response()->json($json_response, 200, []);
            }
        }

        // SWAP SERVICES
        try {

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
        ])
            ->with(['sender_user' => function ($query) {
                $query->withCount([
                    'user_rating AS total_service_user_rating' => function ($query) {
                        $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                    },
                ]);
            }])
            ->with(['receiver_user' => function ($query) {
                $query->withCount([
                    'user_rating AS total_service_user_rating' => function ($query) {
                        $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_service_user_rating"));
                    },
                ]);
            }])
            ->orderBy('id', 'desc');

                // STATUS 'pending', 'active', 'completed', 'cancelled'
                if ($status) {
                    $swap_services->where(['service_status' => $status]);
                }

                // OFFER
                $swap_services_get = $swap_services;
                // IF offer IS OFFER SENT OR OFFER RECEIVED
                if ($offer) {
                    if ($offer == 'offersent') {
                        $swap_services_get = $swap_services_get->where('user_sender_id', auth()->user()->id);
                    } elseif ($offer == 'offersreceived') {
                        $swap_services_get = $swap_services_get->where('user_receiver_id', auth()->user()->id);
                    }
                }

                // IF REQUEST SINGLE SWAP SERVICE BY ID
                if ($id) {
                    if ($type) {
                        $swap_services_get = $swap_services_get
                    ->where('id', $id)
                    ->where('type', $type)
                    ->first();
                    } else {
                        $swap_services_get = $swap_services_get
                    ->where('id', $id);
                    }
                } else {
                    $swap_services_get = $swap_services_get
                ->where(function ($query) {
                    $query->where('user_sender_id', auth()->user()->id)
                        ->orWhere('user_receiver_id', auth()->user()->id);
                })
                ->get();
                }

                $swap_data_array_sent = $swap_services_get->toArray();
                $status_code = 200;
                $swap_json_response_array = [
            'status_code' => $status_code,
            'status_message' => 'success',
        ];

                // WRAP SINGLE HIRE SEVICE IN DATA KEY
                if ($id) {
                    $swap_data_array_sent = [
                'data' => $swap_data_array_sent,
            ];
                }
                $swap_json_response = array_merge($swap_json_response_array, $swap_data_array_sent);
            }
            // IF HIRE CHECK NOT AVAILABLE
            else {
                $status_code = 400;
                $json_response = [
            'status_code' => $status_code,
            'status_message' => 'error',
            'message' => 'Swap Service not linked with Auth User!',
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
        'message' => $exception->getMessage(),
    ];
        }
        // SWAP SERVICES END
        






        // HIRE SERVICES
        try {

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
            ])
            ->withCount([
                'service_ratings AS total_star_rating' => function ($query) {
                    $query->select(DB::raw("( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as total_star_rating"));
                },
                'service_ratings',
            ]);

                // STATUS 'pending', 'active', 'completed', 'cancelled'
                if ($status) {
                    $hire_services->where(['service_status' => $status]);
                }

                // OFFER
                $hire_services_get = $hire_services;
                // IF offer IS OFFER SENT OR OFFER RECEIVED
                if ($offer) {
                    if ($offer == 'offersent') {
                        $hire_services_get = $hire_services_get->where('user_sender_id', auth()->user()->id);
                    } elseif ($offer == 'offersreceived') {
                        $hire_services_get = $hire_services_get->where('user_receiver_id', auth()->user()->id);
                    }
                }

                if ($type) {
                    $hire_services_get = $hire_services_get
                        ->where('type', $type);
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
                ->get();
                }

                $hire_data_array_sent = $hire_services_get->toArray();
                $status_code = 200;
                $hire_json_response_array = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                ];

                // WRAP SINGLE HIRE SEVICE IN DATA KEY
                if ($id) {
                    $hire_data_array_sent = [
                    'data' => $hire_data_array_sent,
                ];
                }
                $hire_json_response = array_merge($json_response_array, $hire_data_array_sent);
            }

            // IF HIRE CHECK NOT AVAILABLE

            else {
                $status_code = 200;
                $json_response = [
                    'status_code' => 400,
                    'status_message' => 'error',
                    'message' => 'Hire Service not linked with Auth User!',
                ];
            }
        }
        // IF CATCH ERROR IN STORING DATA
        catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
 



        if ($id && $type) {
            $swap_service = SwapServices::where(['id' => $id, 'type' => $type])
        ->first();
            $hire_service = HireServices::where(['id' => $id, 'type' => $type])
        ->first();
            if (!$swap_service && !$hire_service) {
                $json_response = [
            'status_code' => 200,
            'status_message' => 'error',
            'message' => 'Swap & Hire Service are not found!',
        ];
                return response()->json($json_response, 200, []);
            }

            if (!$swap_service) {
                $swap_hire_response = $hire_data_array_sent;
            }
            if (!$hire_service) {
                $swap_hire_response = $swap_data_array_sent;
            }

            $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success'
            ];
 
            $json_response = array_merge($json_response, $swap_hire_response);
        } else {

            
            // IF DATA IS EMPTY
            if (empty($swap_data_array_sent) && empty($hire_data_array_sent)) {
                $json_response = [
                        'status_code' => $status_code,
                        'status_message' => 'success',
                        'data' => [],
            ];
                return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            


            // IF SWAP IS EMPTY
            if (empty($swap_data_array_sent) && $hire_data_array_sent) {
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'data' => $hire_data_array_sent,
                ];
                return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            // IF HIRE IS EMPTY
            if ($swap_data_array_sent && empty($hire_data_array_sent)) {
                $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'data' => $swap_data_array_sent,
            ];
            return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
            }

            // HIRE SERVICES END
            if ($swap_data_array_sent && $hire_data_array_sent) {
                $swap_hire_response = array_merge($swap_data_array_sent, $hire_data_array_sent);
                // SORT BY CREATED DATE
                $sort = array();
                foreach ($swap_hire_response as $k => $v) {
                    $sort['created_at'][$k] = $v['created_at'];
                }
                array_multisort($sort['created_at'], SORT_DESC, $swap_hire_response);
            }


        
            $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'data' => $swap_hire_response,
            ];
        }

        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
