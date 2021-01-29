<?php

use App\PlanningBiblio\Utils;
use PHPUnit\Framework\TestCase;

class agentNameTest extends TestCase
{
    /**
     * @test
     */
    public function displayName() {

        $GLOBALS['config']['Agent-FullNameFormat'] = 'Surname Name';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'full');
        $this->assertEquals('Dupond Jean', $displayedName);

        $GLOBALS['config']['Agent-FullNameFormat'] = 'Name Surname';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'full');
        $this->assertEquals('Jean Dupond', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'N. Surname';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('J. Dupond', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'Name S.';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('Jean D.', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'Surname N.';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('Dupond J.', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'S. Name';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('D. Jean', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'Name';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('Jean', $displayedName);

        $GLOBALS['config']['Agent-NameFormat'] = 'Surname';
        $displayedName = Utils::agentName('Jean', 'Dupond', 'short');
        $this->assertEquals('Dupond', $displayedName);
    }
}
