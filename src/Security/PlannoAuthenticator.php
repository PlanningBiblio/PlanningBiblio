<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class PlannoAuthenticator extends AbstractAuthenticator
{
    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        // $apiToken = $request->headers->get('X-AUTH-TOKEN');
        // if (null === $apiToken) {
        // The token header was empty, authentication fails with HTTP Status
        // Code 401 "Unauthorized"
        // throw new CustomUserMessageAuthenticationException('No API token provided');
        // }

        // implement your own logic to get the user identifier from `$apiToken`
        // e.g. by looking up a user in the database using its API key
        // $userIdentifier = /** ... */;

        // return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // TODO Check if it is still needed
        $this->redirectCAS($request, $logger);

        $session = $request->getSession();

        $login = $request->request->get('_username');
        $password = $request->request->get('_password');

        var_dump($login); echo "<br>";
        var_dump($password); echo "<br>";
        exit;
        $redirect_url = $request->get('redirURL') ?? '/index.php';

        $authArgs = null;
        if (preg_match('/^CAS|^OpenIDConnect/', $this->config['Auth-Mode'])) {
            $authArgs = '?noCAS';
        }

        $auth = false;

        if ($login != 'admin') {
            // Check authentication method.
            switch ($this->config('Auth-Mode')) {
                case 'LDAP':
                    $auth = authLDAP($login, $password);
                    break;

                // LDAP auth with SQL fallback.
                case 'LDAP-SQL':
                    $auth = authLDAP($login, $password);
                    if (!$auth) {
                        $auth = authSQL($login, $password);
                    }
                    break;

                // SSO auth with SQL fallback.
                case 'CAS-SQL':
                case 'OpenIDConnect-SQL':
                    if ($login and $_POST['auth'] == 'CAS'
                        and array_key_exists('login_id', $_SESSION)
                        and $login == $session->get('loginId')) {
                        $auth = true;
                    }
                    if (!$auth) {
                        $auth = authSQL($login, $password);
                    }
                    break;
            }
        }

        if ($this->config('Auth-Mode') == 'SQL' or $login == 'admin') {
            $auth = authSQL($login, $password);
        }

        if ($authArgs and $redirect_url) {
            $authArgs .= '&amp;redirURL=' . urlencode($redirect_url);
        } elseif ($redirect_url) {
            $authArgs = '?redirURL=' . urlencode($redirect_url);
        }

        // Create a CSRF Token
        $CSRFToken = CSRFToken();
        $_SESSION['oups']['CSRFToken'] = $CSRFToken;

        $error = '';

        if ($auth) {
            // Log login and client IP if success login.
            loginSuccess($login, $CSRFToken);
            $db = new \db();
            $db->select2("personnel", "id,nom,prenom", array("login"=>$login));
            if ($db->result) {
                $_SESSION['login_id'] = $db->result[0]['id'];
                $_SESSION['login_nom'] = $db->result[0]['nom'];
                $_SESSION['login_prenom'] = $db->result[0]['prenom'];

                // Symfony Session
                $session = $request->getSession();
                $session->set('loginId', $db->result[0]['id']);

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('personnel', array('last_login' => date('Y-m-d H:i:s')), array('id' => $session->get('loginId')));
                return $this->redirect($this->config('URL') . "/$redirect_url");
            } else {
                $error = "unknown_user";
            }
        } else {
            loginFailed($login, $CSRFToken);
            $error = 'login_failed';
        }

        $this->templateParams(array(
            'show_menu' => 0,
            'error'     => $error,
            'auth_args' => $authArgs
        ));

        return $this->output('login.html.twig');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }

    private function redirectCAS(Request $request, LoggerInterface $logger): string
    {
        $session = $request->getSession();

        if (preg_match('/^CAS|^OpenIDConnect/', $this->config['Auth-Mode'])
            and !isset($_GET['noCAS'])
            and empty($session->get('loginId'))
            and !isset($_POST['login'])
            and !isset($_POST['acces']))
        {

            $redirURL = $_GET['redirURL'] ?? '';
            // TODO : replace "$_SESSION['oups']['Auth-Mode']" with $session->set('Auth-Mode', 'SSO') 
            $_SESSION['oups']['Auth-Mode'] = 'SSO';

            $login = null;

            // authCAS function redirect user to the CAS server.
            // Once authenticated, it checks if the login exists.
            // If yes, it create the session and log the action.
            if (preg_match('/^CAS/', $this->config['Auth-Mode'])) {
                $login = authCAS($logger);

            // OpenID Connect
            } elseif (preg_match('/^OpenIDConnect/', $this->config['Auth-Mode'])) {
                $oidc = new OpenIDConnect();
                $user = $oidc->auth($request);
                $login = $user ? $user->login : null;
            }

            // Check if user login exists in database.
            $db = new \db();
            $db->select2('personnel', array('id','nom','prenom'), array('login' => 'LIKE' . $login, 'supprime' => '0'));

            // If user's login doesn't exist,
            // show an unauthorized message
            if (!$db->result or empty($login)) {
                // Redirect to error page
                return 'cas_unknown_user';
            }

            // Création de la session
            // If login exists, create session.
            $_SESSION['login_id']=$db->result[0]['id'];
            $_SESSION['login_nom']=$db->result[0]['nom'];
            $_SESSION['login_prenom']=$db->result[0]['prenom'];

            // Symfony Session
            $session = $request->getSession();
            $session->set('loginId', $db->result[0]['id']);

            // Create CSRF Token
            $CSRFToken = CSRFToken();
            $_SESSION['oups']['CSRFToken'] = $CSRFToken;

            // Log cient's login and IP.
            loginSuccess($login, $CSRFToken);

            // Update last_login field.
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('personnel', array('last_login' => date('Y-m-d H:i:s')), array('id' => $session->get('loginId')));

            // Redirect
            header('Location: ' . $this->config('URL') . "/$redirURL");
            exit;
        }

        return '';
    }
}
