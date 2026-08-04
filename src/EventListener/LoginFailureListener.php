<?php

namespace App\EventListener;

use App\Service\IPBlockerService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

final class LoginFailureListener
{
    public function __construct(
        private IPBlockerService $iPBlocker,
    ) {}

    #[AsEventListener]
    public function onLoginFailureEvent(LoginFailureEvent $event): void
    {
        // Block the source IP address if it generates too many unsuccessful login attempts.
        // The default "unknown" values ​​are added to avoid errors in unit tests.
        $address = $_SERVER['REMOTE_ADDR'] ?? '[unknown]';
        $username = $event->getRequest()->request->get('_username') ?? '[unknown]';

        $this->iPBlocker->logFailure($address, $username);
    }
}
