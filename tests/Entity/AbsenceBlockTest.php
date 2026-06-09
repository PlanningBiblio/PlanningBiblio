<?php

namespace App\Tests\Entity;

use App\Entity\AbsenceBlock;
use PHPUnit\Framework\TestCase;

class AbsenceBlockTest extends TestCase
{
    private AbsenceBlock $block;

    protected function setUp(): void
    {
        $this->block = new AbsenceBlock();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->block->getId());
        $this->assertNull($this->block->getStart());
        $this->assertNull($this->block->getEnd());
        $this->assertNull($this->block->getComment());
    }

    public function testStartAndEndDates(): void
    {
        $start = new \DateTime('2025-01-01');
        $end = new \DateTime('2025-01-10');

        $this->block
            ->setStart($start)
            ->setEnd($end);

        $this->assertSame($start, $this->block->getStart());
        $this->assertSame($end, $this->block->getEnd());
    }

    public function testComment(): void
    {
        $this->block->setComment('Blocking of holiday period');

        $this->assertSame('Blocking of holiday period', $this->block->getComment());
    }

    public function testFluentInterface(): void
    {
        $result = $this->block->setComment('Test');

        $this->assertInstanceOf(AbsenceBlock::class, $result);
    }

    public function testNullableValues(): void
    {
        $this->block
            ->setStart(null)
            ->setEnd(null)
            ->setComment(null);

        $this->assertNull($this->block->getStart());
        $this->assertNull($this->block->getEnd());
        $this->assertNull($this->block->getComment());
    }
}
