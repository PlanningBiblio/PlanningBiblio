<?php

namespace App\EventListener;

use App\Model\ConfigParam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class UrlListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $config = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(array('nom' => 'URL'));

        $request = $event->getRequest();

        $request::setTrustedProxies(
            array($request->server->get('REMOTE_ADDR')),
            Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);

        $url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

        if ($config->valeur() != $url) {
            $config->valeur($url);
            $this->entityManager->flush();
        }
    }
}
