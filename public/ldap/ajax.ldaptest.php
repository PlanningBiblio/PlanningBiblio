<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

@file public/ldap/ajax.ldaptest.php
Création : 6 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de tester les paramètres LDAP saisis sur la page administration / configuration
Script appelé par la fonction JS ldaptest() (admin/js/config?js) lors du click sur le bouton "test" de la page administration / configuration / LDAP
*/

require_once(__DIR__ . '/../../init/init_ajax.php');
include_once('class.ldap.php');

$filter = $request->get('filter');
$host = $request->get('host');
$idAttribute = $request->get('idAttribute');
$protocol = $request->get('protocol');
$rdn = $request->get('rdn');
$suffix = $request->get('suffix');
$password = $request->get('password');
$port = $request->get('port');

$port = filter_var($port, FILTER_SANITIZE_NUMBER_INT);

// Connexion au serveur LDAP
$url = $protocol.'://'.$host.':'.$port;

if ($fp=@fsockopen($host, $port, $errno, $errstr, 5)) {
    if ($ldapconn = ldap_connect($url)) {
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        if (ldap_bind($ldapconn, $rdn, $password)) {
            if (ldap_search($ldapconn, $suffix, $filter, array($idAttribute))) {
                echo json_encode('ok');
                exit;
            } else {
                echo json_encode('search');
                exit;
            }
        } else {
            echo json_encode('bind');
            exit;
        }
    }
}

echo json_encode('error');
