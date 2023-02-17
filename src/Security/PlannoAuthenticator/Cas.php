<?php

namespace App\Security\PlannoAuthenticator;

use App\PlanningBiblio\Config;

include_once(__DIR__ . '/../../../public/ldap/class.ldap.php');

class Cas
{
    public static function redirect() {
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
