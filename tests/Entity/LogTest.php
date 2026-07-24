<?php

namespace App\Tests\Entity;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $log = new Log();

        $now = new \DateTime();

        $log
            ->setMessage('Something happened')
            ->setProgram('Something')
            ->setTimestamp($now);

        $entityManager->persist($log);
        $entityManager->flush();
        $entityManager->clear();

        $id = $log->getId();
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $log = $entityManager->getRepository(Log::class)->find($id);

        $this->assertSame('Something happened', $log->getMessage());
        $this->assertSame('Something', $log->getProgram());
        $this->assertSame($now->format('Y-m-d H:i:s'), $log->getTimestamp()->format('Y-m-d H:i:s'));
    }
}
