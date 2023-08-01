<?php

use App\Services;
use App\ServicesCategory;
use App\Locations;
use Illuminate\Http\Request;
use App\SwapLocations;
use App\SwapOfferLocations;


function imageUpload($file, $directory_name)
{
    // CREATE FOLDER DIRECTORY
    $folder_name = date('Ymd') . '_' . mt_rand(1000, 990000);
    File::makeDirectory(public_path() .'/'. 'images/'.$directory_name.'/' . $folder_name, 0777, true);
    // DESTINATION PATH
    $destination_path = ('images/'.$directory_name.'/' . $folder_name);
    // GET IMAGE TYPE
    $image_name = randomString() . '.' . $file->getClientOriginalExtension();

    // SAVE IMAGE TO PATH
    $file->getClientOriginalExtension();
    $file->getRealPath();
    $file->getMimeType();
    $file->move($destination_path, $image_name);
    $image_name = $destination_path . '/' . $image_name;

    return $image_name;
}

function randomString()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pin = mt_rand(1000000, 9999999)
        . mt_rand(1000000, 9999999)
        . $characters[rand(0, strlen($characters) - 1)];
    $string = str_shuffle($pin);
    return $string;
}

function checkCategories($services_category_id)
{
    if (!empty($services_category_id)) {
        // SEPERATE IDS FROM COMMA
        $service_id_explode = explode(",", $services_category_id);

        // CHECK SERVICES EXISTS
        foreach ($service_id_explode as $service_id_explode_data) {
            if (!ServicesCategory::where(['id' => $service_id_explode_data])->exists()) {
                return false;
            }
        }
    }
    return true;
}

function checkLocations($services_location_id)
{
    if (!empty($services_location_id)) {
        // SEPERATE IDS FROM COMMA
        $services_location_id_explodes = explode(",", $services_location_id);
        // CHECK LOCATIONS EXISTS
        foreach ($services_location_id_explodes as $services_location_id_explode) {
            if (!Locations::where(['id' => $services_location_id_explode])->exists()) {
                return false;
            }
        }
    }
    return true;
}

function incrementServiceViews($Services, $request)
{
    if (!(Cache::store('file')->get($request->ip()))) {
        // 86400 = 24Hr * 60min * 60seconds
        Cache::store('file')->put($request->ip(), 'value', 86400);
        $Services->increment('views');
        return true;
    }
    return false;
}

function StoreMultipleSwapLocations($swap_location_id, $swap_services_id)
{
    // DB TRANSACTION BEGIN
    DB::beginTransaction();
    try {
        // SEPERATE IDS FROM COMMA
        $swap_location_explodes = explode(",", $swap_location_id);
        // SEPERATE & STORE SERVICE PROVIDE LOCATIONS DATA IN SERVICES LOCATIONS
        foreach ($swap_location_explodes as $swap_location_explode) {
            $swap_location = new SwapLocations();
            $swap_location->location_id = $swap_location_explode;
            $swap_location->swap_services_id = $swap_services_id;
            $swap_location->save();
        }
        DB::commit();
        return true;
    } catch (\Throwable $exception) {
        // ROLLBACK ALL CHANGES
        DB::rollback();
        return false;
    }
    return true;
}


function StoreMultipleSwapOfferLocations($swap_location_offer_id, $swap_services_id)
{
    // DB TRANSACTION BEGIN
    DB::beginTransaction();
    try {
        // SEPERATE IDS FROM COMMA
        $swap_location_offer_explodes = explode(",", $swap_location_offer_id);
        // SEPERATE & STORE SERVICE PROVIDE LOCATIONS DATA IN SERVICES LOCATIONS
        foreach ($swap_location_offer_explodes as $swap_location_offer_explode) {
            $swap_location = new SwapOfferLocations();
            $swap_location->location_id = $swap_location_offer_explode;
            $swap_location->swap_services_id = $swap_services_id;
            $swap_location->save();
        }
        DB::commit();
        return true;
    } catch (\Throwable $exception) {
        // ROLLBACK ALL CHANGES
        DB::rollback();
        return false;
    }
    return true;
}

function addNotification($user_receiver_id,$type,$info){

    App\Notifications::create([
        'user_sender_id' => auth()->user()->id,
        'user_receiver_id' => $user_receiver_id,
        'type' => $type,
        'status' => 'unread',
        'info' => $info,
    ]);
    return true;
}
