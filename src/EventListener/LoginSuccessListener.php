<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;


final class LoginSuccessListener
{
    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $session->set('loginId', $event->getUser()->getId());
    }
}
