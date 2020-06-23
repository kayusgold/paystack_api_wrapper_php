<?php
include_once("PaystackBaseclass.php");

class Paystack extends PaystackBaseClass
{

    protected static $header;
    protected static $client;
    protected static $url;
    protected static $secretKey;

    /**
     * PrepareData is used to get all default values from the base/parent class
     *
     * @param boolean $isDevelopment
     * @return void
     */
    private static function PrepareData($isDevelopment = false)
    {
        $self = new static;
        self::$header = $self->requestHeader(); //from parent
        self::$client = $self->getClient();
        self::$url = $self->getURL($isDevelopment);
        self::$secretKey = $self->getSecretKey();
        self::$responseIsObject = true;
    }

    public static function GetBanks()
    {
        self::PrepareData();

        $resp = self::$client->request('GET', self::$url, '/bank', self::$header);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Resolve account number to get correct account name
     *
     * @param string $bank_code
     * @param string $account_number
     * @return object
     */
    public static function ResolveAccountNumber($bank_code, $account_number)
    {
        self::PrepareData();

        $body = ['account_number' => $account_number, 'bank_code' => $bank_code];

        $resp = self::$client->request('GET', self::$url, '/bank/resolve', self::$header, $body, false);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Resolve BVN
     *
     * @param string $bvn
     * @return object
     */
    public static function ResolveBVN($bvn)
    {
        self::PrepareData();

        $resp = self::$client->request('GET', self::$url, '/bank/resolve_bvn/' . $bvn, self::$header);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Validate and match details with BVN details
     *
     * @param string $bank_code
     * @param string $account_number
     * @param string $bvn
     * @param string $first_name
     * @param string $middle_name
     * @param string $last_name
     * @return object
     */
    public static function ValidateBVN($bank_code, $account_number, $bvn, $first_name = "", $middle_name = "", $last_name = "")
    {
        self::PrepareData();

        $data = [
            "account_number" => $account_number,
            "bank_code" => $bank_code,
            "bvn" => $bvn,
        ];
        if ($first_name != "")
            $data['first_name'] = $first_name;
        if ($middle_name != "")
            $data['middle_name'] = $middle_name;
        if ($last_name != "")
            $data['last_name'] = $last_name;

        $resp = self::$client->request('POST', self::$url, '/bvn/match', self::$header, $data, false);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Initiate a transaction
     *
     * @param Array $details - $details must have the following properties: ref, amount, email, channel and callback_url (optional)
     * @return object
     */
    public static function InitiateTransaction($details)
    {
        self::PrepareData();

        $data = [
            'reference' => $details['ref'],
            'callback_url' => (isset($details['callback_url'])) ? $details['callback_url'] : "", //'http://paycollect.cellcore.com.ng/paystack_test?ref='.$details['ref'],
            'amount' => $details['amount'], //amount in kobo
            'email' => $details['email'], //customer's email
            'channels' => [$details['channel']], //card or bank or both
        ];

        $resp = self::$client->request('POST', self::$url, '/transaction/initialize', self::$header, $data, true);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Verify Transactions
     *
     * @param string $reference : the reference id of the transaction to be verified
     * @return object
     */
    public static function VerifyTransaction($reference)
    {
        self::PrepareData();

        $resp = self::$client->request('GET', self::$url, '/transaction/verify/' . $reference, self::$header);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Charge Authorization: This allow charging a user who has once entered card/bank details with paystack and authorization code is known
     *
     * @param array $details : required properties: ref, amount, email, authorization_code, optional: callback_url, narration
     * @return object
     */
    public static function ChargeAuthorization($details)
    {
        self::PrepareData();

        $data = [
            'reference' => $details['ref'],
            'callback_url' => (isset($details['callback_url'])) ? $details['callback_url'] : "", //'http://paycollect.cellcore.com.ng/paystack_test?ref='.$details['ref'],
            'amount' => $details['amount'], //amount in kobo
            'email' => $details['email'], //customer's email
            'authorization_code' => $details['authorization_code'],
            'metadata' => ["narration" => $details['narration']]
        ];

        $resp = self::$client->request('POST', self::$url, '/transaction/charge_authorization', self::$header, $data, true);

        //print_r(PaystackBaseClass::ProcessResponse($resp));

        return PaystackBaseClass::ProcessResponse($resp);
    }

    /**
     * Validate Response Signature: a security check to be sure that the response is from paystack.
     *
     * @param string $signature : this is from the header $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']
     * @param json $body : response data from "php://input"
     * @return boolean
     */
    public static function ValidateResponseSignature($signature, $body)
    {
        $self = new static;
        self::$secretKey = $self->getSecretKey();
        return PaystackBaseClass::ValidateSignature($signature, $body, self::$secretKey);
    }
}
