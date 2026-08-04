<?php

namespace App\EventListener;

use App\Entity\IPBlocker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
        $username = $event->getUser()->getLogin();
        $address = $_SERVER['REMOTE_ADDR'] ?? '[unknown]';

        $iPBlocker = new IPBlocker();
        $iPBlocker
            ->setIP($address)
            ->setLogin($username)
            ->setStatus('success');

        $this->entityManager->persist($iPBlocker);
        $this->entityManager->flush();
    }
}
