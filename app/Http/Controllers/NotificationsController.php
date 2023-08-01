<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        // VIEW NOTIFICATIONS
        $notifications_new = Notifications::with('sender_user:id,picture')->where(
            [
             'user_receiver_id' => auth()->user()->id,
             'seen' => 0
            ])
        ->get();

        // VIEW NOTIFICATIONS
        $notifications_earlier = Notifications::with('sender_user:id,picture')->where([
             'user_receiver_id' => auth()->user()->id,
             'seen' => 1
            ])
            ->get();

        // UPDATE SEEN TO 1
        Notifications::where('user_receiver_id', auth()->user()->id)->update(['seen' => 1]);

        $status_code = 200;
        $json_response = [
            'status_code' => $status_code,
            'status_message' => 'success',
            'data' => [ 'new' => $notifications_new, 'earlier' => $notifications_earlier],
        ];
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
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

        $store_notification = Notifications::create($request->all() + ['user_sender_id' => auth()->user()->id]);
        $status_code = 200;
        $json_response = [
            'status_code' => $status_code,
            'status_message' => 'success',
            'message' => 'Data Successfully saved.',
            'data' => $store_notification,
        ];
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
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
    public function destroy(Request $request)
    {
        $id = $request->query('id');
        Notifications::where(['user_receiver_id' => auth()->user()->id,'id'=>$id])->delete();
        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => "Successfully Deleted",
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
