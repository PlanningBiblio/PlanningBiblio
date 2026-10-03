<?php

namespace App\Tests\Entity;

use App\Entity\Detached;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DetachedTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $detached = new Detached();

        $date = new \DateTime('2026-02-10');

        $detached
            ->setDate($date)
            ->setUserId(42);

        $entityManager->persist($detached);
        $entityManager->flush();
        $entityManager->clear();

        $id = $detached->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $detached = $entityManager->getRepository(Detached::class)->find($id);

        $this->assertSame($date->format('Y-m-d'), $detached->getDate()->format('Y-m-d'  ));
        $this->assertSame(42, $detached->getUserId());
    }
}