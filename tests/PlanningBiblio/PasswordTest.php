<?php

use App\PlanningBiblio\Utils;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function test(){

        $password = Utils::checkStrength('toto');
        $this->assertEquals(json_encode('length'), $password);

        $password = Utils::checkStrength('toto1234');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('toto123!');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('toToTo!o');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('totoTOTO');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('totoT0to');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('Toto1234');
        $this->assertEquals(json_encode('character'), $password);

        $password = Utils::checkStrength('Toto!234');
        $this->assertEquals(json_encode('OK'), $password);

    }
}