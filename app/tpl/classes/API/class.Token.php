<?php
namespace API;

use API;

class Token
{
    private string $token;

    function __construct()
    {
       $url = 'http://api.theoldmountain.com/ping.php';
       $api = new API\API($url);
       $response = $api->response();

       // If the current token is expired, retrieve a new token.
       if(!$response['Ping'])
       {
           $url = 'http://api.theoldmountain.com/vmx.php';

           $api = new API\API($url);

           $response = $api->response();
       }

       $this->token = isset($response['access_token']) ? $response['access_token'] : '';
    }

    public function get_token()
    {
       return $this->token;
    }
}
?>
