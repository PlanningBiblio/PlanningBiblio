<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LoginListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $config = $GLOBALS['config'];
        $route = $event->getRequest()->getPathInfo();
        $route = ltrim($route, '/');
        $session = $event->getRequest()->getSession();

        if (in_array($route, ['login', 'logout', 'legal-notices', 'ical'])) {
            return;
        }

        if (empty($session->get('loginId'))) {
            $redirect = !empty($route) ? "?redirURL=$route" : null;
            $event->setResponse(new RedirectResponse($config['URL'] . '/login' . $redirect));
        }

        return;
    }
}
