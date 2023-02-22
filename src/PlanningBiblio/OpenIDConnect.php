<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\Logger;
use Jumbojett\OpenIDConnectClient;

class OpenIDConnect
{

    private $provider;
    private $ca_cert;
    private $client_id;
    private $client_secret;

    public function __construct()
    {
        $this->provider = $_ENV['OIDC_PROVIDER'];
        $this->cacert = $_ENV['OIDC_CACERT'];
        $this->client_id = $_ENV['OIDC_CLIENT_ID'];
        $this->client_secret = $_ENV['OIDC_CLIENT_SECRET'];
    }

    public function auth()
    {
        try {

            $oidc = new OpenIDConnectClient($this->provider,
                $this->client_id,
                $this->client_secret,
            );

            $oidc->setCertPath($this->ca_cert);
            $oidc->authenticate();

            $user = new \stdClass();
            $user->firstname = $oidc->requestUserInfo('given_name');
            $user->lastname = $oidc->requestUserInfo('family_name');
            $user->email = $oidc->requestUserInfo('email');

        } catch (Exception $e) {
            return false;
        }

        return $user;
    }

}
