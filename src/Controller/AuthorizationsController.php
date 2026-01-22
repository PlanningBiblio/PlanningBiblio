<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\OpenIDConnect;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/ldap/class.ldap.php');

class AuthorizationsController extends BaseController
{

    #[Route(path: '/login', name: 'login', methods: ['GET'])]
    public function login(Request $request, LoggerInterface $logger = null)
    {

        $error = $this->redirectCAS($request, $logger);

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

        if ($this->config('Auth-Mode') == 'OpenIDConnect' and !empty($this->config('OIDC-Provider'))) {
            if (stristr($this->config('OIDC-Provider'), 'google')) {
                $sSOLink = 'Se connecter avec un compte Google';
            } elseif (stristr($this->config('OIDC-Provider'), 'microsoft')) {
                $sSOLink = 'Se connecter avec un compte Microsoft';
            } else {
                $sSOLink = 'Se connecter avec un compte professionnel';
            }
        }

        if (substr($this->config('Auth-Mode'), 0, 3) == 'CAS' and !empty($this->config('CAS-Hostname'))) {
            $sSOLink = 'Se connecter avec un compte CAS';
        }

        $this->templateParams(array(
            'show_menu' => 0,
            'redirect_url' => $redirect_url,
            'new_login' => $new_login,
            'demo_mode' => empty($this->config('demo')) ? 0 : 1,
            'error' => $error,
            'sSOLink' => $sSOLink,
        ));

        return $this->output('login.html.twig');
    }

    #[Route(path: '/login', name: 'login.check', methods: ['POST'])]
    public function check_login(Request $request, LoggerInterface $logger = null)
    {
        $this->redirectCAS($request, $logger);

        $session = $request->getSession();

        $login = $request->get('login');
        $password = $request->get('password');
        $redirect_url = $request->get('redirURL') ?? '/index.php';

        $authArgs = null;
        if (substr($this->config('Auth-Mode'), 0, 3) == 'CAS') {
            if (array_key_exists('oups', $_SESSION)
                and array_key_exists('Auth-Mode', $_SESSION['oups'])
                and $_SESSION['oups']['Auth-Mode'] == 'CAS') {
                $authArgs = '?noCAS';
            }
        }

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

                // CAS auth with SQL fallback.
                case 'CAS-SQL':
                    $auth = false;
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

    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request)
    {
        session_destroy();

        // Symfony Session
        $session = $request->getSession();
        $session->invalidate();

        $authArgs = null;
        if (in_array($this->config('Auth-Mode'), ['CAS', 'CAS-SQL', 'OpenIDConnect'])) {
            $authArgs = $_SESSION['oups']['Auth-Mode'] == 'CAS' ? null: '?noCAS';
        }

        if (substr($this->config('Auth-Mode'), 0, 3) == 'CAS'
            and $_SESSION['oups']['Auth-Mode'] == 'CAS') {

            $cas_url = 'https://'
                . $this->config('CAS-Hostname')
                . ':' . $this->config('CAS-Port') . '/'
                . $this->config('CAS-URI-Logout');
            return $this->redirect($cas_url);
        }

        if ($this->config('Auth-Mode') == 'OpenIDConnect') {
            $oidc = new OpenIDConnect();
            $oidc->logout($request);
        }

        return $this->redirect($this->config('URL') . "/login$authArgs");
    }

    #[Route(path: '/access-denied', name: 'access-denied', methods: ['GET'])]
    public function denied(Request $request)
    {
        $content = $this->renderView('access-denied.html.twig');
        return new Response($content, 403);
    }

    private function redirectCAS(Request $request, $logger)
    {
        $session = $request->getSession();

        if ((substr($this->config('Auth-Mode'), 0, 3) == 'CAS' or $this->config('Auth-Mode') == 'OpenIDConnect')
            and !isset($_GET['noCAS'])
            and empty($session->get('loginId'))
            and !isset($_POST['login'])
            and !isset($_POST['acces'])) {

            $redirURL = $_GET['redirURL'] ?? '';
            // TODO : replace "$_SESSION['oups']['Auth-Mode']" with $session->set('Auth-Mode', 'SSO') 
            $_SESSION['oups']['Auth-Mode']="CAS";

            $login = null;

            // authCAS function redirect user to the CAS server.
            // Once authenticated, it checks if the login exists.
            // If yes, it create the session and log the action.
            if (substr($this->config('Auth-Mode'), 0, 3) == 'CAS') {
                $login = authCAS($logger);

            // OpenID Connect
            } elseif ($this->config('Auth-Mode') == 'OpenIDConnect') {
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
