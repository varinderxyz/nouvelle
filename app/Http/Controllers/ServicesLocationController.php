<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServicesLocation;

class ServicesLocationController extends Controller
{
    public function index()
    {
        // COLLECT ALL SERVICES DATA
        $all_services_locations = ServicesLocation::all();

        // SERVICES DATA TO ARRAY
        $services_locations_data_array = $all_services_locations->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'services' => $services_locations_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
        // VALIDATION
        $this->validate($request, [
            'services_location_name' => 'required|unique:services_location'
        ]);

        // STORE SERVICE DATA
        $store_service_locations = new ServicesLocation();
        $store_service_locations->services_location_name = $request->input('services_location_name');
        $store_service_locations->save();

        // SERVICES DATA TO ARRAY
        $services_locations_data_array = $store_service_locations->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'service' => $services_locations_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $id)
    {
        // VALIDATION
        $this->validate($request, [
            'services_location_name' => 'required|unique:services_location'
        ]);

        // UPDATE SERVICE DATA
        $update_service_locations = ServicesLocation::where(['id' => $id])->first();
        $update_service_locations->services_location_name = $request->input('services_location_name');
        $update_service_locations->save();

        // SERVICES DATA TO ARRAY
        $services_locations_data_array = $update_service_locations->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'service' => $services_locations_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function destroy($id)
    {
        // SELECT SERVICE LOCATION WHERE ID
        ServicesLocation::where(['id' => $id])->delete();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => "Successfully Deleted",
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
