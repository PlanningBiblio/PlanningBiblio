<?php

namespace Tests\Service;

use App\Service\PublicHolidayService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PublicHolidayServiceTest extends KernelTestCase
{
    public function testgetFrenchHolidaysByDateRange(): void
    {
        $kernel = self::bootKernel();

        $start = new DateTime('2026-09-01');
        $end = (clone $start)->modify('+1 year');

        $days = PublicHolidayService::getFrenchHolidaysByDateRange($start, $end);

        $holidays = [
            ['01/11/2026', 'La Toussaint'],
            ['11/11/2026', 'Armistice'],
            ['25/12/2026', 'Noël'],
            ['01/01/2027', 'Jour de l\'an'],
            ['28/03/2027', 'Pâques'],
            ['29/03/2027', 'Lundi de Pâques'],
            ['01/05/2027', 'Fête du travail'],
            ['06/05/2027', 'Jeudi de l\'Ascension'],
            ['08/05/2027', '8 mai 1945'],
            ['16/05/2027', 'Pentecôte'],
            ['17/05/2027', 'Lundi Pentecôte'],
            ['14/07/2027', 'Fête nationale'],
            ['15/08/2027', 'Assomption'],
        ];

        $this->assertCount(13, $days);

        foreach ($holidays as $k => $v) {
            $this->assertEquals($v[0], $days[$k]->getDay()->format('d/m/Y'));
            $this->assertEquals($v[1], $days[$k]->getName());
            $this->assertEquals('Ajouté automatiquement', $days[$k]->getComment());
            $this->assertTrue($days[$k]->isPublicHoliday());
            $this->assertFalse($days[$k]->isClosed());
        }
    }
}
