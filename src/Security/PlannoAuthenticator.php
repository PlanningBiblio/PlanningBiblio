<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

use App\PlanningBiblio\Config;
use App\Security\PlannoAuthenticator\Cas;

class PlannoAuthenticator extends AbstractAuthenticator
{
    public const LOGIN_ROUTE = 'login';

    protected $logger;

    protected $redirect_url;

    public function __construct(private UserProviderInterface $userProvider, LoggerInterface $logger, Private UrlGeneratorInterface $urlGenerator){
        $this->logger = $logger;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        $requested_url = ltrim($request->getPathInfo(), '/');
        return $request->isMethod('POST') && self::LOGIN_ROUTE === $requested_url;
    }

    // Called by vendor/symfony/security-http/Authentication/AuthenticatorManager.php
    // in executeAuthenticator method.
    public function authenticate(Request $request): Passport
    {
        $this->redirect_url = $request->get('redirURL');

        if ($this->check_login($request)) {
            $login = $request->get('login');

            $passport = new SelfValidatingPassport(new UserBadge($login, [$this->userProvider, 'loadUserByIdentifier']));
            return $passport;
        }

        return new SelfValidatingPassport(new UserBadge('unknownuser', [$this->userProvider, 'loadUserByIdentifier']));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->redirect_url == self::LOGIN_ROUTE) {
            $this->redirect_url = 'index';
        }
        // on success, let the request continue
        return new RedirectResponse($this->urlGenerator->generate($this->redirect_url));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    private function check_login(Request $request)
    {
        $config = Config::getInstance();
        Cas::redirect();

        $login = $request->get('login');
        $password = $request->get('password');
        $redirect_url = $request->get('redirURL') ?? '/index.php';

        $authArgs = null;
        if (substr($config->get('Auth-Mode'), 0, 3) == 'CAS') {
            if (array_key_exists('oups', $_SESSION)
                and array_key_exists('Auth-Mode', $_SESSION['oups'])
                and $_SESSION['oups']['Auth-Mode'] == 'CAS') {
                $authArgs = '?noCAS';
            }
        }

        if ($login != 'admin') {
            // Check authentication method.
            switch ($config->get('Auth-Mode')) {
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

                // CAS auth with SQL fallback.
                case 'CAS-SQL':
                    if ($login and $_POST['auth'] == 'CAS'
                        and array_key_exists('login_id', $_SESSION)
                        and $login == $_SESSION['login_id']) {
                        $auth = true;
                    }
                    if (!$auth) {
                        $auth = authSQL($login, $password);
                    }
                    break;
            }
        }

        if ($config->get('Auth-Mode') == 'SQL' or $login == 'admin') {
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

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update("personnel", array("last_login"=>date("Y-m-d H:i:s")), array("id"=>$_SESSION['login_id']));
                return true;
            }

            $error = "unknown_user";
            return false;
        }

        loginFailed($login, $CSRFToken);
        $error = 'login_failed';

        return false;

        //$this->templateParams(array(
        //    'show_menu' => 0,
        //    'error'     => $error,
        //    'auth_args' => $authArgs
        //));

        //return $this->output('login.html.twig');
    }

    private function redirectCAS()
    {
        $config = Config::getInstance();
        if (substr($config->get('Auth-Mode'), 0, 3)=="CAS"
            and !isset($_GET['noCAS'])
            and empty($_SESSION['login_id'])
            and !isset($_POST['login'])
            and !isset($_POST['acces'])) {

            $redirURL = $_GET['redirURL'] ?? '';
            $_SESSION['oups']['Auth-Mode']="CAS";

            // authCAS function redirect user to the CAS server.
            // Once authenticated, it checks if the login exists.
            // If yes, it create the session and log the action.
            $login = authCAS($this->logger);

            // Check if user login exists in database.
            $db = new \db();
            $db->select2("personnel", array("id","nom","prenom"), array("login"=>$login, "supprime"=>"0"));

            // If user's login doesn't exist,
            // show an unauthorized message
            if (!$db->result) {
                // Redirect to error page
                return 'cas_unknown_user';
            }

            // Création de la session
            // If login exists, create session.
            $_SESSION['login_id']=$db->result[0]['id'];
            $_SESSION['login_nom']=$db->result[0]['nom'];
            $_SESSION['login_prenom']=$db->result[0]['prenom'];

            // Create CSRF Token
            $CSRFToken = CSRFToken();
            $_SESSION['oups']['CSRFToken'] = $CSRFToken;

            // Log cient's login and IP.
            loginSuccess($login, $CSRFToken);

            // Update last_login field.
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", array("last_login"=>date("Y-m-d H:i:s")), array("id"=>$_SESSION['login_id']));

            // Redirect
            header('Location: ' . $config->get('URL') . "/$redirURL");
            exit;
        }

        return '';
    }
}