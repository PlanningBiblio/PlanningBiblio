<?php

namespace App\Service;

use App\Entity\Config;
use App\Entity\IPBlocker;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class IPBlockerService {

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /* 
     * Block the source IP address if it generates too many unsuccessful login attempts.
     */
	public function logFailure(String $username): void
    {
        $address = $_SERVER['REMOTE_ADDR'];
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

    /* 
     * Logs successful authentication in the IPBlocker table.
     */
	public function logSuccess(String $username): void
    {
        // The default "[unknown]" value is added to avoid errors in unit tests.
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
