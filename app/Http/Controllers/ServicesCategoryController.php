<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServicesCategory;
// use App\ServicesLocations;
use Validator;
// use Throwable;
use Illuminate\Support\Facades\Storage;

class ServicesCategoryController extends Controller
{
    // VIEW ALL SERVICES CATEGORIES
    public function index(Request $request)
    {

        try {
            $id = $request->query('id');
            // COLLECT ALL SERVICES CATEGORY DATA
            $all_services = ServicesCategory::simplePaginate(10);

            // IF REQUEST SERVICE BY ID
            if ($id) {
                $all_services = ServicesCategory::findOrFail($id);
            }

            // SERVICES CATEGORY DATA TO ARRAY
            $services_data_array = $all_services->toArray();

            // SUCCESS RESPONSE
            $status_code = 200;

            if ($id) {
                $json_response = [
                    'status_code' => $status_code,
                    'status_message' => 'success',
                    'data' => $services_data_array
                ];
            } else {
                $json_response_array = [
                'status_code' => $status_code,
                'status_message' => 'success',
            ];
                $json_response = array_merge($json_response_array, $services_data_array);
            }
        } catch (\Throwable $exception) {
            $status_code = 200;
            $json_response = [
                'status_code' => 400,
                'status_message' => 'error',
                'message' => $exception->getMessage()
            ];
        }
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // STORE SERVICES CATEGORIES
    public function store(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:services_category',
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:8000',
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
                // CHECK IF PICTURE AVAILABLE
                if ($request->has('picture')) {
                    // IMAGE
                    // $file = $request->file('picture');
                    // $directory_name = "services_categories";
                    // $image_name = imageUpload($file, $directory_name);

                    // STORE FILE
                    $file = $request->file('picture');
                    $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString() . randomString(), $file, 'public');
                    $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/' . $image_name;
                }

                // STORE SERVICE CATEGORY DATA
                $request_fields = array_merge($request->except('picture'), [
                    'picture' =>  $image_name,
                    'picture2' =>  $image_name
                ]);
                $store_service_category = ServicesCategory::create($request_fields);

                // SERVICES CATEGORY DATA TO ARRAY
                $services_data_array = $store_service_category->toArray();

                // SUCCESS RESPONSE
                $status_code = 200;
                $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'data' => $services_data_array,
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
        // ELSE END
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // UPDATE SERVICE CATEGORIES WITH REQUEST ID
    public function update(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services_category',
            'name' => 'unique:services_category',
            'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:8000',
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
                $id = $request->input('id');

                // UPDATE SERVICE CATEGORY DATA
                $update_service_category = ServicesCategory::findOrFail($id);
                $update_service_category->update($request->all());

                if ($request->has('picture')) {
                    // $file = $request->file('picture');
                    // $directory_name = "services_categories";
                    // $image_name = imageUpload($file, $directory_name);
                    // STORE FILE
                    $file = $request->file('picture');
                    $image_name = Storage::disk('s3')->put('seesaw/images/' . randomString() . randomString(), $file, 'public');
                    $image_name = 'https://getsoundtrax.s3.us-west-1.amazonaws.com/' . $image_name;




                    $update_service_category->picture = $image_name;
                    $update_service_category->update();
                }

                // SERVICES DATA TO ARRAY
                $services_data_array = $update_service_category->toArray();
                $status_code = 200;

                $json_response = [
                'status_code' => $status_code,
                'status_message' => 'success',
                'data' => $services_data_array,
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
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    // DELETE SERVICE CATEGORY
    public function destroy(Request $request)
    {
        // VALIDATION
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services_category'
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

                // SELECT SERVICE WHERE ID
                ServicesCategory::destroy($id);
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
        return response()->json($json_response, $status_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
