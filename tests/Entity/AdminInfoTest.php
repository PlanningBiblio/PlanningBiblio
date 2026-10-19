<?php

namespace App\Tests\Entity;

use App\Entity\AdminInfo;
use PHPUnit\Framework\TestCase;

class AdminInfoTest extends TestCase
{
    public function testNewMessageIsForAllSites(): void
    {
        $info = new AdminInfo();

        $this->assertSame([], $info->getSites());
        $this->assertTrue($info->isForAllSites());
        $this->assertTrue($info->isDisplayedOnSite(1));
        $this->assertTrue($info->isDisplayedOnSite(42));
    }

    public function testSetSitesNormalizesValues(): void
    {
        $info = new AdminInfo();
        $info->setSites(['3', 1, '1', 2]);

        $this->assertSame([1, 2, 3], $info->getSites(), 'sites are cast to int, deduplicated and sorted');
        $this->assertFalse($info->isForAllSites());
    }

    public function testIsDisplayedOnSite(): void
    {
        $info = new AdminInfo();
        $info->setSites([2, 3]);

        $this->assertFalse($info->isDisplayedOnSite(1));
        $this->assertTrue($info->isDisplayedOnSite(2));
        $this->assertTrue($info->isDisplayedOnSite(3));
    }

    public function testEmptySitesMeansAllSites(): void
    {
        $info = new AdminInfo();
        $info->setSites([2]);
        $info->setSites([]);

        $this->assertTrue($info->isForAllSites());
        $this->assertTrue($info->isDisplayedOnSite(1));
    }
}
