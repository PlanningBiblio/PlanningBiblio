<?php

namespace App\Planno;

use Jumbojett\OpenIDConnectClient;
use Symfony\Component\HttpFoundation\Request;

class OpenIDConnect
{
    use \App\Traits\LoggerTrait;

    private const LOG_PROGRAM = 'OpenID Connect';

    private $config;
    private $entityManager;
    private $provider;
    private $ca_cert;
    private $client_id;
    private $client_secret;
    private $login_attribute;

    public function __construct()
    {
        $this->config = $GLOBALS['config'];
        $this->entityManager = $GLOBALS['entityManager'];

        $this->provider = $this->config['OIDC-Provider'];
        $this->ca_cert = $this->config['OIDC-CACert'];
        $this->client_id = $this->config['OIDC-ClientID'];
        $this->client_secret = $this->config['OIDC-ClientSecret'];
        $this->login_attribute = !empty($this->config['OIDC-LoginAttribute']) ? $this->config['OIDC-LoginAttribute'] : 'email';
    }

    /**
     * Runs the OpenID Connect authorization code flow.
     *
     * Returns a stdClass (firstname, lastname, email, login) on success,
     * false on any failure. Never lets an exception bubble up to the controller.
     */
    public function auth(Request $request)
    {
        $session = $request->getSession();

        try {
            $oidc = $this->createClient();
            $oidc->addScope(['openid', 'email', 'profile']);

            $oidc->authenticate();

            $session->set('oidcToken', $oidc->getIdToken());

            $claims = $this->collectClaims($oidc);
            if ($claims === null) {
                return false;
            }

            $user = $this->buildUser($claims);

            if ($user->login === null) {
                $this->log($this->missingLoginMessage($claims), self::LOG_PROGRAM);
            }

            if (!empty($this->config['OIDC-Debug'])) {
                // Log claim names only (no personal data), plus the resolved login
                $this->log(json_encode([
                    'claims' => array_keys($claims),
                    'login_attribute' => $this->login_attribute,
                    'login' => $user->login,
                ]), self::LOG_PROGRAM);
            }

        } catch (\Throwable $e) {
            // e.g. error=access_denied, expired client secret, unreachable provider
            $this->log('Authentication failed: ' . $e->getMessage(), self::LOG_PROGRAM);
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
            $oidc = $this->createClient();
            $oidc->signOut($oidcToken, rtrim($this->config['URL'] ?? '', '/') . '/logout');

        } catch (\Throwable $e) {
            $this->log('Logout failed: ' . $e->getMessage(), self::LOG_PROGRAM);
            return false;
        }
    }

    /**
     * Merges ID Token claims and UserInfo claims.
     *
     * - OpenID Connect Core 1.0 §5.4: with the authorization code flow, profile
     *   and email claims are returned by the UserInfo endpoint, but providers
     *   MAY also put them in the ID Token (e.g. Microsoft Entra ID only exposes
     *   preferred_username and oid in the ID Token).
     * - OpenID Connect Core 1.0 §5.3.2: the sub claim returned by UserInfo MUST
     *   match the sub claim of the ID Token, otherwise the response MUST NOT be used.
     *
     * UserInfo values take precedence over ID Token values.
     *
     * @return array|null merged claims, or null if the sub claims do not match
     */
    public static function mergeClaims(array $idTokenClaims, array $userInfoClaims): ?array
    {
        if (array_key_exists('sub', $userInfoClaims)
            && $userInfoClaims['sub'] !== ($idTokenClaims['sub'] ?? null)) {
            return null;
        }

        return array_merge($idTokenClaims, $userInfoClaims);
    }

    /**
     * Builds the user object expected by AuthorizationsController.
     * Non-scalar claim values (arrays, objects) are ignored.
     */
    public function buildUser(array $claims): \stdClass
    {
        $user = new \stdClass();
        $user->firstname = self::scalarClaim($claims, 'given_name');
        $user->lastname = self::scalarClaim($claims, 'family_name');
        $user->email = self::scalarClaim($claims, 'email');
        $user->login = self::scalarClaim($claims, $this->login_attribute);

        return $user;
    }

    /**
     * Tells whether the claims come from the Microsoft Entra ID v1.0 endpoint.
     *
     * The v1.0 endpoint is used when OIDC-Provider does not end with "/v2.0"
     * (e.g. https://login.microsoftonline.com/<tenant>). Its ID Token carries
     * neither email nor preferred_username, and its UserInfo response does not
     * release them either.
     */
    public static function isMicrosoftV1(array $claims): bool
    {
        if (($claims['ver'] ?? null) === '1.0') {
            return true;
        }

        $issuer = $claims['iss'] ?? null;

        return is_string($issuer) && str_starts_with($issuer, 'https://sts.windows.net/');
    }

    /**
     * Builds the message logged when OIDC-LoginAttribute is not in the claims.
     * Contains claim names only, no personal data.
     */
    public function missingLoginMessage(array $claims): string
    {
        $message = "Login attribute '{$this->login_attribute}' not found in claims: "
            . implode(', ', array_keys($claims));

        if (self::isMicrosoftV1($claims)) {
            $message .= '. Microsoft Entra ID v1.0 endpoint detected:'
                . ' set OIDC-Provider to https://login.microsoftonline.com/<tenant-id>/v2.0';
        }

        return $message;
    }

    /**
     * Returns the redirect URI sent to the provider.
     *
     * Built from the Planno URL parameter instead of being guessed from the
     * current request: the library would otherwise force https when browsers
     * send "Upgrade-Insecure-Requests: 1" and would drop ports that differ
     * between the container and the host (Docker, reverse proxies).
     *
     * @return string|null null when the URL parameter is not set
     */
    public function getRedirectUrl(): ?string
    {
        if (empty($this->config['URL'])) {
            return null;
        }

        return rtrim($this->config['URL'], '/') . '/login';
    }

    /**
     * Creates and configures the OpenID Connect client.
     * Protected so that unit tests can inject a mock.
     */
    protected function createClient(): OpenIDConnectClient
    {
        $oidc = new OpenIDConnectClient($this->provider, $this->client_id, $this->client_secret);
        $this->configureClient($oidc);

        return $oidc;
    }

    /**
     * Applies the settings shared by login and logout.
     */
    protected function configureClient(OpenIDConnectClient $oidc): void
    {
        $oidc->setHttpUpgradeInsecureRequests(false);

        $redirectUrl = $this->getRedirectUrl();
        if ($redirectUrl !== null) {
            $oidc->setRedirectURL($redirectUrl);
        }

        if (!empty($this->ca_cert)) {
            $oidc->setCertPath($this->ca_cert);
        }
    }

    /**
     * Reads the verified ID Token claims and the UserInfo claims (single HTTP call).
     * The UserInfo endpoint is optional (OpenID Connect Discovery 1.0 §3): if it is
     * missing or fails, the ID Token claims are used alone.
     *
     * @return array|null null when the UserInfo sub does not match the ID Token sub
     */
    private function collectClaims(OpenIDConnectClient $oidc): ?array
    {
        $idTokenClaims = self::toArray($oidc->getVerifiedClaims());

        $userInfoClaims = [];
        try {
            $userInfoClaims = self::toArray($oidc->requestUserInfo());
        } catch (\Throwable $e) {
            $this->log('UserInfo unavailable, using ID Token claims only: ' . $e->getMessage(), self::LOG_PROGRAM);
        }

        $claims = self::mergeClaims($idTokenClaims, $userInfoClaims);
        if ($claims === null) {
            $this->log('UserInfo sub does not match ID Token sub: authentication refused', self::LOG_PROGRAM);
        }

        return $claims;
    }

    private static function toArray($value): array
    {
        return (is_object($value) || is_array($value)) ? (array) $value : [];
    }

    private static function scalarClaim(array $claims, string $name): ?string
    {
        $value = $claims[$name] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }
}
