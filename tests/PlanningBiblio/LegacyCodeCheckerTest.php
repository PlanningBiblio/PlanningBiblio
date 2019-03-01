<?php

use PlanningBiblio\LegacyCodeChecker;
use PHPUnit\Framework\TestCase;

class LegacyCodeCheckerTest extends TestCase
{
    public function testIsTwigizedWithUnknownPage() {
        $checker = new LegacyCodeChecker();
        $this->assertFalse($checker->isTwigized('admin/foo.php'));
    }

    public function testIsTwigizedWithTwigizedPage() {
        $checker = new LegacyCodeChecker();
        $this->assertTrue($checker->isTwigized('absences/infos.php'));
    }
}
