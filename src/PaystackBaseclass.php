<?php

namespace LoveyCom;

class PaystackBaseClass implements PaymentInterface
{
    protected $api_key = "";
    protected $secret_key = "";
    protected static $responseIsObject = true;
    protected $bheader; //basic auth ( using base64 encoding)
    protected $oheader; //oauth ( using bearer token )
    protected $token;
    protected $login_time; // timestamp of the login time
    protected $login_expires; //timestamp of the login expires time
    protected $isDev = false;
    protected $query = ['timezone' => 'Africa/Lagos'];

    public function getSecretKey()
    {
        return $this->secret_key;
    }

    public function getURL($isDevelopment = false)
    {
        if ($isDevelopment) {
            $this->isDev = true;
            return "https://api.paystack.co";
        } else {
            $this->isDev = false;
            return "https://api.paystack.co";
        }
    }

    public function getClient()
    {
        $client = new HttpRequest();
        return $client;
    }

    public function requestHeader($headerType = "Basic")
    {
        $this->bheader = [
            'headers' => [
                'Authorization: Bearer ' . $this->secret_key,
                'Content-Type: application/json',
            ]
        ];

        return $this->bheader;
    }

    private function checkIfTokenHasExpired()
    {
        if (strtotime(date("Y-m-d G:i:s")) < $this->login_expires || ($this->login_expires - strtotime(date("Y-m-d G:i:s"))) > 10)
            return false;
        return true;
    }

    private function prepareOAuthHeaders()
    {
        if (!empty($this->token) && $this->checkIfTokenHasExpired() != true) {
            return true;
        }

        $url = $this->getURL($this->isDev);

        $header = [
            'headers' => [
                'Authorization: Basic ' . $this->secret_key,
                'Content-Type: application/json',
            ]
        ];

        $client = $this->getClient();

        $resp = $client->request('POST', $url, '/auth/login', $header, [], false);

        $resp = json_decode($resp);
        if ($resp->requestSuccessful && $resp->responseMessage == "success") {
            $this->token = $resp->responseBody->accessToken;
            $this->login_time = strtotime(date("Y-m-d G:i:s"));
            $this->login_expires = $this->login_time + $resp->responseBody->expiresIn;

            $this->oheader = [
                'headers' => [
                    'Authorization: Bearer ' . $this->token,
                    'Content-Type: application/json',
                ]
            ];
            return true;
        }
        return false;
    }

    /**
     * Verifies if request was successful
     *
     * @param json $resp
     * @return boolean
     */
    public static function VerifyResponse($resp)
    {
        $resp = json_decode($resp);
        if ($resp->status && $resp->message != null)
            return true;
        return false;
    }

    /**
     * Validate Response to be sure it is from paystack
     *
     * @param string $signature : this is from the header $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']
     * @param json string $body : response data from "php://input"
     * @param string $secret_key : user's secret key
     * @return boolean
     */
    public static function ValidateSignature($signature, $body, $secret_key)
    {
        // confirm the event's signature
        if ($signature !== hash_hmac('sha512', $body, $secret_key)) {
            // silently forget this ever happened
            return false;
        }
        return true;
    }

    /**
     * Process the json response
     *
     * @param json $resp
     * @return object
     */
    public static function ProcessResponse($resp)
    {
        //print_r($resp);

        if (self::VerifyResponse($resp)) {
            $resp = json_decode($resp);
            if (self::$responseIsObject)
                return (object) ['Status' => $resp->status, 'data' => $resp->data];
            return json_encode(['Status' => $resp->status, 'data' => $resp->data]);
        } else {
            $resp = json_decode($resp);
            if (self::$responseIsObject)
                return (object) ["Status" => $resp->status, "Desc" => $resp->message];
            return json_encode(["Status" => $resp->status, "Desc" => $resp->message]);
        }
    }
}
