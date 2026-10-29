<?php
/**
 * Description :
 * Fonctions permettant les authentifications LDAP et CAS
 */

use App\Entity\Agent;

function authCAS($logger): string
{
    $config = $GLOBALS['config'];

    if ($config['CAS-Debug']) {
        phpCAS::setLogger($logger);
        phpCAS::setVerbose(true);
    }

    $base_schema = $config['CAS-Port'] == '80' ? 'http' : 'https';
    $base_port = in_array($config['CAS-Port'], [80, 443]) ? null : ": {$config['CAS-Port']}";
    $base_url = $base_schema . '://' . $config['CAS-Hostname'] . $base_port;

    phpCAS::client($config['CAS-Version'], $config['CAS-Hostname'], intval($config['CAS-Port']), $config['CAS-URI'], $base_url, false);

    phpCAS::setExtraCurlOption(CURLOPT_SSLVERSION, intval($config['CAS-SSLVersion']));
    if ($config['CAS-CACert']) {
        phpCAS::setCasServerCACert($config['CAS-CACert']);
    } else {
        phpCAS::setNoCasServerValidation();
    }

    phpCAS::setFixedServiceURL($config['URL']);

    phpCAS::forceAuthentication();

    $login=phpCAS::getUser();

    if (!empty($config['CAS-LoginAttribute'])) {
        $attr = $config['CAS-LoginAttribute'];
        $attributes = phpCAS::getAttributes();

        if (!empty($attributes[$attr]) and is_string($attributes[$attr])) {
            $login = $attributes[$attr];
        }
    }

    $login = htmlspecialchars(strval($login));

    // Si authentification CAS et utilisateur existe : retourne son login
    return $login;
}

function authLDAP($login, $password)
{
    // Variables
    $entityManager = $GLOBALS['entityManager'];
    $auth = false;

    if ($password == '') {
        return $auth;
    }

    if (!$GLOBALS['config']['LDAP-Port']) {
        $GLOBALS['config']['LDAP-Port']="389";
    }

    // Check if the user exists in our app
    $userExists = $entityManager->getRepository(Agent::class)->findOneBy([
        'login' => $login,
        'supprime' => 0,
    ]);

    if (!$userExists) {
        return false;
    }

    //	Connexion au serveur LDAP
    // Recheche du DN de l'utilisateur
    $dn = null;
    $login = ldap_escape($login, '', LDAP_ESCAPE_FILTER);

    $url = $GLOBALS['config']['LDAP-Protocol'].'://'.$GLOBALS['config']['LDAP-Host'].':'.$GLOBALS['config']['LDAP-Port'];
    $ldapconn = ldap_connect($url)
    or die("Impossible de se connecter au serveur LDAP");
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
    if ($ldapconn) {
        $ldapbind=ldap_bind($ldapconn, $GLOBALS['config']['LDAP-RDN'], decrypt($GLOBALS['config']['LDAP-Password']));
        if ($ldapbind) {
            $sr=ldap_search($ldapconn, $GLOBALS['config']['LDAP-Suffix'], "({$GLOBALS['config']['LDAP-ID-Attribute']}=$login)", array("dn"));
            $infos=ldap_get_entries($ldapconn, $sr);
            if ($infos[0]['dn']) {
                $dn=$infos[0]['dn'];
            }
        }
    }

    // Connexion au serveur LDAP avec le DN de l'utilisateur
    if ($dn) {
        $url = $GLOBALS['config']['LDAP-Protocol'].'://'.$GLOBALS['config']['LDAP-Host'].':'.$GLOBALS['config']['LDAP-Port'];
        $ldapconn = @ldap_connect($url)
      or die("Impossible de se connecter au serveur LDAP");
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        if ($ldapconn) {
            if (@ldap_bind($ldapconn, $dn, $password)) {
                $auth=true;
                $_SESSION['oups']['Auth-Mode']="LDAP";
            }
        }
    }
    return $auth;
}

function cmp_ldap($a, $b): int
{	//tri par nom puis prenom (sn et givenname)
    if ($a['sn'][0] == $b['sn'][0] && isset($a['givenname'])) {
        if ($a['givenname'][0] == $b['givenname'][0]) {
            return 0;
        }
        return ($a['givenname'][0] < $b['givenname'][0]) ? -1 : 1;
    }
    return ($a['sn'][0] < $b['sn'][0]) ? -1 : 1;
}
