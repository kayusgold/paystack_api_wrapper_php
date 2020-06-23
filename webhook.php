<?php
require_once("Paystack.php");

$filename = "webhook_response.txt";
$headerfile = "header.txt";
$serverfile = "server.txt";

file_put_contents($serverfile, date("Y-m-d G:i:s") . " - " . json_encode($_SERVER) . "\r\n\r\n", FILE_APPEND);
file_put_contents($headerfile, date("Y-m-d G:i:s") . " - " . json_encode(getallheaders()) . "\r\n\r\n", FILE_APPEND);

//receive the webhook response/event
if ($body = file_get_contents("php://input")) {
    $data = json_decode($body);
    //validate webhook response
    $signature = (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '');

    /* It is a good idea to log all events received. Add code *
    * here to log the signature and body to db or file       */
    file_put_contents($filename, $body . "\r\n\r\n", FILE_APPEND);

    if (!$signature) {
        // only a post with paystack signature header gets our attention
        //echo "No signature";
        exit();
    }

    if (!Paystack::ValidateResponseSignature($signature, $body)) {
        $fx = "signatures.txt";
        file_put_contents("signatures.txt", "The webhook signature DOES NOT MATCH: " . $signature . "\r\n\r\n", FILE_APPEND);
        exit();
    } else {
        $fx = "signatures.txt";
        file_put_contents("signatures.txt", "The webhook signature matched: " . $signature . "\r\n\r\n", FILE_APPEND);
    }

    switch ($data->event) {
        case 'charge.success':
            //successfully validated card/bank details or charging card/bank was successful.
            chargeSucessHandler($data);
            break;
        case 'transfer.success':

            break;
        case 'transfer.failed':

            break;
        case 'invoice.create':

            break;
        case 'invoice.failed':

            break;
        default:
            # code...
            break;
    }
}

function chargeSucessHandler($datax)
{
    //do whatever you need to do when charge is succesful here.

    //send back http status 200 to the sender of request. This is important.
    http_response_code(200);
}
