<?php

use App\Model\Agent;
use App\Model\Absence;
use App\Model\WeekPlanning;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ICalendarControllerTest extends PLBWebTestCase
{

    public function testICalendar()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $GLOBALS['config']['PlanningHebdo'] = 0;

        $agent2 = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif',
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

        // TODO: Not logged-in

        $this->logInAgent($agent2, array(3,100));

#        $GLOBALS['config']['Multisites-nombre'] = 1;
#        $GLOBALS['config']['Multisites-site1'] = 'Site N°1';

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
        $client->request('GET', "/ical", array("id" => $agent2->id()));
        $content = explode("\n", $client->getResponse()->getContent());
        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals($code, '200', 'status code is 200');
        $this->assertEquals($content[1], 'X-WR-CALNAME:Service Public Doenv J', 'ICS export matches the agent');
        
        // Unknown login
        $client->request('GET', "/ical", array("login" => 'unkown.login'));
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals($content->{'error'}, "Impossible de trouver l'id associé au login unkown.login", 'Unknown login');

    }
}
