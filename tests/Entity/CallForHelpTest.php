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
        $start = new \DateTime('2026-02-9');
        $end = new \DateTime('2026-02-12');
        $timestamp = new \DateTime();

        $entity
            ->setSite(1)
            ->setPosition(2)
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

        $this->assertEquals(1, $entity->getSite());
        $this->assertEquals(2, $entity->getPosition());
        $this->assertEquals($date->format('Y-m-d'), $entity->getDate()->format('Y-m-d'));
        $this->assertEquals($start->format('Y-m-d'), $entity->getStart()->format('Y-m-d'));
        $this->assertEquals($end->format('Y-m-d'), $entity->getEnd()->format('Y-m-d'));
        $this->assertEquals('test@example.com', $entity->getRecipients());
        $this->assertEquals('Test subject', $entity->getSubject());
        $this->assertEquals('Test message', $entity->getMessage());
        $this->assertEquals($timestamp->format('Y-m-d H:i:s'), $entity->getTimestamp()->format('Y-m-d H:i:s'));
    }
}
