<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/class.ldap.php
Création : 2 juillet 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fonctions permettant les authentifications LDAP et CAS
Fichier inclus par ldap/auth.php
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!isset($version)) {
    include_once __DIR__ . "/../include/accessDenied.php";
}

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

        if (!empty($attributes[$attr]) && is_string($attributes[$attr])) {
            $login = $attributes[$attr];
        }
    }

    // Si authentification CAS et utilisateur existe : retourne son login
    return htmlspecialchars(strval($login));
}

function authLDAP($login, $password)
{
    // Variables
    $auth=false;

    if ($password == '') {
        return $auth;
    }

    if (!$GLOBALS['config']['LDAP-Port']) {
        $GLOBALS['config']['LDAP-Port']="389";
    }

    // Vérifions si l'utilisateur existe dans le planning
    $db=new db();
    $db->select("personnel", "id,nom,prenom", "login='$login' AND `supprime`='0';");
    if (!$db->result) {
        return false;
    }

    //	Connexion au serveur LDAP
    // Recheche du DN de l'utilisateur
    $dn=null;
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
        if ($ldapconn && @ldap_bind($ldapconn, $dn, $password)) {
            $auth=true;
            $_SESSION['oups']['Auth-Mode']="LDAP";
        }
    }
    return $auth;
}

function cmp_ldap($a, array $b): int
{	//tri par nom puis prenom (sn et givenname)
    if ($a['sn'][0] == $b['sn'][0] && isset($a['givenname'])) {
        if ($a['givenname'][0] == $b['givenname'][0]) {
            return 0;
        }
        return ($a['givenname'][0] < $b['givenname'][0]) ? -1 : 1;
    }
    return ($a['sn'][0] < $b['sn'][0]) ? -1 : 1;
}

/**
* La fonction ldap_escape est incluse dans PHP à partir de la version 5.6
* Elle est déclarée ici pour les versions précédentes
*/
if (!function_exists('ldap_escape')) {
    define('LDAP_ESCAPE_FILTER', 0x01);
    define('LDAP_ESCAPE_DN', 0x02);

    /**
     * @param string $subject The subject string
     * @param string $ignore Set of characters to leave untouched
     * @param int $flags Any combination of LDAP_ESCAPE_* flags to indicate the
     *                   set(s) of characters to escape.
     */
    function ldap_escape($subject, $ignore = '', $flags = 0): string
    {
        static $charMaps = array(
            LDAP_ESCAPE_FILTER => array('\\', '*', '(', ')', "\x00"),
            LDAP_ESCAPE_DN     => array('\\', ',', '=', '+', '<', '>', ';', '"', '#'),
        );

        // Pre-process the char maps on first call
        if (!isset($charMaps[0])) {
            $charMaps[0] = array();
            for ($i = 0; $i < 256; $i++) {
                $charMaps[0][chr($i)] = sprintf('\\%02x', $i);
                ;
            }

            for ($i = 0, $l = count($charMaps[LDAP_ESCAPE_FILTER]); $i < $l; $i++) {
                $chr = $charMaps[LDAP_ESCAPE_FILTER][$i];
                unset($charMaps[LDAP_ESCAPE_FILTER][$i]);
                $charMaps[LDAP_ESCAPE_FILTER][$chr] = $charMaps[0][$chr];
            }

            for ($i = 0, $l = count($charMaps[LDAP_ESCAPE_DN]); $i < $l; $i++) {
                $chr = $charMaps[LDAP_ESCAPE_DN][$i];
                unset($charMaps[LDAP_ESCAPE_DN][$i]);
                $charMaps[LDAP_ESCAPE_DN][$chr] = $charMaps[0][$chr];
            }
        }

        // Create the base char map to escape
        $flags = (int)$flags;
        $charMap = array();
        if (($flags & LDAP_ESCAPE_FILTER) !== 0) {
            $charMap += $charMaps[LDAP_ESCAPE_FILTER];
        }
        if (($flags & LDAP_ESCAPE_DN) !== 0) {
            $charMap += $charMaps[LDAP_ESCAPE_DN];
        }
        if ($charMap === []) {
            $charMap = $charMaps[0];
        }

        // Remove any chars to ignore from the list
        $ignore = (string)$ignore;
        for ($i = 0, $l = strlen($ignore); $i < $l; $i++) {
            unset($charMap[$ignore[$i]]);
        }

        // Do the main replacement
        $result = strtr($subject, $charMap);

        // Encode leading/trailing spaces if LDAP_ESCAPE_DN is passed
        if (($flags & LDAP_ESCAPE_DN) !== 0) {
            if ($result[0] === ' ') {
                $result = '\\20' . substr($result, 1);
            }
            if ($result[strlen($result) - 1] === ' ') {
                $result = substr($result, 0, -1) . '\\20';
            }
        }

        return $result;
    }
}
