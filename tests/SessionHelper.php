<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

trait SessionHelper
{
    public function createSession(KernelBrowser $client, string $agentid): Session
    {
        $container = $client->getContainer();
        $sessionSavePath = $container->getParameter('session.save_path');
        $sessionStorage = new MockFileSessionStorage($sessionSavePath);

        $session = new Session($sessionStorage);
        $session->start();
        $session->set('loginId', $agentid);
        $session->save();
        $client->request('GET', '/');
        $sessionCookie = new Cookie(
            $session->getName(),
            $session->getId(),
        );
        $client->getCookieJar()->set($sessionCookie);
        #$session->save();

        return $session;
    }
}
