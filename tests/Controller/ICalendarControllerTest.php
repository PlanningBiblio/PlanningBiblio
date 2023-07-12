<?php

use App\Model\Agent;
use App\Model\Absence;
use App\Model\WeekPlanning;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ICalendarControllerTest extends PLBWebTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
    }


    public function testICalendar()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $_SERVER['SERVER_NAME'] = 'planno.local';

        $agent = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif', 'mail' => 'jdoenv@example.com',
            )
        );
        $client = static::createClient();

        $GLOBALS['config']['ICS-Code'] = 0;

        // ICS-Export disabled
        $response = $client->request('GET', "/ical");
        $content = json_decode($client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '403', 'status code is 403');
        $this->assertEquals($content->{'error'}, "L'exportation ICS est désactivée", 'ICS-Export is disabled');

        // Agent id not provided
        $GLOBALS['config']['ICS-Export'] = 1;
        $response = $client->request('GET', "/ical");
        $content = json_decode($client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '400', 'status code is 400');
        $this->assertEquals($content->{'error'}, "L'id de l'agent n'est pas fourni", 'Agent ID not provided');

        // Unknown id
        $response = $client->request('GET', "/ical", array("id" => 42));
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "id inconnu", 'Unknown agent ID');

        // Agent id provided
        $client->request('GET', "/ical", array("id" => $agent->id()));
        $content = explode("\n", $client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent id');

        // Unknown login
        $client->request('GET', "/ical", array("login" => 'unkown.login'));
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "Impossible de trouver l'id associé au login unkown.login", 'Unknown login');

        // Login provided
        $client->request('GET', "/ical", array("login" => 'jdoenv'));
        $content = explode("\n", $client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent login');

        // Unknown email
        $client->request('GET', "/ical", array("mail" => 'unkown.email@example.com'));
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "Impossible de trouver l'id associé au mail unkown.email@example.com", 'Unknown email');

        // Email provided
        $client->request('GET', "/ical", array("mail" => 'jdoenv@example.com'));
        $content = explode("\n", $client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent email');

         // Test planning position
        $this->createPlanningPositionFor($agent);
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:Accueil', 'ICS export with planning position');

        // Test holiday
        $GLOBALS['config']['Conges-Enable'] = 1;
        $this->createHolidayFor($agent);
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY: ICS holiday test', 'ICS export with holiday');

        // Test absence
        $this->createAbsenceFor($agent);
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:ICS absence test ', 'ICS export with absence');

        // With interval in URL
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1, "interval" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals(sizeof($content), 23, 'Older than 1 day is not exported due to ICS-Interval config');

        // With interval in config
        $GLOBALS['config']['ICS-Interval'] = 1;
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals(sizeof($content), 23, 'Older than 1 day is not exported due to ICS-Interval config');

        // With code
        $GLOBALS['config']['ICS-Code'] = 1;
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = json_decode($client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '401', 'status code is 401');
        $this->assertEquals($content->{'error'}, "Accès refusé", 'Access denied when code is needed but wrong or missing');
    }

    private function createPlanningPositionFor($agent)
    {
        $date = new DateTime('now - 3 day');
        $now = new DateTime();

        $db = new \db();

        $db->CSRFToken = $this->CSRFToken;
        $insert = array(
            "date"       => $date->format('Y-m-d'),
            "debut"      => '11:00:00',
            "fin"        => '12:00:00',
            "poste"      => 1,
            "site"       => 1,
            "perso_id"   => $agent->id(),
            "chgt_login" => 1,
            "chgt_time"  => $now->format('Y-m-d')
        );
        $db->insert("pl_poste", $insert);

        $insert = array(
            "date"        => $date->format('Y-m-d'),
            "verrou"      => 1,
            "validation"  => 1,
            "perso"       => $agent->id(),
            "verrou2"     => 1,
            "validation2" => $now->format('Y-m-d'),
            "perso2"      => $agent->id(),
            "vivier"      => $now->format('Y-m-d'),
            "site"        => 1
        );
        $db->insert("pl_poste_verrou", $insert);

        $insert = array(
            "id" => 1,
            "nom"        => "Accueil",
        );
        $db->insert("postes", $insert);



    }
    private function createHolidayFor($agent)
    {
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
