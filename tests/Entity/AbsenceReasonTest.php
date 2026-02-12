<?php

namespace App\Tests\Entity;

use App\Entity\AbsenceReason;
use PHPUnit\Framework\TestCase;

class AbsenceReasonTest extends TestCase
{
    private AbsenceReason $entity;

    protected function setUp(): void
    {
        $this->entity = new AbsenceReason();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->entity->getId());
        $this->assertSame('', $this->entity->getValue());
        $this->assertSame(0, $this->entity->getRank());
        $this->assertSame(0, $this->entity->getType());
        $this->assertSame('A', $this->entity->getNotificationWorkflow());
        $this->assertFalse($this->entity->isTeleworking());
    }

    public function testSetAndGetValue(): void
    {
        $this->entity->setValue('Paid Leave');

        $this->assertSame('Paid Leave', $this->entity->getValue());
    }

    public function testSetAndGetRank(): void
    {
        $this->entity->setRank(10);

        $this->assertSame(10, $this->entity->getRank());
    }

    public function testSetAndGetType(): void
    {
        $this->entity->setType(2);

        $this->assertSame(2, $this->entity->getType());
    }

    public function testSetAndGetNotificationWorkflow(): void
    {
        $this->entity->setNotificationWorkflow('B');

        $this->assertSame('B', $this->entity->getNotificationWorkflow());
    }

    public function testSetAndIsTeleworking(): void
    {
        $this->entity->setTeleworking(true);

        $this->assertTrue($this->entity->isTeleworking());
    }

    public function testFluentInterface(): void
    {
        $result = $this->entity
            ->setValue('Sick Leave')
            ->setRank(5)
            ->setType(1)
            ->setNotificationWorkflow('C')
            ->setTeleworking(true);

        $this->assertInstanceOf(AbsenceReason::class, $result);
        $this->assertSame('Sick Leave', $this->entity->getValue());
        $this->assertSame(5, $this->entity->getRank());
        $this->assertSame(1, $this->entity->getType());
        $this->assertSame('C', $this->entity->getNotificationWorkflow());
        $this->assertTrue($this->entity->isTeleworking());
    }
}
