<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServicesCatg;
use App\Services;
use Illuminate\Support\Facades\DB;
class ServicesCatgController extends Controller
{
    public function index()
    {
        // COLLECT ALL SERVICES CATEGORY DATA
        $all_services = ServicesCatg::all();

        // SERVICES CATEGORY DATA TO ARRAY
        $services_data_array = $all_services->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'services_catg' => $services_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
        // VALIDATION
        $this->validate($request, [
            'name' => 'required|unique:services_catg'
        ]);

        // STORE SERVICE CATEGORY DATA
        $store_service = new ServicesCatg();
        $store_service->name = $request->input('name');
        $store_service->save();

        // SERVICES CATEGORY DATA TO ARRAY
        $services_data_array = $store_service->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'services_catg' => $services_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $id)
    {
        // VALIDATION
        $this->validate($request, [
            'name' => 'required|unique:services_catg'
        ]);

        // UPDATE SERVICE CATEGORY DATA
        $update_service = ServicesCatg::where(['id' => $id])->first();
        $update_service->name = $request->input('name');
        $update_service->save();

        // SERVICES DATA TO ARRAY
        $services_data_array = $update_service->toArray();

        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'services_catg' => $services_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function categoryServices($category_id)
    {
       
        // COLLECT ALL SERVICES DATA
        $all_services = Services::join('users', 'services.user_token', '=', 'users.user_token')
            ->join('services_location', 'services.services_location_id', '=', 'services_location.id')
            ->join('users_services', 'users_services.services_token', '=', 'services.services_token')
            ->leftJoin('services_rating', 'services_rating.services_token', '=', 'services.services_token')
            ->select(
            'services.id as service_id',
                'service_name',
            'users_services.service_cat_id as service_cat_id',
                'services.picture as services_picture',
                // 'users.user_token',
                'services_location_id',
                'services_location_name',
                'users.name as user_name',
                'services.longitude as services_longitude',
                'services.latitude as services_latitude',
                'hourly_rate',
                'available_for',
                'cancellation_terms_hour',
                'service_descp',
                'service_status',
                'video_url',
                DB::raw(' ( (SUM(star_rating)) / (COUNT(star_rating)*5) * 5 ) as star_rating')
            )
            ->where([
                ['services.service_status','=', 'active'],
                ['users_services.service_cat_id', '=',  $category_id]
            ])
            ->orderBy('services.user_token', 'DESC')
            ->groupBy('services.services_token')
            ->get();

        // SERVICES DATA TO ARRAY
        $services_data_array = $all_services->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'services' => $services_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function destroy($id)
    {
        // SELECT SERVICE WHERE ID
        ServicesCatg::where(['id' => $id])->delete();
        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => '200',
            'status_message' => 'success',
            'message' => "Successfully Deleted",
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
