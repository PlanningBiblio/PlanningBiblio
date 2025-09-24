<?php

use PHPUnit\Framework\TestCase;

use App\Entity\Agent;
use App\Entity\ConfigParam;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class ClassAbsencesTest extends TestCase
{
    public function testBuildICSContent(): void
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

        $ics_content = $absence->build_ics_content();
        $lines = explode(PHP_EOL, $ics_content);

        $timezone = date_default_timezone_get();

        $this->assertEquals('BEGIN:VCALENDAR', $lines[0], 'BEGIN == "VCALENDAR"');
        $this->assertEquals("DTSTART;TZID=$timezone:20220117T080000", $lines[14], 'DTSTART;TZID h:m');
        $this->assertEquals("DTEND;TZID=$timezone:20220117T123000", $lines[15], 'DTEND;TZID h:m');
    }

    public function testRecipients(): void
    {
        global $entityManager;

        # MT46949: Check that getRecipients handles empty values for Absences-notifications-*
        $absence = new absences();
        $absence->perso_id = 8;
        $responsables = array();
        $member = $entityManager->getRepository(Agent::class)->find(1);
        $this->setParam('Absences-notifications-A1', '');
        $this->setParam('Absences-notifications-A2', '');
        $this->setParam('Absences-notifications-A3', '');
        $this->setParam('Absences-notifications-A4', '');
        $this->setParam('Absences-notifications-B1', '');
        $this->setParam('Absences-notifications-B2', '');
        $this->setParam('Absences-notifications-B3', '');
        $this->setParam('Absences-notifications-B4', '');
        $absence->getRecipients("-A2", $responsables, $member);
        $destinataires = $absence->recipients;
        $this->assertEmpty($destinataires, 'When all Absences-notification* are empty, recipients is empty');

        $this->setParam('Absences-notifications-A1', '[]');
        $this->setParam('Absences-notifications-A2', '[]');
        $this->setParam('Absences-notifications-A3', '[]');
        $this->setParam('Absences-notifications-A4', '[]');
        $this->setParam('Absences-notifications-B1', '[]');
        $this->setParam('Absences-notifications-B2', '[]');
        $this->setParam('Absences-notifications-B3', '[]');
        $this->setParam('Absences-notifications-B4', '[]');
        $absence->getRecipients("-A2", $responsables, $member);
        $destinataires = $absence->recipients;
        $this->assertEmpty($destinataires, 'When all Absences-notification* are empty arrays, recipients is empty');



    }

    protected function setParam($name, $value)
    {
        global $entityManager;
        $absence = new absences();
        $GLOBALS['config'][$name] = $value;
        $param = $entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $name]);

        $param->setValue($value);
        $entityManager->persist($param);
        $entityManager->flush();
    }

}
