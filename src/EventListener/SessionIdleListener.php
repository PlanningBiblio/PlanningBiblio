<?php

namespace App\EventListener;

use DateTime;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SessionIdleListener
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/_profiler') || str_starts_with($path, '/_wdt') || str_starts_with($path, '/login')) {
            return;
        }

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $session = $event->getRequest()->getSession();

        $lastActivity = $session->get('lastActivity');

        if ($lastActivity) {
            $diff = $lastActivity->diff(new DateTime());

            if ((int) $diff->format('%S')> $_ENV['SESSION_IDLE']) {
                $url = $this->urlGenerator->generate('logout');
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }
        }

        $lastActivity = new DateTime();
        $session->set('lastActivity', $lastActivity);
    }
}
