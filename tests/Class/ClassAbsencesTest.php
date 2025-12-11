<?php

use PHPUnit\Framework\TestCase;

use App\Entity\Agent;
use App\Entity\AbsenceReason;
use App\Entity\Config;
use App\Entity\Manager;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

require_once(__DIR__ . '/../../legacy/Class/class.absences.php');

class ClassAbsencesTest extends PLBWebTestCase
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

        $ics_content = $absence->build_ics_content();
        $lines = explode(PHP_EOL, $ics_content);

        $timezone = date_default_timezone_get();

        $this->assertEquals('BEGIN:VCALENDAR', $lines[0], 'BEGIN == "VCALENDAR"');
        $this->assertEquals("DTSTART;TZID=$timezone:20220117T080000", $lines[14], 'DTSTART;TZID h:m');
        $this->assertEquals("DTEND;TZID=$timezone:20220117T123000", $lines[15], 'DTEND;TZID h:m');
    }

    public function testRecipients()
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

    public function testGetRecipients2()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Absences-validation', 1);
        # Absence must be validated at level1 first
        $this->setParam('Absences-Validation-N2', 1);

        # Absence reason with workflow B
        $workflow_b_reason = 'Reason with workflow B';
        $ar = new AbsenceReason();
        $ar->setValue($workflow_b_reason);
        $ar->setRank(1);
        $ar->setType(1);
        $ar->setTeleworking(0);
        $ar->setNotificationWorkflow('B');
        $this->entityManager->persist($ar);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent1 = $builder->build(Agent::class, array('mail' => 'agent@example.com'));
        $manager_level1_for_agent1 = $builder->build(Agent::class, array('mail' => 'managerlevel1@example.com'));
        $manager_level2_for_agent1 = $builder->build(Agent::class, array('mail' => 'managerlevel2@example.com'));

        $managed1 = new Manager();
        $managed1->setUser($agent1);
        $managed1->setLevel1(1);
        $managed1->setLevel1Notification(1);
        $managed1->setLevel2(0);
        $manager_level1_for_agent1->addManaged($managed1);

        $managed2 = new Manager();
        $managed2->setUser($agent1);
        $managed2->setLevel1(0);
        $managed2->setLevel2(1);
        $managed2->setLevel2Notification(1);
        $manager_level2_for_agent1->addManaged($managed2);

        $this->entityManager->persist($managed1);
        $this->entityManager->persist($managed2);
        $this->entityManager->persist($manager_level1_for_agent1);
        $this->entityManager->persist($manager_level2_for_agent1);
        $this->entityManager->persist($agent1);
        $this->entityManager->flush();

        $this->assertTrue( $manager_level1_for_agent1->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertFalse($manager_level1_for_agent1->isManagerOf(array($agent1->getId()), 'level2'));

        $this->assertFalse($manager_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertTrue( $manager_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level2'));

        $id = $this->createAbsenceFor($agent1, 0);
        $this->assertEquals('managerlevel1@example.com', $this->getRecipients($id, 1)[0], "creating a workflow A absence notifies manager level1");
        $this->assertEquals('managerlevel2@example.com', $this->getRecipients($id, 3)[0], "validating a workflow A absence at level 1 notifies manager level2");
        $this->assertEquals('agent@example.com', $this->getRecipients($id, 4)[0], "validating a workflow A absence at level 2 notifies agent");

        $id = $this->createAbsenceFor($agent1, 0, $workflow_b_reason);
        $this->assertEquals('managerlevel1@example.com', $this->getRecipients($id, 1)[0], "creating a workflow B absence notifies manager level1");
        # getRecipients2 on workflow B will never be called with notifications = 3, because it is directly validated at level 2
        $this->assertEquals('agent@example.com', $this->getRecipients($id, 4)[0], "validating a workflow B absence at level 2 notifies agent");

        # Revert params
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Absences-validation', 0);
        $this->setParam('Absences-Validation-N2', 0);

    }

    private function getRecipients($absence_id, $notifications) {
        $a = new \absences();
        $a->fetchById($absence_id);
        $agents = $a->elements['agents'];
        $debut = $a->elements['debut'];
        $fin = $a->elements['fin'];
        $a->getRecipients2(null, $agents, $notifications, 500, $debut, $fin);
        return $a->recipients;
    }

    private function createAbsenceFor($agent, $status = 0, $motif = 'default')
    {
        $date = new DateTime('now');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->getId());
        $absence->commentaires = '';
        $absence->motif = $motif;

        # Note:

        # In database:
        # valide = 0  and valide_n1 = 0  when the absence is asked for
        # valide = 0  and valide_n1 = id when the absence is approved (waiting for hierarchy approval)
        # valide = id and valide_n1 = id when the absence is approved

        # In absence object:
        # valide = 0 when is the absence is asked for
        # valide = 1 when is the absence is approved
        # valide = 2 when the absence is approved (waiting for hierarchy approval)

        # In a nutshell:
        # database valide    = object valide_n1
        # database valide_n1 = object valide_n2

        $absence->valide = $status;
        $absence->valide_n1 = $status;
        $absence->valide_n2 = $status;
        $absence->CSRFToken = $this->CSRFToken;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';

        $absence->add();
#        print_r($absence->recipients);

        return $absence->id;
    }

    protected function setParam($name, $value)
    {
        global $entityManager;
        $absence = new absences();
        $GLOBALS['config'][$name] = $value;
        $param = $entityManager
            ->getRepository(Config::class)
            ->findOneBy(['nom' => $name]);

        $param->setValue($value);
        $entityManager->persist($param);
        $entityManager->flush();
    }

}
