<?php  
    interface PaymentInterface { 
        public  function getURL($isDevelopment = true); 
        public  function getClient(); 
        public  function requestHeader($headerType); 
        public static function VerifyResponse($resp); 
        public static function ProcessResponse($resp); 
    }  