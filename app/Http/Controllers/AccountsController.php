<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Accounts;
use Auth;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($user_id)
    {
        $accounts = Accounts::where('user_id',$user_id)->get();
        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'accounts' => $accounts
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
        $account = new Accounts();
        $account->account_type = $request->account_type;
        $account->account_value = $request->account_value;
        $account->user_id = $request->user_id;
        $account->save();

        $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'account' => $account
        ];
        
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $account = Accounts::find($id);
        $account->delete();

        $json_response = [
                'status_code' => '204',
                'status_message' => 'success',
        ];
        
        return response()->json($json_response, 204, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
