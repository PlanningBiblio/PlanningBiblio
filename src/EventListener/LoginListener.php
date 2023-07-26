<?php

namespace App\EventListener;

use App\Model\ConfigParam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LoginListener
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $url = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(array('nom' => 'URL'));

        $route = $event->getRequest()->getPathInfo();
        $route = ltrim($route, '/');
        $session = $event->getRequest()->getSession();

        if (in_array($route, ['login', 'logout', 'legal-notices', 'ical'])) {
            return;
        }

        if (empty($session->get('loginId'))) {
            $redirect = !empty($route) ? "?redirURL=$route" : null;
            $event->setResponse(new RedirectResponse($url->valeur() . '/login' . $redirect));
        }
    }
}
