<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/include/function.php');

class IncludeFunctionTest extends TestCase
{
    public function testHeure4(): void
    {
        // Numeric to string
        $heure = heure4('0');
        $this->assertEquals($heure, null, '0 == null');

        $heure = heure4('0', true);
        $this->assertEquals($heure, '0h00', '0 == 0h00');

        $heure = heure4('0.5');
        $this->assertEquals($heure, '0h30', '0.5 == 0h30');

        // MT 40009
        $heure = heure4('-0.5');
        $this->assertEquals($heure, '-0h30', '-0.5 == -0h30');

        // String to numeric
        $heure = heure4('0h30');
        $this->assertEquals($heure, '0.50', '0h30 == 0.50');
    }
}
