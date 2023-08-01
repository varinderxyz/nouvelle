<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Withdrawal;
use App\Accounts;
use Auth;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_withrawal_request = Withdrawal::all();

        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'all_withrawal_request' => $all_withrawal_request
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $user->last_used_account = $request->payment_method_id;
        $user->save();
        $account = Accounts::find($request->payment_method_id);
        $withdrawal_request = new Withdrawal();
        $withdrawal_request->name = auth()->user()->name;
        $withdrawal_request->user_id = auth()->user()->id;
        $withdrawal_request->email = auth()->user()->email;
        $withdrawal_request->amount = $request->amount;
        $withdrawal_request->payment_method_id = $request->payment_method_id;
        $withdrawal_request->payment_method_type = $account->account_type;
        $withdrawal_request->payment_method_value = $account->account_value;
        $withdrawal_request->save();

        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'withdrawal_request' => $withdrawal_request
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $withdrawal_request = Withdrawal::where('user_id',$id)->get();
        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'withdrawal_request' => $withdrawal_request
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {  
        $withdrawal_request = Withdrawal::find($id);
        $withdrawal_request->status = $request->status;
        $withdrawal_request->save();

        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'withdrawal_request' => $withdrawal_request
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getrate()
    {  
        $withdrawal_rate = 10;
        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'withdrawal_rate' => $withdrawal_rate
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
