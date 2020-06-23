<?php

namespace LoveyCom;

class HttpRequest
{
    /**
     * 
     */
    public function __construct()
    {
    }

    /**
     * Using the generic request method
     *
     * @param string $http_method
     * @param string $url
     * @param string $endpoint
     * @param array $header
     * @param array $query
     * @param boolean $sendJSON
     * @return JSON
     */
    public function request($http_method = 'GET', $url, $endpoint = "", $header = array(), $query = array(), $sendJSON = false)
    {
        if (strtoupper($http_method) == "POST") {
            //header('Content-Type: application/json');
            return $this->post($url, $endpoint, $header, $query, $sendJSON);
        } else if (strtoupper($http_method) == "PUT") {
            //header('Content-Type: application/json');
            return $this->put($url, $endpoint, $header, $query, $sendJSON);
        } else if (strtoupper($http_method) == "GET") {
            //header('Content-Type: application/json');
            return $this->get($url, $endpoint, $header, $query);
        } else if (strtoupper($http_method) == "DELETE") {
            //header('Content-Type: application/json');
            return $this->delete($url, $endpoint, $header, $query);
        } else {
            throw new \Exception("Only GET and POST methods are supported.");
        }
    }

    /**
     * using the GET method
     *
     * @param string $url
     * @param string $endpoint
     * @param array $header
     * @param array $query
     * @return JSON
     */
    public function get($url, $endpoint = "", $header = array(), $query = array())
    {
        $url .= $endpoint . "?";
        if (count($query) > 0) {
            $query = http_build_query($query);
            $url .= $query;
        }

        //$log = "New GET Request.\n";
        //$log .= "URL: $url\n";

        $ch = curl_init();

        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, $url);

        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (array_key_exists('headers', $header)) {
            // Set HTTP Header for POST request 
            //$log .= "Header sent: " . json_encode($header['headers']) . "\n";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header['headers']);
        }

        //Execute the request.
        $result = curl_exec($ch);


        //Handle curl errors
        if (curl_error($ch)) {
            $error_msg = curl_error($ch);
            //$log .= "Error: $error_msg\n";
            throw new \Exception(curl_error($ch));
        }

        //$log .= "\n------------------------END-------------------------\n\n";
        //file_put_contents('log.txt', $log, FILE_APPEND);

        //Close the cURL handle.
        curl_close($ch);

        //Print the data out onto the page.
        //header('Content-Type: application/json');
        return json_encode(json_decode($result));
    }

    /**
     * Sending a POST request
     *
     * @param string $url
     * @param string $endpoint
     * @param array $header
     * @param array $query
     * @param boolean $sendJSON
     * @return JSON
     */
    public function post($url, $endpoint = "", $header = array(), $query = array(), $sendJSON = false)
    {
        if ($sendJSON == true) {
            $payload = json_encode($query);
        } else {
            $payload = $query;
        }

        // Prepare new cURL resource
        $ch = curl_init($url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        if (array_key_exists('headers', $header)) {
            // Set HTTP Header for POST request 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header['headers']);
        }

        // Submit the POST request
        $result = curl_exec($ch);

        //header('Content-Type: application/json');
        return json_encode(json_decode($result));

        // Close cURL session handle
        curl_close($ch);
    }

    /**
     * Sending a PUT request
     *
     * @param string $url
     * @param string $endpoint
     * @param array $header
     * @param array $query
     * @param boolean $sendJSON
     * @return JSON
     */
    public function put($url, $endpoint = "", $header = array(), $query = array(), $sendJSON = false)
    {
        if ($sendJSON == true) {
            $payload = json_encode($query);
        } else {
            $payload = $query;
        }

        // Prepare new cURL resource
        $ch = curl_init($url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        //curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        if (array_key_exists('headers', $header)) {
            // Set HTTP Header for POST request 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header['headers']);
        }

        // Submit the POST request
        $result = curl_exec($ch);

        //header('Content-Type: application/json');
        return json_encode(json_decode($result));

        // Close cURL session handle
        curl_close($ch);
    }

    /**
     * Making a DELETE request
     *
     * @param string $url
     * @param string $endpoint
     * @param array $header
     * @param array $query
     * @return JSON
     */
    public function delete($url, $endpoint = "", $header = array(), $query = array())
    {
        $url .= $endpoint;
        if (count($query) > 0) {
            $query = http_build_query($query);
            $url .= "?" . $query;
        }

        // $log = "New DELETE Request.\n";
        // $log .= "URL: $url\n";

        $ch = curl_init();

        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, $url);


        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (array_key_exists('headers', $header)) {
            // Set HTTP Header for POST request 
            //$log .= "Header sent: " . json_encode($header['headers']) . "\n";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header['headers']);
        }

        //Execute the request.
        $result = curl_exec($ch);


        //Handle curl errors
        if (curl_error($ch)) {
            $error_msg = curl_error($ch);
            //$log .= "Error: $error_msg\n";
            throw new \Exception(curl_error($ch));
        }

        //$log .= "\n------------------------END-------------------------\n\n";
        //file_put_contents('log.txt', $log, FILE_APPEND);

        //Close the cURL handle.
        curl_close($ch);

        //Print the data out onto the page.
        //header('Content-Type: application/json');
        return json_encode(json_decode($result));
    }
}
