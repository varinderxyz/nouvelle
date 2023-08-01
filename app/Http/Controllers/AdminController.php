<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        
        // GET DATA
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role
        ];
 
        // LOGIN ATTEMPT
        if (auth()->attempt($credentials)) {
            // IF LOGIN SUCCESS
            // GENERATE RANDOM PASSPORT TOKEN FOR API
            $token = auth()->user()->createToken(rand(99999, 1000000))->accessToken;
            // SUCCESS RESPONSE
            $json_response = [
                'status_code' => '200',
                'status_message' => 'success',
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => $request->email,
                'token' => $token
            ];
            return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        // IF WRONG USERNAME OR PASSWORD
        else {

            // FAIL RESPONSE
            $json_response = [
                'status_code' => '401',
                'status_message' => 'fail',
                'error' => 'Wrong usename or password!'
            ];
            return response()->json($json_response, 401, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
