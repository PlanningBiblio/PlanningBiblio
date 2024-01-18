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
    private $config;


    public function __construct()
    {
        $this->provider = $_ENV['OIDC_PROVIDER'];
        $this->cacert = $_ENV['OIDC_CACERT'];
        $this->client_id = $_ENV['OIDC_CLIENT_ID'];
        $this->client_secret = $_ENV['OIDC_CLIENT_SECRET'];
        $this->login_attribute = !empty($_ENV['OIDC_LOGIN_ATTRIBUTE']) ? $_ENV['OIDC_LOGIN_ATTRIBUTE'] : 'email';
        $this->config = $GLOBALS['config'];
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
            $user->login = $oidc->requestUserInfo($this->login_attribute);

        } catch (Exception $e) {
            return false;
        }

        return $user;
    }


    public function logout()
    {

        $oidc = new OpenIDConnectClient($this->provider,
            $this->client_id,
            $this->client_secret,
        );

        $oidc->setCertPath($this->ca_cert);
        $oidc->signOut($this->client_id, $this->config['URL'] . '/logout');
    }
}
