<?php

use App\Model\Agent;

use App\PlanningBiblio\Helper\HolidayHelper;
use App\PlanningBiblio\ClosingDay;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayHelperTest extends TestCase
{
    public function testgetCountedHoursWithCongesFulldayReferenceTime() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-fullday-switching-time'] = 4.25;
        $GLOBALS['config']['Conges-fullday-reference-time'] = 7.5;

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'me'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '12:30:00', '2' => '', '3' => ''),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '18:00:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '15:00:00'),
            // Agent is working 7 hours on Thursday.
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '16:00:00'),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($working_hours),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Request holiday on a Tuesday for all the day.
        // Agent works 9h this day. Reference day is 7h30
        // We debit 1 day and 1h30 as regularization (compensatory time)
        // hours are 7 (one day) to debit
        // rest is -1.5 (-1h30) to debit
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-01',
            'hour_start' => '00:00:00',
            'end' => '2021-06-01',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 9h on 1 day');
        $this->assertEquals(0, $result['minutes'], 'request 9h on 1 day');
        $this->assertEquals('7h00', $result['hr_hours'], 'request 9h on 1 day');
        $this->assertEquals(-1.5, $result['rest'], 'request 9h on 1 day');
        $this->assertEquals('1h30', $result['hr_rest'], 'request 9h on 1 day');
        $this->assertEquals(1, $result['days'], 'request 9h on 1 day');

        // Request holiday on a Monday (3h30 of work).
        // Agent works 3h30 this day. Switching time is 4.25.
        // Agent hours is under switching time so, reference day
        // is 7h30 / 2: 3h45
        // We debit 1 day and credit 0h15
        // Hours are 3h30 (half a day)
        // rest is 0.25 (0h15) to credit
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-05-31',
            'hour_start' => '00:00:00',
            'end' => '2021-05-31',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(3, $result['hours'], 'request 3h30 on 1 day');
        $this->assertEquals(50, $result['minutes'], 'request 3h30 on 1 day');
        $this->assertEquals('3h30', $result['hr_hours'], 'request 3h30 on 1 day');
        $this->assertEquals(0.25, $result['rest'], 'request 3h30 on 1 day');
        $this->assertEquals('0h15', $result['hr_rest'], 'request 3h30 on 1 day');
        $this->assertEquals(0.5, $result['days'], 'request 3h30 on 1 day');

        // Request holiday on Wednesday.
        // Agent works 6h this day. Reference time is 7h30.
        // Debit one day (7h) and credit 1h30
        // Hours are 7h
        // Rest is 1.5 (1h30) to credit.
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-02',
            'hour_start' => '00:00:00',
            'end' => '2021-06-02',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 6h on 1 day');
        $this->assertEquals(0, $result['minutes'], 'request 6h on 1 day');
        $this->assertEquals('7h00', $result['hr_hours'], 'request 6h on 1 day');
        $this->assertEquals(1.5, $result['rest'], 'request 6h on 1 day');
        $this->assertEquals('1h30', $result['hr_rest'], 'request 6h on 1 day');
        $this->assertEquals(1, $result['days'], 'request 6h on 1 day');

        // Request holiday from Monday to Wednesday.
        // Agent works 9h + 3h30 + 6h.
        // Day by day, we debit 1 day + 0,5 day + 1 day
        // Debit 2.5 days
        // Hours are 7 + 3.5 + 7 : 17.50 (2.5 days)
        // Rest is 0.25 (-1.5 + 0.25 + 1.5).
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-05-31',
            'hour_start' => '00:00:00',
            'end' => '2021-06-02',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(17, $result['hours'], 'request 18h30 on 3 day');
        $this->assertEquals(50, $result['minutes'], 'request 18h30 on 3 day');
        $this->assertEquals('17h30', $result['hr_hours'], 'request 18h30 on 3 day');
        $this->assertEquals(0.25, $result['rest'], 'request 18h30 on 3 day');
        $this->assertEquals('0h15', $result['hr_rest'], 'request 18h30 on 3 day');
        $this->assertEquals(2.5, $result['days'], 'request 18h30 on 3 day');

        // Add a closing day on Thursday.
        $j = new ClosingDay();
        $j->CSRFToken = '00000';
        $j->update(array(
            'annee' => '2020-2021',
            'jour' => array(0 => '03/06/2021'),
            'ferie' => array(0 => 1),
            'fermeture' => array(0 => 1),
            'nom' => array(0 => 'Closed'),
            'commentaire' => array(0 => "It's closed !"),
        ));

        // Request holiday on a closing day.
        // Only regularization are returned.
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-03',
            'hour_start' => '00:00:00',
            'end' => '2021-06-03',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(0, $result['hours'], 'Closing day, no hour');
        $this->assertEquals(0, $result['minutes'], 'Closing day, no minute');
        $this->assertEquals('0h00', $result['hr_hours'], 'Closing day, no hour');
        $this->assertEquals(0.50, $result['rest'], 'Closing day, Half hour regul');
        $this->assertEquals('0h30', $result['hr_rest'], 'Closing day, Half hour regul');
        $this->assertEquals(0, $result['days'], 'Closing day: 0 day');
    }

    public function testgetCountedHoursHolidayModeDays() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-fullday-switching-time'] = '';
        $GLOBALS['config']['Conges-fullday-reference-time'] = '';

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'roger'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '12:30:00', '2' => '', '3' => ''),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '18:00:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '15:00:00'),
            3 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($working_hours),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Request holiday on a Tuesday for all the day (9h of work).
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-01',
            'hour_start' => '00:00:00',
            'end' => '2021-06-01',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(00, $result['minutes'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals('7h00', $result['hr_hours'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(0, $result['rest'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals('', $result['hr_rest'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(1, $result['days'], 'request 9h on 1 day (default reference time)');

    }

    public function testgetCountedHoursHolidayModeHours() {
        $GLOBALS['config']['Conges-Mode'] = 'heures';
        $GLOBALS['config']['Conges-Recuperations'] = 0;
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['config']['Conges-fullday-switching-time'] = '4';
        $GLOBALS['config']['Conges-fullday-reference-time'] = '';

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'e.sosson'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:10:00', '1' => '12:00:00', '2' => '12:40:00', '3' => '17:00:00'),
            1 => array('0' => '09:10:00', '1' => '12:00:00', '2' => '12:40:00', '3' => '17:00:00'),
            2 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            3 => array('0' => '09:10:00', '1' => '12:00:00', '2' => '12:40:00', '3' => '17:00:00'),
            4 => array('0' => '09:10:00', '1' => '12:00:00', '2' => '12:40:00', '3' => '16:40:00'),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2035-12-31',
                'temps' => json_encode($working_hours),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        $holidayHlper = new HolidayHelper(array(
            'start' => '2022-08-01',
            'hour_start' => '00:00:00',
            'end' => '2022-08-12',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(56, $result['hours']);
        $this->assertEquals('56h40', $result['hr_hours']);
    }
}

