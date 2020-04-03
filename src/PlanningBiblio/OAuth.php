<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\Logger;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuth {

    private $clientid;
    private $clientsecret;
    private $tokenURL;
    private $authURL;
    private $options;
    private $token;
    private $logger;

    function __construct($logger, $clientid, $clientsecret, $tokenURL, $authURL, $options = array()) {

        $this->clientid = $clientid;
        $this->clientsecret = $clientsecret;
        $this->tokenURL = $tokenURL;
        $this->authURL = $authURL;
        $this->options = $options;
        $this->logger = $logger;

    }

    function getToken() {

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $this->clientid,
            'clientSecret'            => $this->clientsecret,
            'urlAuthorize'            => $this->authURL,
            'urlAccessToken'          => $this->tokenURL,
            'urlResourceOwnerDetails' => ''
        ]);

        if (!$this->token) {
            try {

                // Try to get an access token using the client credentials grant.
                $accessToken = $provider->getAccessToken('client_credentials', $this->options);

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token
                $this->logger->log("Unable to get OAuth token", get_class($this));
                $this->logger->log("Message: " . $e->getMessage() . " code: " . $e->getCode(), get_class($this));
                exit();

            }
        } else {
            $existingAccessToken = $this->token;
            if ($existingAccessToken->hasExpired()) {
                $refresh_token = $existingAccessToken->getRefreshToken();
                if ($refresh_token != null) {
                    $newAccessToken = $provider->getAccessToken('refresh_token', [
                        'refresh_token' => $refresh_token
                    ]);
                } else {
                    $newAccessToken = $provider->getAccessToken('client_credentials');
                }
                $accessToken = $newAccessToken;
            } else {
                $accessToken = $existingAccessToken;
            }
        }
        $this->token = $accessToken;
        return $accessToken;
    }
}

