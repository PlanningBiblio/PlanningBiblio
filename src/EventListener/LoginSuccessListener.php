<?php

namespace App\EventListener;

use App\Entity\Agent;
use App\Service\IPBlockerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IPBlockerService $iPBlocker,
    ) {}

    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        // Create the "loginId" session, as it remains necessary for certain checks.
        // Delete it when it is no longer needed.
        $session = $event->getRequest()->getSession();
        $session->set('loginId', $event->getUser()->getId());

        $username = $event->getUser()->getLogin();

        // Logs successful authentication in the IPBlocker table.
        // We use $event->getUser() instead of request->get(username) to ensure unit tests work correctly.
        $this->iPBlocker->logSuccess($username);

        $agent = $this->entityManager->getRepository(Agent::class)->findOneByLogin($username);
        $agent->setLastLogin(new \DateTime());
        $this->entityManager->flush();
    }
}
