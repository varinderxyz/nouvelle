<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Locations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;

// use Throwable;


class LocationsController extends Controller
{
    // VIEW ALL LOCATIONS
    public function index(Request $request)
    {
        try {
            // FILTER LOCATION WITH TYPE = USERS/SERVICES
            $type = $request->get('type');

            if ($type) {
                // COLLECT ALL locations DATA
                $all_locations = Locations::where('type', $type)->simplePaginate(10);
            } else {
                // COLLECT ALL locations DATA
                $all_locations = Locations::simplePaginate(10);
            }

            // locations DATA TO ARRAY
            $locations_data_array = $all_locations->toArray();

            // SUCCESS RESPONSE
            $status_code = 200;

            $json_response_array = [
            'status_code' => $status_code,
            'status_message' => 'success',
            'data' => $locations_data_array,
        ];

            $json_response = array_merge($json_response_array, $locations_data_array);
        } catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // STORE LOCATIONS
    public function store(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:locations'
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
                // STORE locations DATA
                $store_locations = new Locations();
                $store_locations->name = $request->input('name');
                $store_locations->type = $request->input('type');
                $store_locations->save();

                // locations DATA TO ARRAY
                $locations_data_array = $store_locations->toArray();

                // SUCCESS RESPONSE
                $status_code = 200;

                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'data' => $locations_data_array,
               ];
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

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // UPDATE LOCATION WITH REQUEST ID
    public function update(Request $request, $id)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:locations',
            'name' => 'required|unique:locations'
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
        // ELSE UPDATE DATA WITH CHECK MODEL
        else {
            try {
                // UPDATE locations DATA
                $update_locations = Locations::findOrFail($id);
                $update_locations->update($request->all());

                // locations DATA TO ARRAY
                $locations_data_array = $update_locations->toArray();

                // SUCCESS RESPONSE
                $status_code = 200;

                $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'data' => $locations_data_array,
              ];
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
        }
        // ELSE END

        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:locations'
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
        // ELSE DATA WITH CHECK MODEL
        else {
            try {
                $id = $request->query('id');
                // SELECT locations LOCATION WHERE ID
                Locations::where(['id' => $id])->delete();
                // SUCCESS RESPONSE
                $status_code = 200;
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'message' => "Successfully Deleted",
                 ];
            } catch (\Throwable $exception) {
                $status_code = 200;
                $json_response = [
                    'status_code' => 400,
                    'status_message' => 'error',
                    'message' => $exception->getMessage()
              ];
            }
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
