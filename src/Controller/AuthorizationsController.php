<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Security\PlannoAuthenticator\Cas;

use Psr\Log\LoggerInterface;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/ldap/class.ldap.php');

class AuthorizationsController extends BaseController
{

    /**
     * @Route("/login", name="login", methods={"GET"})
     */
    public function login(Request $request)
    {

        $cas_error = Cas::redirect();
        $error = $request->get('error');
        $auth_args = $request->get('auth_args');

        if ($auth_args) {
            $this->templateParams(array('auth_args' => $auth_args));
        }

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

        $this->templateParams(array(
            'show_menu' => 0,
            'redirect_url' => $redirect_url,
            'new_login' => $new_login,
            'demo_mode' => empty($this->config('demo')) ? 0 : 1,
            'error' => $cas_error ? $cas_error : $error,
        ));

        return $this->output('login.html.twig');
    }

    /**
     * @Route("/login", name="login.check", methods={"POST"})
     */
    public function login_check(Request $request){}

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
        $content = $this->renderView('access-denied.html.twig');
        return new Response($content, 403);
    }
}