<?php

namespace App\PlanningBiblio;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuth {

    # Prérequis:
    # 
    # L'instance PlanningBiblio doit être en HTTPS.
    #
    # Configurer son compte Microsoft pour l'API Graph:
    #
    # Créer ou utiliser un compte Microsoft.
    # Se rendre sur le portail Microsoft Azur (https://portal.azure.com) avec le même compte.
    # Cliquer sur Azure Active Directory pour créer l'annuaire de l'organisation (compte gratuit de 30 jours ou payant).
    # 
    # Inscrire l'application (toujours sur Azure) 
    # Dans la même page (AD) cliquer sur "inscriptions d'applications".
    #     => nouvelle inscription,
    #     => cocher "compte dans cette annuaire d'organisation uniquement,
    #     => renseigner l'url de redirection (pour le retour),
    #     => cliquer sur "s'inscrire" pour valider.
    #
    # Sur le dashborad de l'application qui apparait, noter:
    #     - L'id d'application (client) => $this->clientid,
    #     - L'id de l'annuaire (locataire) => Tenant: l'identifiant dans l'url (467158a5-8844-4109-a16a-79d35a612a5a/oauth2/token),
    #     - Id de l'objet => on sait pas au moment de l'écriture de cette doc :^),
    #
    # Cliquer sur certificats secrets:
    # Cliquer sur "nouveau secret client"
    # Mettre la valeur dans $this->clientsecret.

    private $clientid;

    private $clientsecret;

    private $tokenURL = "https://login.microsoftonline.com/{tenant}/oauth2/token";

    private $authURL = "https://login.microsoftonline.com/{tenant}/oauth2/authorize";

    private $redirectURL = "https://my.planningbiblio.fr/graphauth";

    function __construct() {
        $this->clientid = '';
        $this->clientsecret = '';
        $this->tokenURL = "";
        $this->authURL = "";
	$this->redirectURL = "https://graph-planningb.test.biblibre.eu/graphauth";
    }

    function getToken() {
        //$provider = new \League\OAuth2\Client\Provider\GenericProvider([
        $provider = new GenericProvider([
            'clientId'                => $this->clientid,
            'clientSecret'            => $this->clientsecret,
            'urlAuthorize'            => $this->authURL,
            'urlAccessToken'          => $this->tokenURL,
	    'redirectUri'             => $this->redirectURL,
            'urlResourceOwnerDetails' => $this->redirectURL
        ]);

        if (!array_key_exists("oauthToken", $_SESSION)) {
            try {

                // Try to get an access token using the client credentials grant.
                $accessToken = $provider->getAccessToken('client_credentials');

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token
                exit("hey" . $e->getMessage());
                var_dump($e->getResponseBody());

            }
        } else {
            $existingAccessToken = unserialize($_SESSION['oauthToken']);
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
        $_SESSION['oauthToken'] = serialize($accessToken);
        return $accessToken;
    }
}

