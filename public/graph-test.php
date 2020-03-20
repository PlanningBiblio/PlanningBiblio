<?php

require __DIR__ . '/../vendor/autoload.php';

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'b1ac0c42-9c34-4539-aa79-8383d7d27531',
    'clientSecret'            => 'K6UFI5c_BgdL:=XJBw*iOJU6k28=Jlyr',
    'redirectUri'             => 'https://graph-planningb.test.biblibre.eu/graph-test.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    'urlResourceOwnerDetails' => '',
    'scopes'                  => 'openid profile offline_access user.read calendars.read calendars.readwrite'
]);

if (!isset($_GET['code'])) {

    $authorizationUrl = $provider->getAuthorizationUrl();

    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: ' . $authorizationUrl);
    exit;

} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    
    exit('Invalid state');

} else {

    try {

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

	$token = $accessToken->getToken();
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

	$headers['Authorization'] = "Bearer $token";
	$response = \Unirest\Request::get('https://graph.microsoft.com/v1.0/me', $headers);
#var_dump($response);

	// Create a calendar
	$headers['Content-Type'] = "application/json";
	#$body = Unirest\Request\Body::json(array('name' => 'BobCalendar'));
	#$response = \Unirest\Request::POST('https://graph.microsoft.com/v1.0/me/calendars', $headers, $body);

	// Get calendars
	$response = \Unirest\Request::get('https://graph.microsoft.com/v1.0/me/calendars', $headers);
	var_dump($response);
	echo "<br /><br />";

	// Add an event.
	$groupid = $response->body->value[0]->id;
	echo "Group: #$groupid";
	echo "<br /><br />";
	$body = Unirest\Request\Body::json(array(
	  "subject" => "Let's go for lunch",
	  "body" => array( 
	    "contentType" => "HTML",
	    "content" => "Does late morning work for you?"
	  ),
	  "start" => array( 
	      "dateTime" => "2019-06-16T12:00:00",
	      "timeZone" => "Pacific Standard Time"
	  ),
	  "end" => array(
	      "dateTime" => "2019-06-16T14:00:00",
	      "timeZone" => "Pacific Standard Time"
	  ),
	  "location" => array(
	      "displayName" => "Harry's Bar"
	  ),
	  "attendees" => array(
	    array(
	      "emailAddress" => array(
		"address" => "alex.arnaud@biblibre.com",
		"name" => "Alex Arnaud"
	      ),
	      "type" => "required"
	    )
	  )
	));
	#$response = \Unirest\Request::POST("https://graph.microsoft.com/v1.0/me/calendar/events", $headers, $body);

	// Obtenir les évènements
	$response = \Unirest\Request::GET("https://graph.microsoft.com/v1.0/me/events", $headers);
	var_dump($response->body);

        $resourceOwner = $provider->getResourceOwner($accessToken);

        var_export($resourceOwner->toArray());

        $request = $provider->getAuthenticatedRequest(
            'GET',
            'http://brentertainment.com/oauth2/lockdin/resource',
            $accessToken
        );

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }
}
