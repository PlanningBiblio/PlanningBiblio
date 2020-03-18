<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\OAuth;

class GraphClient
{

    private $baseUrl = 'https://graph.microsoft.com/v1.0';
    //private $baseUrl = 'https://planningbiblio.onmicrosoft.com';
    //private $baseUrl = 'https://graph.windows.net/planningbiblio.onmicrosoft.com';

    private $oauth;

    public function __construct() 
    {
	$this->oauth = new OAuth();
    }

    public function getEvent() {
	$token = $this->oauth->getToken();
	var_dump("token: $token<br /><br />");
	$headers['Authorization'] = "Bearer $token";
	#$response = \Unirest\Request::post($this->baseUrl . '/me/calendarview', $headers);
	#$response = \Unirest\Request::post($this->baseUrl . '/467158a5-8844-4109-a16a-79d35a612a5a/users', $headers);
	//$response = \Unirest\Request::post($this->baseUrl . '/e99dfb02-a369-4443-9c6a-b92e430ba7c5/users', $headers);
	$response = \Unirest\Request::post($this->baseUrl . '/users/c34593a8-278f-4078-a42c-8480cc6868b3', $headers);

	return $response;
    }
}
