<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Certifications;
// use App\User;

class CertificationsController extends Controller
{

    public function index()
    {
        // COLLECT ALL CERTIFICATIONS DATA
        $all_certifications = Certifications::where(['user_id' => auth()->user()->id])->get();

        // SERVICES DATA TO ARRAY
        $certifications_data_array = $all_certifications->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'certifications' => $certifications_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
   
    public function store(Request $request)
    {
        // VALIDATION
        $this->validate($request, [
            'certification_name' => 'required',
            'university_name' => 'required',
            'month_year' => 'required',
        ]);

        // STORE SERVICE DATA
        $certification = new Certifications();
        $certification->certification_name = $request->input('certification_name');
        $certification->university_name = $request->input('university_name');
        $certification->month_year = $request->input('month_year');
        $certification->user_id = auth()->user()->id;
        $certification->save();

        // SERVICES DATA TO ARRAY
        $certifications_data_array = $certification->toArray();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'certification' => $certifications_data_array,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function update(Request $request, $id)
    {
        // VALIDATION
        $this->validate($request, [
            'certification_name' => 'required',
            'university_name' => 'required',
            'month_year' => 'required',
        ]);

        // UPDATE SERVICE DATA
        $certification = Certifications::where(['id' => $id])->first();
        $certification->certification_name = $request->input('certification_name');
        $certification->university_name = $request->input('university_name');
        $certification->month_year = $request->input('month_year');
        $certification->user_id = auth()->user()->id;
        $certification->save();

        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => "Update Successfully.",
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function destroy($id)
    {
        // SELECT Certifications WHERE ID
        Certifications::where(['id' => $id])->delete();
        // SUCCESS RESPONSE
        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'message' => "Successfully Deleted",
        ];
        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
