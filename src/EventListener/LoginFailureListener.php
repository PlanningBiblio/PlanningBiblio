<?php

namespace App\EventListener;

use App\Entity\Config;
use App\Entity\IPBlocker;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

final class LoginFailureListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[AsEventListener]
    public function onLoginFailureEvent(LoginFailureEvent $event): void
    {
        // Block the source IP address if it generates too many unsuccessful login attempts.
        // The default "unknown" values ​​are added to avoid errors in unit tests.
        $username = $event->getRequest()->request->get('_username') ?? '[unknown]';
        $address = $_SERVER['REMOTE_ADDR'] ?? '[unknown]';

        $maxAttemps = (int) $this->entityManager->getRepository(Config::class)->findOneByNom('IPBlocker-Attempts')->getValue();
        $timeChecked = (int) $this->entityManager->getRepository(Config::class)->findOneByNom('IPBlocker-TimeChecked')->getValue();
        $timestamp = new DateTime("- $timeChecked minutes");

        $numberOfFailures = $this->entityManager->getRepository(IPBlocker::class)->getNumberOfFailures($address, $timestamp);

        $status = $numberOfFailures >= $maxAttemps ? 'blocked' : 'failure';

        $iPBlocker = new IPBlocker();

        $iPBlocker
            ->setIP($address)
            ->setLogin($username)
            ->setStatus($status);

        $this->entityManager->persist($iPBlocker);
        $this->entityManager->flush();
    }
}
