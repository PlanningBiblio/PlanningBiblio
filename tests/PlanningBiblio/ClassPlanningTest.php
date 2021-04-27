<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__ . '/../../public/include/db.php');

class ClassPlanningTest extends TestCase
{
    public function testWorkingHours() {
        $_SESSION['oups']['CSRFToken'] = 'FOO';
        $_SESSION['login_id'] = 1;

        $db = new \db();
        $db->CSRFToken = 'FOO';
        $db->delete('planning_hebdo');

        $p = new \planningHebdo();
        $p->add(array(
            'CSRFToken'     => 'FOO',
            'perso_id'      => 1,
            'debut'         => '2020-01-01',
            'fin'           => '2040-12-31',
            'temps'         => array(
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("09:30:00","12:00:00","14:00:00","18:30:00","1"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("","","","","")
            ),
            'validation'    => 2,
            'exception:'    => 0,
            'number_of_weeks' => 1

        ));

        $p = new \planningHebdo();
        $p->perso_id = 1;
        $p->debut = '2021-03-08';
        $p->fin = '2021-03-08';
        $p->valide = true;
        $p->fetch();

        $hours = $p->elements[0]['temps'];
        $id = $p->elements[0]['id'];
        $this->assertNotEmpty($hours, 'Working hours found');

        $monday_hours = $hours[0];
        $this->assertEquals('09:00:00', $monday_hours[0], 'Monday starts at 09:00:00');
        $this->assertEquals('18:00:00', $monday_hours[3], 'Monday ends at 18:00:00');
        $this->assertEquals('1', $monday_hours[4], 'Monday on site 1');

        $wednesday_hours = $hours[2];
        $this->assertEquals('09:30:00', $wednesday_hours[0], 'Wednesday starts at 09:30:00');
        $this->assertEquals('18:30:00', $wednesday_hours[3], 'Wednesday ends at 18:30:00');
        $this->assertEquals('1', $wednesday_hours[4], 'Wednesday on site 1');

        $p = new \planningHebdo();
        $p->add(array(
            'CSRFToken'     => 'FOO',
            'perso_id'      => 1,
            'debut'         => '2021-03-08',
            'fin'           => '2021-03-13',
            'temps'         => array(
                array("10:00:00","12:00:00","14:00:00","19:00:00","1"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("10:30:00","12:00:00","14:00:00","19:30:00","2"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("09:00:00","12:00:00","14:00:00","18:00:00","1"),
                array("","","","","")
            ),
            'validation'    => 2,
            'exception'    => $id,
            'number_of_weeks' => 1

        ));

        $p = new \planningHebdo();
        $p->perso_id = 1;
        $p->debut = '2021-03-08';
        $p->fin = '2021-03-08';
        $p->valide = true;
        $p->fetch();

        $exception_hours = $p->elements[0]['temps'];
        $this->assertEquals($id, $p->elements[0]['id'], 'Exception is about workinghour with id $id');

        $monday_exception_hours = $exception_hours[0];
        $this->assertEquals('10:00:00', $monday_exception_hours[0], 'Monday exception starts at 10:00:00');
        $this->assertEquals('19:00:00', $monday_exception_hours[3], 'Monday exception ends at 19:00:00');
        $this->assertEquals('1', $monday_exception_hours[4], 'Monday exception on site 1');

        $wednesday_exception_hours = $exception_hours[2];
        $this->assertEquals('10:30:00', $wednesday_exception_hours[0], 'Wednesday exception starts at 10:30:00');
        $this->assertEquals('19:30:00', $wednesday_exception_hours[3], 'Wednesday exception ends at 19:30:00');
        $this->assertEquals('2', $wednesday_exception_hours[4], 'Wednesday exception on site 2');
    }

    public function testWorkingHoursWithTwoWeeksTurnover() {
        $_SESSION['oups']['CSRFToken'] = 'FOO';
        $_SESSION['login_id'] = 1;
        $GLOBALS['config']['nb_semaine'] = "2";

        $db = new \db();
        $db->CSRFToken = 'FOO';
        $db->delete('planning_hebdo');

        $p = new \planningHebdo();
        $p->add(array(
            'CSRFToken'     => 'FOO',
            'perso_id'      => 1,
            'debut'         => '2020-01-01',
            'fin'           => '2040-12-31',
            'temps'         => array(
                "0" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "1" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "2" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "3" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "4" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "5" => ["","","","",""],
                "7" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "8" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "9" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "10" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "11" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "12" => ["","","","",""]
            ),
            'validation'    => 2,
            'exception:'    => 0,
            'number_of_weeks' => 2

        ));

        $p = new \planningHebdo();
        $p->perso_id = 1;
        $p->debut = '2021-03-08';
        $p->fin = '2021-03-08';
        $p->valide = true;
        $p->fetch();

        $hours = $p->elements[0]['temps'];
        $id = $p->elements[0]['id'];

        $p = new \planningHebdo();
        $p->add(array(
            'CSRFToken'     => 'FOO',
            'perso_id'      => 1,
            'debut'         => '2021-03-08',
            'fin'           => '2021-03-13',
            'temps'         => array(
                "0" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "1" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "2" => ["09:00:00","12:00:00","14:00:00","18:00:00","2"],
                "3" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "4" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "5" => ["","","","",""],
                "7" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "8" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "9" => ["09:00:00","12:00:00","14:00:00","18:00:00","2"],
                "10" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "11" => ["09:00:00","12:00:00","14:00:00","18:00:00","1"],
                "12" => ["","","","",""]
            ),
            'validation'    => 2,
            'exception'    => $id,
            'number_of_weeks' => 2

        ));

        $p = new \planningHebdo();
        $p->perso_id = 1;
        $p->debut = '2021-03-08';
        $p->fin = '2021-03-08';
        $p->valide = true;
        $p->fetch();

        $exception_hours = $p->elements[0]['temps'];

        $wednesday_odd_hours = $exception_hours[2];
        $this->assertEquals('2', $wednesday_odd_hours[4], 'Wednesday odd on site 2');

        $wednesday_even_hours = $exception_hours[9];
        $this->assertEquals('2', $wednesday_even_hours[4], 'Wednesday even on site 2');
    }
}
