<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Braintree;
use Braintree_Gateway;
use Braintree_TransactionSearch;
// use Braintree_CustomerSearch;
use App\UsersTransactions;
use App\UsersWallet;
use App\HireServices;
use App\SwapServices;
// use Illuminate\Support\Facades\Crypt;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->gateway = new Braintree_Gateway([
            'environment' => 'sandbox',
            'merchantId' => env('BRAINTREE_MERCHANT_ID'),
            'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey' => env('BRAINTREE_PRIVATE_KEY')
        ]);
    }

    // GENERATE CLIENT TOKEN
    public function getClientToken()
    {
        $clientToken = $this->gateway->clientToken()->generate([
            "customerId" => auth()->user()->payment_customer_id
       ]);

        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'braintree_client_token' => $clientToken,
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    //Process Payment
    public function processPayment(Request $request)
    {

        // VALIDATION
        $this->validate($request, [
            'amount' => 'required',
            'paymentMethodNonce' => 'required',
            'sender_user_id' => 'exists:users,id',
            'receiver_user_id' => 'exists:users,id',
            'user_receiver_id' => 'exists:users,id',
            'service_id' => 'exists:services,id',
            // 'encrypt_payment_data' => 'required'
        ]);

        // OpenSSL and the AES-256-CBC cipher

        // $data = $request->all();
        // $encrypt_data = encrypt($data);

        // GET ENCRYPTED STRING DATA
        // $encrypt_payment_data = $request->encrypt_payment_data;

        // // DECRYPT AES DATA
        // $decrypt_data = decrypt($encrypt_payment_data);

        // // DECODE JSON RESPONSE
        // $request_data = json_decode(json_encode($decrypt_data), true);

        $amount = $request->amount;
        $paymentMethodNonce = $request->paymentMethodNonce;

        // ID OF HIRE/SWAP
        $service_id = $request->service_id;
        // TYPE (HIRE/SWAP)
        $type = $request->type;

        // PROCESS PAYMENT
        $transactionresult = $this->gateway->transaction()->sale([
           'amount' => $amount,
           'paymentMethodNonce' => $paymentMethodNonce,
           'options' => [
             'submitForSettlement' => true
           ]
        ]);

        $transactionSuccessResponse = $transactionresult->success;

        if($transactionSuccessResponse == 'true'){
            // SAVE PAYMENT METHOD DETAIL
            $this->gateway->paymentMethod()->create([
                'customerId' => auth()->user()->payment_customer_id,
                'paymentMethodNonce' => $paymentMethodNonce,
            ]);

            $sender_user_id = $request->sender_user_id;
            $receiver_user_id = $request->receiver_user_id;

            // STORE USERS TRANSACTIONS
            $users_transaction = new UsersTransactions();
            $users_transaction->sender_user_id = $sender_user_id;
            $users_transaction->receiver_user_id = $receiver_user_id;
            $users_transaction->amount_paid = $amount;
            $users_transaction->save();

            // UPDATE WALLET BALANCE
            // IF USER WALLET ALREADY EXISTS THEN UPDATE VALUE
            if (UsersWallet::where(['user_id' => $receiver_user_id])->exists()) {
                $Wallet = UsersWallet::where(['user_id' => $receiver_user_id])->first();
                $Wallet->wallet_balance =  $Wallet->wallet_balance + $amount;
                $Wallet->update();
            } else {
                $Wallet = new UsersWallet();
                $Wallet->user_id = $receiver_user_id;
                $Wallet->wallet_balance = $amount;
                $Wallet->save();
            }

            //  UPDATE PAYMENT STATUS TO PAID
            if($type == "swap"){
                $swap_service = SwapServices::where('id', $service_id)->first();
                $swap_service->payment_status = "paid";
                $swap_service->update();
            }
            else{
                $hire_service = HireServices::where('id', $service_id)->first();
                $hire_service->payment_status = "paid";
                $hire_service->update();
            }

            $response_code = 200;
            $json_response = [
                'status_code' => 200,
                'status_message' => 'success',
                'data' => $transactionresult
            ];

        }
        else{
            $response_code = 200;
            $json_response = [
                'status_code' => 406,
                'status_message' => 'error',
                'data' => $transactionresult
            ];
        }

        return response()->json($json_response, $response_code, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function braintreeTransactionDetail()
    {
        // GET BRAINTREE TRANSACTIONS WITH CUSTOMER ID
        $collection = $this->gateway->transaction()->search([
            Braintree_TransactionSearch::customerId()->is(auth()->user()->payment_customer_id),
        ]);

        // FETCH ALL DATA
        foreach ($collection as $transaction) {
            $data[] = $transaction;
        }

        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'data' => $data
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);

    }

    public function usersTransactionDetail()
    {
        // GET USERS TRANSACTIONS WITH AUTH USER ID

        // GET SENT MONEY TO OTHERS WITH OTHER USERS DETAIL
        $sent_user_transactions = UsersTransactions::with('sendMoneyToUser')->where('sender_user_id',auth()->user()->id)->get();
        $sent_user_transactions_data = $sent_user_transactions->toArray();

        // GET RECEIVE MONEY FROM OTHERS WITH OTHER USERS DETAIL
        $receive_user_transactions = UsersTransactions::with('receiveMoneyFromUser')->where('receiver_user_id', auth()->user()->id)->get();
        $receive_user_transactions_data = $receive_user_transactions->toArray();

        $json_response = [
            'status_code' => 200,
            'status_message' => 'success',
            'data' => [
                'sent_money' => $sent_user_transactions_data,
                'receive_money' => $receive_user_transactions_data
            ]
        ];

        return response()->json($json_response, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
