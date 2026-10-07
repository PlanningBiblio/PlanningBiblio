<?php

/* TODO : Delete this file when the following variables are no longer used.
 * - msg* : Informational messages passed via the URL, will be replaced by the use of flashBags
 * - user* : Will be replaced by the use of the User object once Symfony authentication is operational
 * - CSRFSession : Will be replaced by Symfony's CSRF check
 */

$templates_params = array(
    'msg'                 => $request->get('msg'),
    'msgType'             => $request->get('msgType'),
    'msg2'                => $request->get('msg2'),
    'msg2Type'            => $request->get('msg2Type'),
    'user_surname'        => $_SESSION['login_nom'],
    'user_firstname'      => $_SESSION['login_prenom'],
    'CSRFSession'         => $CSRFSession,
);
