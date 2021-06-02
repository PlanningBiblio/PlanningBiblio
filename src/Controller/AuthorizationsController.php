<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/ldap/class.ldap.php');

class AuthorizationsController extends BaseController
{

    /**
     * @Route("/login", name="login", methods={"GET"})
     */
    public function login(Request $request)
    {
        $this->redirectCAS();

        if (loginFailedWait() > 0) {
            return $this->redirectToRoute('access-denied');
        }

        $redirect_url = $request->get('redirURL');
        $new_login = $request->get('newlogin');

        $this->templateParams(array(
            'show_menu' => 0,
            'redirect_url' => $redirect_url,
            'new_login' => $new_login,
            'demo_mode' => empty($this->config('demo')) ? 0 : 1,
        ));

        return $this->output('login.html.twig');
    }

    /**
     * @Route("/login", name="login.check", methods={"POST"})
     */
    public function check_login(Request $request)
    {
        $this->redirectCAS();

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

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update("personnel", array("last_login"=>date("Y-m-d H:i:s")), array("id"=>$_SESSION['login_id']));
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

    /**
     * @Route("/logout", name="logout", methods={"GET"})
     */
    public function logout(Request $request)
    {
        session_destroy();

        $authArgs = null;
        if (substr($this->config('Auth-Mode'), 0, 3) == 'CAS') {
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

        return $this->redirect($this->config('URL') . "/login$authArgs");
    }

    /**
     * @Route("/access-denied", name="access-denied", methods={"GET"})
     */
    public function denied(Request $request)
    {
        return $this->output('access-denied.html.twig');
    }

    private function redirectCAS()
    {
        if (substr($this->config('Auth-Mode'), 0, 3)=="CAS"
            and !isset($_GET['noCAS'])
            and empty($_SESSION['login_id'])
            and !isset($_POST['login'])
            and !isset($_POST['acces'])) {

            $redirURL = $_GET['redirURL'] ?? '';
            $_SESSION['oups']['Auth-Mode']="CAS";

            // authCAS function redirect user to the CAS server.
            // Once authenticated, it checks if the login exists.
            // If yes, it create the session and log the action.
            $login = authCAS();

            // If login succes, redirect to requested page
            if ($login) {

                // Redirection vers le planning
                header('Location: ' . $this->config('URL') . "/$redirURL");
                exit;
            }
        }
    }
}