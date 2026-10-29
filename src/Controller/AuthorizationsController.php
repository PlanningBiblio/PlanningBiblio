<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\IPBlocker;
use App\Controller\BaseController;
use App\Planno\OpenIDConnect;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

include_once(__DIR__ . '/../../legacy/Common/function.php');
include_once(__DIR__ . '/../../legacy/Class/class.ldap.php');

class AuthorizationsController extends BaseController
{
    #[Route(path: '/login', name: 'login', methods: ['GET'])]
    public function login(Request $request, LoggerInterface $logger = null): Response
    {
        $error = $this->redirectCAS($request, $logger);

        if ($return = $this->checkLoginAttempts()) {
            return new Response($return, 403);
        }

        $redirect_url = $request->query->get('redirURL');
        $new_login = $request->query->get('newlogin');

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
            'sSOLink' => $sSOLink,
        ));

        return $this->output('login.html.twig');
    }

    #[Route(path: '/login', name: 'login.check', methods: ['POST'])]
    public function check_login(Request $request, Session $session, LoggerInterface $logger = null): Response|RedirectResponse
    {
        $this->redirectCAS($request, $logger);

        if ($return = $this->checkLoginAttempts()) {
            return new Response($return, 403);
        }

        $login = $request->request->get('login');
        $password = $request->request->get('password');
        $redirect_url = $request->request->get('redirURL') ?? '/index.php';

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
                        $auth = $this->authSQL($login, $password);
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
                        $auth = $this->authSQL($login, $password);
                    }
                    break;
            }
        }

        if ($this->config('Auth-Mode') == 'SQL' or $login == 'admin') {
            $auth = $this->authSQL($login, $password);
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

            $user = $this->entityManager->getRepository(Agent::class)->findOneByLogin($login);
            if ($user) {
                $_SESSION['login_id'] = $user->getId();
                $_SESSION['login_nom'] = $user->getLastname();
                $_SESSION['login_prenom'] = $user->getFirstname();

                // Symfony Session
                $session->set('loginId', $user->getId());

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('personnel', ['last_login' => date('Y-m-d H:i:s')], ['id' => $user->getId()]);
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
    public function logout(Request $request, Session $session): RedirectResponse
    {
        session_destroy();

        // Symfony Session
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
    public function denied(Request $request): Response
    {
        // Managed by ControllerAuthorizationListener::triggerAccessDenied
        $content = $this->renderView('access-denied.html.twig');
        return new Response($content, 403);
    }

    private function authSQL($login, $password): bool
    {
        $entityManager = $GLOBALS['entityManager'];
        $agent = $entityManager->getRepository(Agent::class)->findOneBy(['login' => $login, 'supprime' => 0]);

        if (empty($agent) or empty($login)) {
            return false;
        }

        // Old MD5 password
        if (strlen($agent->getPassword()) == 32) {
            if (md5($password) == $agent->getPassword()) {
                $_SESSION['oups']['Auth-Mode'] = 'SQL';

                // Update password
                $bcrypt_password = password_hash($password,PASSWORD_BCRYPT);

                $agent->setPassword($bcrypt_password);
                $em->persist($agent);
                $em->flush();

                return true;
            }
        // New bcrypt password
        } else {
            if (password_verify($password, $agent->getPassword())) {
                $_SESSION['oups']['Auth-Mode'] = 'SQL';
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the "Forbidden" view if the IP address or account is blocked.
     */
    private function checkLoginAttempts(): string|false
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $minutes = $this->config['IPBlocker-Wait'];
        $waitSince = new DateTimeImmutable("- $minutes minutes");

        if ($this->entityManager->getRepository(IPBlocker::class)->ipIsBlocked($ipAddress, $waitSince)) {
            return $this->renderView('forbidden.html.twig', ['remote_addr' => $ipAddress]);
        }

        return false;
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
            $user = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => $login, 'supprime' => 0]);

            // If user's login doesn't exist,
            // show an unauthorized message
            if (!$user or empty($login)) {
                // Redirect to error page
                return 'cas_unknown_user';
            }

            // Création de la session
            // If login exists, create session.
            $_SESSION['login_id'] = $user->getId();
            $_SESSION['login_nom'] = $user->getLastname();
            $_SESSION['login_prenom'] = $user->getFirstname();

            // Symfony Session
            $session->set('loginId', $user->getId());

            // Create CSRF Token
            $CSRFToken = CSRFToken();
            $_SESSION['oups']['CSRFToken'] = $CSRFToken;

            // Log cient's login and IP.
            loginSuccess($login, $CSRFToken);

            // Update last_login field.
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('personnel', ['last_login' => date('Y-m-d H:i:s')], ['id' => $user->getId()]);

            // Redirect
            header('Location: ' . $this->config('URL') . "/$redirURL");
            exit;
        }

        return '';
    }
}
