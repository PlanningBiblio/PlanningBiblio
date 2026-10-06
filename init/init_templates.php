<?php

$templates_params = array(
    'theme'               => $theme,
    'msg'                 => $request->get('msg'),
    'msgType'             => $request->get('msgType'),
    'msg2'                => $request->get('msg2'),
    'msg2Type'            => $request->get('msg2Type'),
    'user_surname'        => $_SESSION['login_nom'],
    'user_firstname'      => $_SESSION['login_prenom'],
    'CSRFSession'         => $CSRFSession,
);
