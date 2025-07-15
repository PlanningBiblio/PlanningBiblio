<?php

use App\Model\Absence;
use App\Model\Agent;
use App\Model\Holiday;
use App\Model\PlanningPosition;
use App\Model\Position;
use App\Model\WorkingHour;

use App\PlanningBiblio\WorkingHours;

use Symfony\Component\DomCrawler\Crawler;

use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;


class PlanningJobControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }


    public function createWeekPlanningFor($agent)
    {

        $start = \DateTime::createFromFormat("d/m/Y", "01/10/2021");
        $end = \DateTime::createFromFormat("d/m/Y", "01/12/2023");

        $workingHours = array(
            0 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
            1 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
            2 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
            3 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
            4 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
            5 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '1'),
        );

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 0;

        $wh = new WorkingHours($workingHours);

        $planning = $this->builder->build(WorkingHour::class, array(
            'perso_id' => $agent->id(),
            'debut' => $start,
            'fin' => $end,
            'temps' => json_encode($workingHours),
            'valide_n1' => 0,
            'valide' => 1,
            'nb_semaine' => 1
        ));
    }


    public function createPlanningPostFor($agent,$poste)
    {

        $start = \DateTime::createFromFormat("H:i:s", "08:00:00");
        $end = \DateTime::createFromFormat("H:i:s", "19:30:00");
        $date = \DateTime::createFromFormat("d/m/Y", "01/11/2022");

        $id = $poste->id();

        $planning_post = $this->builder->build(PlanningPosition::class, array(
            'perso_id' => $agent->id(),
            'debut' => $start,
            'fin' => $end,
            'date'=> $date,
            'poste' => json_encode($id),
            'site' => json_encode(1),
            'valide_n1' => 0,
            'valide' => 0,
            'nb_semaine' => 1,
            'absent' => 0,
            'supprime' => 0,
            'grise' => 0,
        ));

        return $planning_post;
    }


    public function testContextMenuAgentsDispo()
    {
        $GLOBALS['config']['PlanningHebdo-Agents'] = 0;
        $GLOBALS['config']['toutlemonde'] = 0;
        $GLOBALS['config']['PlanningHebdo'] = 1;
        $GLOBALS['config']['ClasseParService'] = 0;
        $GLOBALS['config']['agentsIndispo'] = 0;
        $GLOBALS['config']['Multisites-nombre'] = 1;
        $GLOBALS['config']['Multisites-site1'] = 'site';
        $GLOBALS['config']['MSGraph-ClientID'] = '';

        $builder = new FixtureBuilder();

        // Create post
        $builder->delete(Position::class);

        $post = $builder->build(Position::class, array(
            'nom' => 'administratif',
            'statistiques' => 1,
            'teleworking' => 1,
            'bloquant' => 0,
        ));
        $id = $post->id();


        // Create agents
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->id();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => json_encode($id),
            'service' => 'Pôle Public', 'sites' => json_encode(array("1")), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->droits());

        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);
        $this->createWeekPlanningFor($jdupont);

        // Create Absence
        $builder->delete(Absence::class);

        $start = \DateTime::createFromFormat("d/m/Y", "31/10/2022");
        $end = \DateTime::createFromFormat("d/m/Y", "02/11/2022");

        $jdupont_off = $this->builder->build(Absence::class, array(
            'debut' => $start,
            'fin' => $end,
            'perso_id' => $jdupont->id(),
            'valide_n1' => 1,
            'valide' =>1,
            'supprime' => 0,
            'groupe' => 1
        ));

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $response = $this->client->getResponse();

        $result = explode('["callback":protected]', $response);

        $this->assertStringContainsString('"position_name":"' .$post->nom() .'"', $result[0]);

        $this->assertStringContainsString('"position_id":"' .$post->id() .'"', $result[0]);

        $this->assertStringContainsString('"date":"2022-11-01"', $result[0]);

        $this->assertStringContainsString('"start":"08:00:00"', $result[0]);

        $this->assertStringContainsString('"end":"19:30:00"', $result[0]);

        $this->assertStringContainsString('"site":"1"', $result[0]);

        $this->assertStringContainsString('"group_tab_hide":0', $result[0]);

        $this->assertStringContainsString('"nb_agents":0', $result[0]);

        $this->assertStringContainsString('"max_agents":"4"', $result[0]);

        $this->assertStringContainsString('"agent_id":"' .$abreton->id() .'"', $result[0]);

        $this->assertStringContainsString('"agent_name":"' .$abreton->nom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$kboivin->nom() .' ' .$kboivin->prenom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$jdevoe->nom() .' ' .$jdevoe->prenom() .'"', $result[0]);

        // Check if the absent agent is in not in the context menu
        $this->assertStringNotContainsString('"name_title":"' .$jdupont->nom() .' ' .$jdupont->prenom() .'"', $result[0]);
    }


    public function testContextMenuWithAgentsIndispo()
    {
        $GLOBALS['config']['PlanningHebdo'] = 1;
        $GLOBALS['config']['Absences-validation'] = 1;
        $GLOBALS['config']['ClasseParService'] = 0;
        $GLOBALS['config']['agentsIndispo'] = 1;
        $GLOBALS['config']['toutlemonde'] = 0;
        $GLOBALS['config']['Multisites-nombre'] = 1;
        $GLOBALS['config']['Planning-agents-volants'] = 0;
        $GLOBALS['config']['Multisites-site1'] = 'site';

        $builder = new FixtureBuilder();

        // Create post
        $builder->delete(Position::class);

        $post = $builder->build(Position::class, array(
            'nom' => 'administratif',
            'statistiques' => 1,
            'teleworking' => 1,
            'bloquant' => 0,
        ));
        $id = $post->id();


        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->id();
        $agentHoliday = $this->builder->build(Agent::class, array(
            'login' => 'holiday', 'nom' => 'Day', 'prenom' => 'Holy', 'postes' => json_encode($id),
            'service' => 'Pôle Public', 'sites' => json_encode(array("1")), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array(99,100)
        ));

        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => json_encode($id),
            'service' => 'Pôle Public', 'sites' => json_encode(array("1")), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->droits());

        // Create Holiday
        $builder->delete(Holiday::class);

        $start = \DateTime::createFromFormat("d/m/Y", "18/10/2022");
        $end = \DateTime::createFromFormat("d/m/Y", "04/11/2022");

        $holiday = $builder->build(Holiday::class, array(
            'debut' => $start,
            'fin' => $end,
            'perso_id' => $agentHoliday->id(),
            'valide_n1' => 1,
            'valide' =>0,
            'supprime' => 0,
            'information' => 0
        ));


        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $response = $this->client->getResponse();

        $result = explode('["callback":protected]', $response);

        $this->assertStringContainsString('"position_name":"' .$post->nom() .'"', $result[0]);

        $this->assertStringContainsString('"position_id":"' .$post->id() .'"', $result[0]);

        $this->assertStringContainsString('"date":"2022-11-01"', $result[0]);

        $this->assertStringContainsString('"start":"08:00:00"', $result[0]);

        $this->assertStringContainsString('"end":"19:30:00"', $result[0]);

        $this->assertStringContainsString('"site":"1"', $result[0]);

        $this->assertStringContainsString('"group_tab_hide":0', $result[0]);

        $this->assertStringContainsString('"nb_agents":0', $result[0]);

        $this->assertStringContainsString('"max_agents":"4"', $result[0]);

        $this->assertStringContainsString('"agent_id":"' .$abreton->id() .'"', $result[0]);

        $this->assertStringContainsString('"agent_name":"' .$abreton->nom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$kboivin->nom() .' ' .$kboivin->prenom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$jdevoe->nom() .' ' .$jdevoe->prenom() .'"', $result[0]);

        $this->assertStringContainsString('{"id":"' .$agentHoliday->id() .'","nom":"' .$agentHoliday->nom() .'","prenom":"' .$agentHoliday->prenom() .'"', $result[0]);
    }


    public function testContextMenuWithClasseParService()
    {
        $GLOBALS['config']['PlanningHebdo-Agents'] = 0;
        $GLOBALS['config']['PlanningHebdo'] = 1;
        $GLOBALS['config']['ClasseParService'] = 1;
        $GLOBALS['config']['agentsIndispo'] = 0;
        $GLOBALS['config']['toutlemonde'] = 0;
        $GLOBALS['config']['Multisites-nombre'] = 1;
        $GLOBALS['config']['Multisites-site1'] = 'site';

        $builder = new FixtureBuilder();

        // Create post
        $builder->delete(Position::class);
        $post = $builder->build(Position::class, array(

            'nom' => 'administratif',
            'statistiques' => 1,
            'teleworking' => 1,
            'bloquant' => 0,
        ));
        $id = $post->id();


        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->id();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => json_encode($id),
            'service' => 'Pôle Public', 'sites' => json_encode(array("1")), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->droits());

        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $response = $this->client->getResponse();

        $result = explode('["callback":protected]', $response);

        $this->assertStringContainsString('"position_name":"' .$post->nom() .'"', $result[0]);

        $this->assertStringContainsString('"position_id":"' .$post->id() .'"', $result[0]);

        $this->assertStringContainsString('"date":"2022-11-01"', $result[0]);

        $this->assertStringContainsString('"start":"08:00:00"', $result[0]);

        $this->assertStringContainsString('"end":"19:30:00"', $result[0]);

        $this->assertStringContainsString('"site":"1"', $result[0]);

        $this->assertStringContainsString('"group_tab_hide":1', $result[0]);

        $this->assertStringContainsString('"nb_agents":0', $result[0]);

        $this->assertStringContainsString('"max_agents":"4"', $result[0]);

        $this->assertStringContainsString('"agent_id":"' .$abreton->id() .'"', $result[0]);

        $this->assertStringContainsString('"agent_name":"' .$abreton->nom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$kboivin->nom() .' ' .$kboivin->prenom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$jdevoe->nom() .' ' .$jdevoe->prenom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$jdupont->nom() .' ' .$jdupont->prenom() .'"', $result[0]);
    }


    public function testContextMenuWithMultiSite()
    {
        $GLOBALS['config']['PlanningHebdo-Agents'] = 0;
        $GLOBALS['config']['PlanningHebdo'] = 1;
        $GLOBALS['config']['ClasseParService'] = 1;
        $GLOBALS['config']['agentsIndispo'] = 0;
        $GLOBALS['config']['toutlemonde'] = 0;
        $GLOBALS['config']['Multisites-nombre'] = 2;
        $GLOBALS['config']['Multisites-site1'] = 'site';
        $GLOBALS['config']['Multisites-site2'] = 'site2';

        $builder = new FixtureBuilder();

        // Create post
        $builder->delete(Position::class);

        $post = $builder->build(Position::class, array(
            'nom' => 'administratif',
            'statistiques' => 1,
            'teleworking' => 1,
            'bloquant' => 0,
        ));
        $id = $post->id();

        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("2")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => json_encode($id), 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => json_encode(array("1")),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->id();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => json_encode($id),
            'service' => 'Pôle Public', 'sites' => json_encode(array("1")), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->droits());

        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);

        $start = \DateTime::createFromFormat("d/m/Y", "01/10/2021");
        $end = \DateTime::createFromFormat("d/m/Y", "01/12/2023");

        $workingHours = array(
            0 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
            1 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
            2 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
            3 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
            4 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
            5 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '19:30:00', '2'),
        );

        $planning = $this->builder->build(WorkingHour::class, array(
            'perso_id' => $jdevoe->id(),
            'debut' => $start,
            'fin' => $end,
            'temps' => json_encode($workingHours),
            'valide_n1' => 1,
            'valide' => 1,
            'nb_semaine' => 1
        ));

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $response = $this->client->getResponse();

        $result = explode('["callback":protected]', $response);

        $this->assertStringContainsString('"position_name":"' .$post->nom() .'"', $result[0]);

        $this->assertStringContainsString('"position_id":"' .$post->id() .'"', $result[0]);

        $this->assertStringContainsString('"date":"2022-11-01"', $result[0]);

        $this->assertStringContainsString('"start":"08:00:00"', $result[0]);

        $this->assertStringContainsString('"end":"19:30:00"', $result[0]);

        $this->assertStringContainsString('"site":"1"', $result[0]);

        $this->assertStringContainsString('"group_tab_hide":1', $result[0]);

        $this->assertStringContainsString('"nb_agents":0', $result[0]);

        $this->assertStringContainsString('"max_agents":"4"', $result[0]);

        $this->assertStringContainsString('"agent_id":"' .$abreton->id() .'"', $result[0]);

        $this->assertStringContainsString('"agent_name":"' .$abreton->nom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$kboivin->nom() .' ' .$kboivin->prenom() .'"', $result[0]);

        $this->assertStringContainsString('"name_title":"' .$jdupont->nom() .' ' .$jdupont->prenom() .'"', $result[0]);

        // Check if the agent with wrong site is int the context menu
        $this->assertStringNotContainsString('"name_title":"' .$jdevoe->nom() .' ' .$jdevoe->prenom() .'"', $result[0]);
    }
}
