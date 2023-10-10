<?php

namespace App\EventListener;

use App\Model\ConfigParam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LoginListener
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $route = $event->getRequest()->getPathInfo();
        $route = ltrim($route, '/');

        $session = $event->getRequest()->getSession();

        $url = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(array('nom' => 'URL'));

        // Prevent user accessing to login page if he is already authenticated
        if (!empty($session->get('loginId')) and $route == 'login') {
            $event->setResponse(new RedirectResponse($url->valeur()));
        }

        // Redirect to the login page if there is no session
        // Except for the following routes
        if (in_array($route, ['login', 'logout', 'legal-notices', 'ical'])) {
            return;
        }

        // Redirect to the login page if there is no session
        if (empty($session->get('loginId'))) {

            $routeParams = array();

            // SSO ticket
            $ticket = $event->getRequest()->get('ticket');
            if ($ticket) {
                $routeParams[] = 'ticket=' . $ticket;
            }

            // Requested route
            if (!empty($route)) {
                $routeParams[] = 'redirURL=' . $route;
            }

            $routeParams = !empty($routeParams) ? '?' . implode('&', $routeParams) : null;

            $event->setResponse(new RedirectResponse($url->valeur() . '/login' . $routeParams));
        }
    }
}
