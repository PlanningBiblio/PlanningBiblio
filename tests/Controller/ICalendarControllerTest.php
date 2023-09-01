<?php

use App\Model\Absence;
use App\Model\Agent;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionLock;
use App\Model\Position;
use App\Model\WeekPlanning;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ICalendarControllerTest extends PLBWebTestCase
{
    private $builder;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->builder = new FixtureBuilder();
        $this->entityManager = $entityManager;
    }


    public function testICalendar()
    {
        $_SESSION['login_id'] = 1;

        $this->builder->delete(Agent::class);

        $_SERVER['SERVER_NAME'] = 'planno.local';

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif', 'mail' => 'jdoenv@example.com',
            )
        );
        $deletedagent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'deletedagent', 'nom' => 'Deleted', 'prenom' => 'Agent', 'actif' => 'Inactif', 'supprime' => 1, 'mail' => 'deletedagent@example.com',
            )
        );

        $GLOBALS['config']['ICS-Code'] = 0;

        // ICS-Export disabled
        $response = $this->client->request('GET', "/ical");
        $content = json_decode($this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '403', 'status code is 403');
        $this->assertEquals($content->{'error'}, "L'exportation ICS est désactivée", 'ICS-Export is disabled');

        // Agent id not provided
        $GLOBALS['config']['ICS-Export'] = 1;
        $response = $this->client->request('GET', "/ical");
        $content = json_decode($this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '400', 'status code is 400');
        $this->assertEquals($content->{'error'}, "L'id de l'agent n'est pas fourni", 'Agent ID not provided');

        // Unknown id
        $response = $this->client->request('GET', "/ical", array("id" => 42));
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "id inconnu", 'Unknown agent ID');

        // Agent id provided
        $this->client->request('GET', "/ical", array("id" => $agent->id()));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent id');

        // Unknown login
        $this->client->request('GET', "/ical", array("login" => 'unkown.login'));
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "Impossible de trouver l'id associé au login unkown.login", 'Unknown login');

        // Login provided
        $this->client->request('GET', "/ical", array("login" => 'jdoenv'));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent login');

        // Unknown email
        $this->client->request('GET', "/ical", array("mail" => 'unkown.email@example.com'));
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "Impossible de trouver l'id associé au mail unkown.email@example.com", 'Unknown email');

        // Email provided
        $this->client->request('GET', "/ical", array("mail" => 'jdoenv@example.com'));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent email');

         // Test planning position
        $this->createPlanningPositionFor($agent);
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:Rangement 4', 'ICS export with planning position');

        // Test holiday
        $GLOBALS['config']['Conges-Enable'] = 1;
        $this->createHolidayFor($agent);
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:Congé Payé ICS holiday test', 'ICS export with holiday');

        // Test absence
        $this->createAbsenceFor($agent);
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:ICS absence test ', 'ICS export with absence');

        // With interval in URL
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1, "interval" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals(sizeof($content), 23, 'Older than 1 day is not exported due to ICS-Interval config');

        // With interval in config
        $GLOBALS['config']['ICS-Interval'] = 1;
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals(sizeof($content), 23, 'Older than 1 day is not exported due to ICS-Interval config');
        $GLOBALS['config']['ICS-Interval'] = 0;

        // With code
        $GLOBALS['config']['ICS-Code'] = 1;
        $this->client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = json_decode($this->client->getResponse()->getContent());
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals($code, '401', 'status code is 401');
        $this->assertEquals($content->{'error'}, "Accès refusé", 'Access denied when code is needed but wrong or missing');
        $GLOBALS['config']['ICS-Code'] = 0;

        // No exports for deleted agent
        $this->createPlanningPositionFor($deletedagent);
        $this->createHolidayFor($deletedagent);
        $this->createAbsenceFor($deletedagent);
        $this->client->request('GET', "/ical", array("id" => $deletedagent->id(), "absences" => 1));
        $content = explode("\n", $this->client->getResponse()->getContent());
        $this->assertEquals(sizeof($content), 23, 'No exports for deleted agents');
    }

    private function createPlanningPositionFor($agent)
    {
        $date = new DateTime('now - 3 day');
        $now = new DateTime();

        $this->builder->delete(PlanningPosition::class);
        $this->builder->delete(PlanningPositionLock::class);

        $post = $this->entityManager->getRepository(Position::class)->findOneBy(array('nom' => 'Rangement 4'));

        $pl_post = $this->builder->build(
            PlanningPosition::class,
            array(
                'date' => $date,
                'debut' => new DateTime('now'),
                'fin' => new DateTime('now + 1 hour'),
                'poste' => $post->id(),
                'perso_id' => $agent->id(),
                'absent' => 0,
                'supprime' => 0,
                'grise' => 0,
                'site' => 1,
            )
        );


        $pl_post_lock = $this->builder->build
        (
            PlanningPositionLock::class,
            array(
                'date' => $date,
                'verrou' => 0,
                'verrou2' => 1,
                'site' => 1,
                'perso' => $agent->id(),
                'perso2' => $agent->id(),
            )
        );

    }

    private function createHolidayFor($agent)
    {
        $_SESSION['login_id'] = 1;

        $date = new DateTime('now - 3 day');

        $data = array(
            'debut'         => $date->format('d/m/Y'),
            'fin'           => $date->format('d/m/Y'),
            'hre_debut'     => '',
            'hre_fin'       => '',
            'commentaires'  => 'ICS holiday test',
            'heures'        => '7',
            'minutes'       => '0',
            'rest'          => 0,
            'debit'         => 'credit',
            'perso_id'      => $agent->id(),
            'saisie_par'    => 1,
            'valide'        => 1,
            'valide_n1'     => 0,
            'valide_init'   => 1
        );

        $c = new \conges();
        $c->CSRFToken = $this->CSRFToken;
        $c->add($data);

        return $c->id;
    }

    private function createAbsenceFor($agent, $status = 1)
    {
        // Function absence->add has not access to session.
        $_SESSION['login_id'] = 1;

        $date = new DateTime('now - 3 day');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->id());
        $absence->commentaires = '';
        $absence->motif = 'ICS absence test';
        $absence->valide = $status;
        $absence->CSRFToken = $this->CSRFToken;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';

        $absence->add();

        return $absence->id;
    }
}
