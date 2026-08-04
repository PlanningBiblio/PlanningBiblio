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
        $session = $event->getRequest()->getSession();
        $session->set('loginId', $event->getUser()->getId());

        $iPBlocker = new IPBlocker();
        $iPBlocker
            ->setIP($_SERVER['REMOTE_ADDR'])
            ->setLogin($event->getUser()->getlogin())
            ->setStatus('success');

        $this->entityManager->persist($iPBlocker);
        $this->entityManager->flush();
    }
}
