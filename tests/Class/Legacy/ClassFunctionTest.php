<?php

use PHPUnit\Framework\TestCase;

class ClassFunctionTest extends TestCase
{
    public function testDateSQL(): void
    {
        $this->assertEquals('1980-12-25',          dateSQL('25/12/1980'),          'a valid date is converted to an SQL date');
        $this->assertEquals('1980-12-25 12:00:00', dateSQL('25/12/1980 12:00:00'), 'a valid date and hour is converted to an SQL date');
        $this->assertEquals('1980-12-25',          dateSQL('1980-12-25'),          'a valid SQL date is kept');
        $this->assertEquals('1980-12-25 12:00:00', dateSQL('1980-12-25 12:00:00'), 'a valid SQL date and hour is kept');
        $this->assertEquals('',                    dateSQL('SQL Injection'),       'a invalid date is converted to an empty string');
    }

    public function testDateFr3(): void
    {
        $this->assertEquals('25/12/1980',          dateFr3('1980-12-25'),          'a valid SQL date is converted to a fr date');
        $this->assertEquals('25/12/1980 12:00:00', dateFr3('1980-12-25 12:00:00'), 'a valid SQL date and hour is converted to a fr date');
        $this->assertEquals('25/12/1980',          dateFr3('25/12/1980'),          'a valid fr date is kept');
        $this->assertEquals('25/12/1980 12:00:00', dateFr3('25/12/1980 12:00:00'), 'a valid fr date and hour is kept');
        $this->assertEquals('',                    dateFr3('SQL Injection'),       'a invalid date is converted to an empty string');
    }
}
