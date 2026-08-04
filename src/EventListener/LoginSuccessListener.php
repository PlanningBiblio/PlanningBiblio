<?php

namespace App\EventListener;

use App\Service\IPBlockerService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    public function __construct(
        private IPBlockerService $iPBlocker,
    ) {}

    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        // Create the "loginId" session, as it remains necessary for certain checks.
        // Delete it when it is no longer needed.
        $session = $event->getRequest()->getSession();
        $session->set('loginId', $event->getUser()->getId());

        // Logs successful authentication in the IPBlocker table.
        // We use $event->getUser() instead of request->get(username) to ensure unit tests work correctly.
        // The default "unknown" value is added to avoid errors in unit tests.
        $address = $_SERVER['REMOTE_ADDR'] ?? '[unknown]';
        $username = $event->getUser()->getLogin();

        $this->iPBlocker->logSuccess($address, $username);
    }
}
