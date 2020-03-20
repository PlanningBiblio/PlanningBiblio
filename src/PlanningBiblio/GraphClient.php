<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\OAuth;

class GraphClient
{

    private $baseUrl = 'https://graph.microsoft.com/v1.0';

    private $oauth;

    public function __construct() 
    {
	$this->oauth = new OAuth();
    }

    public function getEvent() {
	$token = $this->oauth->getToken();
	var_dump("token: $token<br /><br />");
	$headers['Authorization'] = "Bearer $token";

	// List all users
	//$response = \Unirest\Request::get($this->baseUrl . '/users', $headers);

	// List a given user
	//$response = \Unirest\Request::get($this->baseUrl . '/users/0bc94fba-dd21-494d-ad7c-aaee5c1f5df6', $headers);

	// Should return a user's default calendar (but returns OrganizationFromTenantGuidNotFound)
	//$response = \Unirest\Request::get($this->baseUrl . '/users/0bc94fba-dd21-494d-ad7c-aaee5c1f5df6/calendar', $headers);

	// Should return a user's calendars (but returns OrganizationFromTenantGuidNotFound)
	$response = \Unirest\Request::get($this->baseUrl . '/users/0bc94fba-dd21-494d-ad7c-aaee5c1f5df6/calendars', $headers);

	return $response;
    }
}
