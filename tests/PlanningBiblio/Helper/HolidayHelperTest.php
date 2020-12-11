<?php

use App\PlanningBiblio\Helper\HolidayHelper;
use PHPUnit\Framework\TestCase;

class HolidayHelperTest extends TestCase
{
    public function testStartEnd() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 0,
            'start_halfday' => '',
            'end_halfday' => ''
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '13:00:00',
            'end' => '2020-12-24',
            'hour_end' => '17:00:00'
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('13:00:00', $startHour, 'start is 13:00');
        $this->assertEquals('17:00:00', $endHour, 'halfday option disabled, end is 17:00');
    }

    public function testStartEndNoHalfdayOption() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => '',
            'end_halfday' => ''
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-24',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('00:00:00', $startHour, 'Halfday option disabled, start is 00:00');
        $this->assertEquals('23:59:59', $endHour, 'halfday option disabled, end is 23:59');
    }

    public function testStartEndEmptyhalday() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-24',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('', $startHour, 'wihtout start_halfday or start_halfday, start is empty');
        $this->assertEquals('', $endHour, 'wihtout start_halfday or end_halfday, end is empty');
    }

    public function testStartEndAfternoonSameDay() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'afternoon',
            'end_halfday' => 'afternoon'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-24',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('12:00:00', $startHour, 'Afternoon holiday starts at 12:00');
        $this->assertEquals('23:59:59', $endHour, 'Afternoon holiday ends at 23:59');
    }

    public function testStartEndMorningSameDay() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'morning',
            'end_halfday' => 'morning'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-24',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('00:00:00', $startHour, 'Morning holiday starts at 00:00');
        $this->assertEquals('12:00:00', $endHour, 'Morning holiday ends at 12:00');
    }

    public function testStartEndFulldaySameDay() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'fullday',
            'end_halfday' => 'fullday'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-24',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('00:00:00', $startHour, 'Fullday holiday starts at 00:00');
        $this->assertEquals('23:59:59', $endHour, 'Fullday holiday ends at 23:59');
    }

    public function testStartEndFullday() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'fullday',
            'end_halfday' => 'fullday'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-25',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('00:00:00', $startHour, 'Fullday first holiday starts at 00:00');
        $this->assertEquals('23:59:59', $endHour, 'Fullday last holiday ends at 23:59');
    }

    public function testStartEndAfternoonFullday() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'afternoon',
            'end_halfday' => 'fullday'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-25',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('12:00:00', $startHour, 'Afternoon first holiday starts at 12:00');
        $this->assertEquals('23:59:59', $endHour, 'Fullday last holiday ends at 23:59');
    }

    public function testStartEndAfternoonMorning() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $GLOBALS['dispatcher'] = '';

        $params = array(
            'halfday' => 1,
            'start_halfday' => 'afternoon',
            'end_halfday' => 'morning'
        );


        $holidayHelper = new HolidayHelper(array(
            'start' => '2020-12-24',
            'hour_start' => '',
            'end' => '2020-12-25',
            'hour_end' => ''
        ));

        list($startHour, $endHour) = $holidayHelper->startEndHours($params);

        $this->assertEquals('12:00:00', $startHour, 'Afternoon first holiday starts at 12:00');
        $this->assertEquals('12:00:00', $endHour, 'Morning last holiday ends at 12:00');
    }
}
