<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\Logger;
use Jumbojett\OpenIDConnectClient;
use Symfony\Component\HttpFoundation\Request;

class OpenIDConnect
{

    private $provider;
    private $ca_cert;
    private $client_id;
    private $client_secret;
    private $config;


    public function __construct()
    {
        $this->config = $GLOBALS['config'];
        $this->provider = $this->config['OIDC-Provider'];
        $this->cacert = $this->config['OIDC-CACert'];
        $this->client_id = $this->config['OIDC-ClientID'];
        $this->client_secret = $this->config['OIDC-ClientSecret'];
        $this->login_attribute = !empty($this->config['OIDC-LoginAttribute']) ? $this->config['OIDC-LoginAttribute'] : 'email';
    }


    public function auth(Request $request)
    {
        $session = $request->getSession();

        try {

            $oidc = new OpenIDConnectClient($this->provider,
                $this->client_id,
                $this->client_secret,
            );

            $oidc->addScope(['openid', 'email', 'profile']);
            $oidc->setCertPath($this->ca_cert);
            $oidc->authenticate();

            $session->set('oidcToken', $oidc->getIdToken());

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


    public function logout(Request $request)
    {
        if (stristr($this->provider, 'google')) {
            return;
        }

        $session = $request->getSession();
        $oidcToken = $session->get('oidcToken');

        try {
            $oidc = new OpenIDConnectClient($this->provider,
                $this->client_id,
                $this->client_secret,
            );

            $oidc->setCertPath($this->ca_cert);
            $oidc->signOut($oidcToken, $this->config['URL'] . '/logout');

        } catch (Exception $e) {
            return false;
        }
    }
}
