<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Planno\OpenIDConnect;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Psr\Log\LoggerInterface;

include_once(__DIR__ . '/../../legacy/Common/function.php');
include_once(__DIR__ . '/../../legacy/Class/class.ldap.php');

class AuthorizationsController extends BaseController
{

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, LoggerInterface $logger = null): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $errorPlanno = $this->redirectCAS($request, $logger);

        $IPBlocker = loginFailedWait();

        if ($IPBlocker > 0) {
            $content = $this->renderView('forbidden.html.twig', array(
                'remote_addr' => $_SERVER['REMOTE_ADDR'],
                'ip_blocker' => $IPBlocker
            ));
            return new Response($content, 403);
        }

        $redirect_url = $request->get('redirURL');
        $new_login = $request->get('newlogin');

        // SSO Link
        $sSOLink = null;

        if (preg_match('/^OpenIDConnect/', $this->config['Auth-Mode']) and !empty($this->config('OIDC-Provider'))) {
            if (stristr($this->config('OIDC-Provider'), 'google')) {
                $sSOLink = 'Se connecter avec un compte Google';
            } elseif (stristr($this->config('OIDC-Provider'), 'microsoft')) {
                $sSOLink = 'Se connecter avec un compte Microsoft';
            } else {
                $sSOLink = 'Se connecter avec un compte professionnel';
            }
        }

        if (preg_match('/^CAS/', $this->config['Auth-Mode']) and !empty($this->config('CAS-Hostname'))) {
            $sSOLink = 'Se connecter avec un compte CAS';
        }

        $this->templateParams(array(
            'show_menu' => 0,
            'redirect_url' => $redirect_url,
            'new_login' => $new_login,
            'demo_mode' => empty($this->config('demo')) ? 0 : 1,
            'error' => $error,
            'errorPlanno' => $errorPlanno,
            'sSOLink' => $sSOLink,
            'last_username' => $lastUsername,
        ));

        return $this->output('security/login.html.twig');
    }

    // This route has not been used since Symfony authentication was implemented,
    // but some aspects need to be revisited for LDAP and SSO connections.
    // #[Route(path: '/login', name: 'login.check', methods: ['POST'])]
    public function check_login(Request $request, LoggerInterface $logger = null)
    {
        $this->redirectCAS($request, $logger);

        $session = $request->getSession();

        $login = $request->get('_username');
        $password = $request->get('_password');

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
            // LoginSuccess process is now in LoginSuccessListener
            loginSuccess($login, $CSRFToken);

            // Update lastLogin is now done in LoginSuccessListener
            $db = new \db();
            $db->select2("personnel", "id,nom,prenom", array("login"=>$login));
            if ($db->result) {
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
            // LoginFailure process is now in LoginFailureListener
            loginFailed($login, $CSRFToken);
            $error = 'login_failed';
        }

        $this->templateParams(array(
            'show_menu' => 0,
            'error'     => $error,
            'auth_args' => $authArgs
        ));

        return $this->output('security/login.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        session_destroy();

        // Symfony Session
        $session = $request->getSession();
        $session->invalidate();

        $authArgs = null;
        if (preg_match('/^CAS|^OpenIDConnect/', $this->config['Auth-Mode'])) {
            $authArgs = $_SESSION['oups']['Auth-Mode'] == 'SSO' ? null: '?noCAS';
        }

        if (preg_match('/^CAS/', $this->config['Auth-Mode'])
            and $_SESSION['oups']['Auth-Mode'] == 'SSO') {

            $cas_url = 'https://'
                . $this->config('CAS-Hostname')
                . ':' . $this->config('CAS-Port') . '/'
                . $this->config('CAS-URI-Logout');
            return $this->redirect($cas_url);
        }

        if (preg_match('/^OpenIDConnect/', $this->config['Auth-Mode'])
            and $_SESSION['oups']['Auth-Mode'] == 'SSO') {
            $oidc = new OpenIDConnect();
            $oidc->logout($request);
        }

        return $this->redirect($this->config('URL') . "/login$authArgs");
    }

    #[Route(path: '/access-denied', name: 'access-denied', methods: ['GET'])]
    public function denied(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        // Managed by ControllerAuthorizationListener::triggerAccessDenied
        $content = $this->renderView('access-denied.html.twig');
        return new Response($content, 403);
    }

    private function redirectCAS(Request $request, $logger): string
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
