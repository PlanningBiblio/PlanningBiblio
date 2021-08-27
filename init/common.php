<?php

use Symfony\Component\HttpFoundation\Request;

use App\Model\ConfigParam;

function plannoBaseUrl(Request $request) {
    $entityManager = $GLOBALS['entityManager'];
    $config_url = $entityManager
        ->getRepository(ConfigParam::class)
        ->findOneBy(array('nom' => 'URL'));

    $request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);

    $url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

    $url = str_replace('/index.php', '', $url);

    if ($config_url->valeur() != $url) {
        $config_url->valeur($url);
        $entityManager->persist($config_url);
        $entityManager->flush();
    }

    return $url;
}
