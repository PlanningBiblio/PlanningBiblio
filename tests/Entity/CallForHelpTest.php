<?php

namespace App\Tests\Entity;

use App\Entity\CallForHelp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CallForHelpTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new CallForHelp();

        $date = new \DateTime('2026-02-10');
        $start = new \DateTime('2026-02-10 08:00');
        $end = new \DateTime('2026-02-10 12:00');
        $timestamp = new \DateTime();

        $entity
            ->setSite(1)
            ->setPoste(2)
            ->setDate($date)
            ->setStart($start)
            ->setEnd($end)
            ->setRecipients('test@example.com')
            ->setSubject('Test subject')
            ->setMessage('Test message')
            ->setTimestamp($timestamp);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager->getRepository(CallForHelp::class)->find($id);

        $this->assertSame(1, $entity->getSite());
        $this->assertSame(2, $entity->getPoste());
        $this->assertSame($date, $entity->getDate());
        $this->assertSame($start, $entity->getStart());
        $this->assertSame($end, $entity->getEnd());
        $this->assertSame('test@example.com', $entity->getRecipients());
        $this->assertSame('Test subject', $entity->getSubject());
        $this->assertSame('Test message', $entity->getMessage());
        $this->assertSame($timestamp, $entity->getTimestamp());
    }
}
