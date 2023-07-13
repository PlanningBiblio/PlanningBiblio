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

        $GLOBALS['config']['PlanningHebdo'] = 0;
        $_SERVER['SERVER_NAME'] = 'planno.local';

        $agent = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif', 'mail' => 'jdoenv@example.com',
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:15:00","17:15:00","2"],
                        "1" => ["09:00:00","12:30:00","13:15:00","17:15:00","3"],
                        "2" => ["10:00:00","13:30:00","15:15:00","18:15:00","-1"],
                        "3" => ["11:00:00","14:30:00","15:15:00","18:15:00","-1"],
                        "4" => ["11:00:00","14:30:00","15:15:00","18:15:00","1"],
                    )
                ),
                'sites' => json_encode(["1", "2", "3","4"])
            )
        );
        $client = static::createClient(); 

        # $this->logInAgent($agent, array(3,100));

#        $GLOBALS['config']['Multisites-nombre'] = 1;
#        $GLOBALS['config']['Multisites-site1'] = 'Site N°1';
        $GLOBALS['config']['ICS-Code'] = 0;

        // ICS-Export disabled
        $response = $client->request('GET', "/ical");
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "L'exportation ICS est désactivée", 'ICS-Export is disabled');

        // Agent id not provided 
        $GLOBALS['config']['ICS-Export'] = 1;
        $response = $client->request('GET', "/ical");
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "L'id de l'agent n'est pas fourni", 'Agent ID not provided');

        // Unknown id
        // TODO: What are we supposed to do in this case?

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

        // TODO: Check public service


        // Test holiday
        $GLOBALS['config']['Conges-Enable'] = 1; 
        $holiday_id = $this->createHolidayFor($agent);
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
#print($client->getResponse()->getContent());
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY: ICS holiday test', 'ICS export with holiday');

        // Test absence
        $holiday_id = $this->createAbsenceFor($agent);
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:ICS absence test ', 'ICS export with absence');
        
        // TODO: With interval in config

        // TODO: With interval in URL

        // With code (boucler sur les tests précédents avec et sans le code en paramètre)
        $GLOBALS['config']['ICS-Code'] = 1;
        $client->request('GET', "/ical", array("id" => $agent->id(), "absences" => 1));
        print($client->getResponse()->getContent());
        $content = explode("\n", $client->getResponse()->getContent());
        $this->assertEquals($content[27], 'SUMMARY:ICS absence test ', 'ICS export with absence');
        


print("\nend\n");

    }

    private function createHolidayFor($agent)
    {
        $date = new DateTime('now + 3 day');

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

        $date = new DateTime('now + 3 day');

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
