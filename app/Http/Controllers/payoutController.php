<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;

class payoutController extends Controller
{

  private $_api_context;
    // public function __construct()
    // {
    //     // setup PayPal api context
    //     $paypal_conf = \Config::get('paypal');
    //     $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
    //     $this->_api_context->setConfig($paypal_conf['settings']);
    // }

    public function __construct()
    {
        $this->_api_context = new ApiContext(
            new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret'))
        );
        $this->_api_context->setConfig(config('paypal.settings'));
    }

    public function simplePay()
    {
        $payouts = new \PayPal\Api\Payout();
        $senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
        $senderBatchHeader->setSenderBatchId(uniqid())->setEmailSubject("You have a Payout!");
        $senderItem = new \PayPal\Api\PayoutItem();
        $senderItem->setRecipientType('Email')->setNote('Thanks for your p2atronage!')->setReceiver('sb-dfmip433115@personal.example.com')->setSenderItemId("002")->setAmount(new \PayPal\Api\Currency('{
                                "value":"7467",
                                "currency":"USD"
                            }'));

        $payouts->setSenderBatchHeader($senderBatchHeader)->addItem($senderItem);
        $request = clone $payouts;
        try {
            
            $output = $payouts->createSynchronous($this->_api_context);
            return $output;

        } catch (\Exception $ex) {
            return $ex;
            
            //  \ResultPrinter::printError("Created Single Synchronous Payout", "Payout", null, $request, $ex);
            // exit(1);
        }
        // \ResultPrinter::printResult("Created Single Synchronous Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);
        return $output;
    }
}