<?php

use PHPUnit\Framework\TestCase;
use App\Planno\Menu;

class ClassDatePlTest extends TestCase
{
    public function testDatePl(): void
    {
        // Setup
        $savDateDebutPlHebdo = $GLOBALS['config']['dateDebutPlHebdo'];
        $savNb_semaine = $GLOBALS['config']['nb_semaine'];

        // MT42875: week cycle should not be reset on new year when nb_semaine is not 2 or 4
        $GLOBALS['config']['dateDebutPlHebdo'] = '11/06/2018';
        $GLOBALS['config']['nb_semaine'] = 2;
        $datePl = new DatePl('2023-12-25');
        $this->assertEquals(2, $datePl->semaine3, 'nb_semaine=2 : Week 2 in the cycle');
        $datePl = new DatePl('2024-01-01');
        $this->assertEquals(1, $datePl->semaine3, 'nb_semaine=2 : Week 1 in the cycle (week cycle is reset on new year)');

        $GLOBALS['config']['nb_semaine'] = 3;
        $datePl = new DatePl('2023-12-25');
        $this->assertEquals(2, $datePl->semaine3, 'nb_semaine=3 : Week 2 in the cycle');
        $datePl = new DatePl('2024-01-01');
        $this->assertEquals(3, $datePl->semaine3, 'nb_semaine=3 : Week 3 in the cycle (week cycle is not reset on new year)');

        $GLOBALS['config']['nb_semaine'] = 4;
        $datePl = new DatePl('2023-12-25');
        $this->assertEquals(4, $datePl->semaine3, 'nb_semaine=4 : Week 4 in the cycle');
        $datePl = new DatePl('2024-01-01');
        $this->assertEquals(1, $datePl->semaine3, 'nb_semaine=4 : Week 1 in the cycle (week cycle is reset on new year)');

        // cycles with 5, 6, 7, 8, 9 and 10 weeks are implemented equally by weekId function
        // so a test with only one of them should be ok
        $GLOBALS['config']['nb_semaine'] = 6;
        $datePl = new DatePl('2023-12-25');
        $this->assertEquals(1, $datePl->semaine3, 'nb_semaine=6 : Week 1 in the cycle');
        $datePl = new DatePl('2024-01-01');
        $this->assertEquals(2, $datePl->semaine3, 'nb_semaine=6 : Week 2 in the cycle (week cycle is not reset on new year)');

        // Teardown
        $GLOBALS['config']['dateDebutPlHebdo'] = $savDateDebutPlHebdo;
        $GLOBALS['config']['nb_semaine'] = $savNb_semaine;
    }
}
