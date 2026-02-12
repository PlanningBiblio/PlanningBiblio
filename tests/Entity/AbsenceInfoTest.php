<?php

namespace App\Tests\Entity;

use App\Entity\AbsenceInfo;
use PHPUnit\Framework\TestCase;

class AbsenceInfoTest extends TestCase
{
    private AbsenceInfo $info;

    protected function setUp(): void
    {
        $this->info = new AbsenceInfo();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->info->getId());
        $this->assertNull($this->info->getStart());
        $this->assertNull($this->info->getEnd());
        $this->assertSame('', $this->info->getComment());
    }

    public function testStartAndEndDates(): void
    {
        $start = new \DateTime('2025-03-01');
        $end = new \DateTime('2025-03-10');

        $this->info
            ->setStart($start)
            ->setEnd($end);

        $this->assertSame($start, $this->info->getStart());
        $this->assertSame($end, $this->info->getEnd());
    }

    public function testComment(): void
    {
        $this->info->setComment('Importante information');

        $this->assertSame('Importante information', $this->info->getComment());
    }

    public function testNullableValues(): void
    {
        $this->info
            ->setStart(null)
            ->setEnd(null)
            ->setComment(null);

        $this->assertNull($this->info->getStart());
        $this->assertNull($this->info->getEnd());
        $this->assertNull($this->info->getComment());
    }

    public function testFluentInterface(): void
    {
        $result = $this->info->setComment('Test');

        $this->assertInstanceOf(AbsenceInfo::class, $result);
    }
}
