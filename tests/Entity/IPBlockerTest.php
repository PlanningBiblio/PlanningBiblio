<?php

namespace App\Tests\Entity;

use App\Entity\IPBlocker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IPBlockerTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entity = new IPBlocker();

        $now = new \DateTime();

        $entity
            ->setIp('192.168.1.1')
            ->setLogin('admin')
            ->setStatus('blocked')
            ->setTimestamp($now);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager->getRepository(IPBlocker::class)->find($id);

        $this->assertSame('192.168.1.1', $entity->getIp());
        $this->assertSame('admin', $entity->getLogin());
        $this->assertSame('blocked', $entity->getStatus());
        $this->assertSame($now, $entity->getTimestamp());
    }
}
