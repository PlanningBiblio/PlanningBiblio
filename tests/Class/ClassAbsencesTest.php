<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class ClassAbsencesTest extends TestCase
{
    public function testBuildICSContent()
    {
        $absence = new absences();
        $absence->perso_id = 8;
        $absence->dtstamp = '20220105T142922Z';
        $absence->commentaires = '';
        $absence->debut = '17/01/2022';
        $absence->fin = '17/01/2022';
        $absence->hre_debut = '08:00:00';
        $absence->hre_fin = '12:30:00';
        $absence->groupe = null;
        $absence->motif = 'Formation';
        $absence->motif_autre = '';
        $absence->rrule = 'FREQ=WEEKLY;WKST=MO;BYDAY=MO;COUNT=3';
        $absence->valide_n1 = 0;
        $absence->valide_n2 = 1;
        $absence->validation_n1 = '0000-00-00 00:00:00';
        $absence->validation_n2 = '2022-01-05 14:29:22';
        $absence->exdate = '';
        $absence->id = '';

        $ics_content = $absence->build_ics_content();
        $lines = explode(PHP_EOL, $ics_content);

        $this->assertEquals('BEGIN:VCALENDAR', $lines[0], 'BEGIN == "VCALENDAR"');
        $this->assertEquals('DTSTART;TZID=UTC:20220117T080000', $lines[14], 'DTSTART;TZID h:m');
        $this->assertEquals('DTEND;TZID=UTC:20220117T123000', $lines[15], 'DTEND;TZID h:m');
    }
}
