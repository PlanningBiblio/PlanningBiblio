<?php

namespace App\Tests\Service;

use App\Entity\AbsenceReason;
use App\Service\AbsenceReasonProvisioner;
use Tests\PLBWebTestCase;

class AbsenceReasonProvisionerDatabaseTest extends PLBWebTestCase
{
    public function testProvisionCreatesMissingReasonsOnce(): void
    {
        $repository = $this->entityManager->getRepository(AbsenceReason::class);
        $maxRank = array_reduce($repository->findAll(), fn (int $max, AbsenceReason $r) => max($max, $r->getRank()), 0);

        $previousServer = $this->entityManager->getRepository(\App\Entity\Config::class)->getParam('ICS-Server1')?->getValue();
        $previousPattern = $this->entityManager->getRepository(\App\Entity\Config::class)->getParam('ICS-Pattern1')?->getValue();

        $this->config->setParam('ICS-Server1', 'https://example.com/[login].ics');
        $this->config->setParam('ICS-Pattern1', 'Calendrier de test');

        $provisioner = new AbsenceReasonProvisioner($this->entityManager);
        $created = [];

        try {
            $created = $provisioner->provision();

            $this->assertContains('Calendrier de test', $created);

            $reason = $repository->findOneBy(['valeur' => 'Calendrier de test']);
            $this->assertNotNull($reason, 'the reason is created');
            $this->assertGreaterThan($maxRank, $reason->getRank(), 'the reason is added at the end of the list');
            $this->assertSame(0, $reason->getType());
            $this->assertSame('A', $reason->getNotificationWorkflow());
            $this->assertFalse($reason->isTeleworking());

            $this->assertNotContains('Calendrier de test', $provisioner->provision(), 'an existing reason is not created twice');

            $this->config->setParam('ICS-Pattern1', '[SUMMARY]');
            $this->assertSame([], array_intersect(['[SUMMARY]'], $provisioner->provision()), 'keywords are never created');
        } finally {
            foreach ($created as $label) {
                foreach ($repository->findBy(['valeur' => $label]) as $reason) {
                    $this->entityManager->remove($reason);
                }
            }
            $this->entityManager->flush();

            $this->config->setParam('ICS-Server1', (string) $previousServer);
            $this->config->setParam('ICS-Pattern1', (string) $previousPattern);
        }
    }
}
