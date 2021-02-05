<?php

use App\PlanningBiblio\Utils;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function test(){

        $utils = new Utils();
        $password = $utils->checkStrength('toto');
        $this->assertEquals('length', $utils->getReason());

        $password = $utils->checkStrength('toto1234');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('toto123!');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('toToTo!o');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('totoTOTO');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('totoT0to');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('Toto1234');
        $this->assertEquals('character', $utils->getReason());

        $password = $utils->checkStrength('Toto!234');
        $this->assertEquals('OK', $utils->getReason());

    }
}