<?php

namespace App\EventListener;

use App\Planno\Helper\ConfigHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class UrlListener
{
    private EntityManagerInterface $entityManager;
    private ConfigHelper $configHelper;

    public function __construct(EntityManagerInterface $em, ConfigHelper $configHelper)
    {
        $this->entityManager = $em;
        $this->configHelper = $configHelper;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $config = $this->configHelper->findOneByName('URL');
        $request = $event->getRequest();

        // See https://github.com/symfony/symfony/pull/38954
        $request::setTrustedProxies(
            array($request->server->get('REMOTE_ADDR')),
            Request::HEADER_X_FORWARDED_TRAEFIK);

        $url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

        if ($config->getValue() != $url) {
            $config->setValue($url);
            $this->entityManager->flush();
        }
    }
}
