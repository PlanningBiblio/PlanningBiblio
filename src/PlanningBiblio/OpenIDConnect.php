<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\Logger;
use Jumbojett\OpenIDConnectClient;
use Symfony\Component\HttpFoundation\Request;

class OpenIDConnect
{

    /**
     * @var mixed
     */
    private $login_attribute;
    private $config;
    private $entityManager;
    private $provider;
    private $ca_cert;
    private $client_id;
    private $client_secret;


    public function __construct()
    {
        $this->config = $GLOBALS['config'];
        $this->entityManager = $GLOBALS['entityManager'];

        $this->provider = $this->config['OIDC-Provider'];
        $this->client_id = $this->config['OIDC-ClientID'];
        $this->client_secret = $this->config['OIDC-ClientSecret'];
        $this->login_attribute = empty($this->config['OIDC-LoginAttribute']) ? 'email' : $this->config['OIDC-LoginAttribute'];
    }


    public function auth(Request $request): false|\stdClass
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

            if ($this->config['OIDC-Debug']) {
                $message = json_encode($oidc->requestUserInfo());
                $logger = new Logger($this->entityManager);
                $logger->log($message, 'OpenID Connect');
            }

        } catch (Exception $e) {
            return false;
        }

        return $user;
    }


    public function logout(Request $request): ?bool
    {
        if (stristr($this->provider, 'google')) {
            return null;
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
        return null;
    }
}
