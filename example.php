<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "Paystack.php";
//NOTE: the returned response will be of object data type, if you want json set:
//self::$responseIsObject = false; in Paystack.php's prepareData method.
//Do not forget to put your app and secret keys in PaystackBaseClass.php's necessary properties.
//To be in development mode: set, self::$url = $self->getURL($isDevelopment); in prepareData method of Paystack.php to true; do not forget to set it to false when going live and also change the keys.

//format the display
echo "<pre>";

/**
 * Get Banks
 */
$banks = Paystack::GetBanks();
print_r($banks);

/**
 * Initiate a transaction
 */
// 1. Using authorization code
$details = [
    "ref" => "PY73828943838GH", //unique string
    "amount" => 500000, //amount in kobo. This is 5,000.00
    "email" => "test_email@email.com",
    'authorization_code' => "auth_293479287389",
    'callback_url' => "http://mywebsite.com/webhookOrCallback.php", //optional
    "narration" => "Monthly subscription for sitting at home. PLATINUM PLAN."
];

$resp = Paystack::ChargeAuthorization($details);
//process the response $resp
if ($resp->Status) {
    $data = $resp->data;
    if ($data->gateway_response == "Successful" || $data->gateway_response == "Approved") {
        //success but you might want to verify 1. the response's signature (security reason), 2. verify the transaction
        //note: if you set callback_url, a notification event will be sent to the url. If not, but you have webhook set in your paystack dashboard, the notification event will be sent to the webhook url. Refer to the doc for webhook events.
        //do whatever you want e.g change user's plan/subscribe user to a plan etc
    } else {
        //handle the failure.
    }
}

// 2. Initiating a fresh transaction
$details = [
    'ref' => "PY73828943838GH", //unique string
    'amount' => 500000, //amount in kobo. This is 5,000.00
    'email' => "test_email@email.com",
    'channel' => 'card', //Refer to paystack doc for available channels.
    'callback_url' => "http://mywebsite.com/webhookOrCallback.php", //optional
];

$resp = Paystack::InitiateTransaction($details);
//print_r($resp);
//process the response $resp
//what you are looking for here is the authorization_url to display to the user
if ($resp->Status) {
    header('Location: ' . $resp->data->authorization_url);
} else {
    echo "Error: " . $resp->Desc;
}
//the user will be asked to enter either bank or card details depending on the channel set in $details above.
