<?php

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\PlanningPosition;
use App\Entity\Position;
use App\Entity\WorkingHour;
use App\Planno\WorkingHours;
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


    public function createWeekPlanningFor($agent): void
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
            'perso_id' => $agent->getId(),
            'debut' => $start,
            'fin' => $end,
            'temps' => $workingHours,
            'valide_n1' => 0,
            'valide' => 1,
            'nb_semaine' => 1
        ));
    }


    public function testContextMenuAgentsDispo(): void
    {
        global $entityManager;

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
        $id = $post->getId();

        // Create agents
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->getId();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => [$id],
            'service' => 'Pôle Public', 'sites' => array("1"), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => [99, 100, 301],
        ));

        $jdoe = new Agent();
        $jdoe->setLogin('jdoe');
        $jdoe->setLastname('Doe');
        $jdoe->setFirstname('John');
        $jdoe->setSkills([$id]);
        $jdoe->setService('Pôle Public');
        $jdoe->setSites([1]);
        $jdoe->setACL([99, 100, 301]);
        $entityManager->persist($jdoe);

        $mgeorges = new Agent();
        $mgeorges->setLogin('mgeorges');
        $mgeorges->setLastname('Georges');
        $mgeorges->setFirstname('Marie');
        $mgeorges->setSkills([$id]);
        $mgeorges->setService('Pôle Public');
        $mgeorges->setSites([1]);
        $mgeorges->setACL([99, 100, 301]);
        $mgeorges->setArrival(\DateTime::createFromFormat('Y-m-d', '2022-11-02'));
        $entityManager->persist($mgeorges);

        $emartin = new Agent();
        $emartin->setLogin('emartin');
        $emartin->setLastname('Martin');
        $emartin->setFirstname('Eric');
        $emartin->setSkills([$id]);
        $emartin->setService('Pôle Public');
        $emartin->setSites([1]);
        $emartin->setDeparture(\DateTime::createFromFormat('Y-m-d', '2022-10-31'));
        $entityManager->persist($emartin);

        $jmarc = new Agent();
        $jmarc->setLogin('jmarc');
        $jmarc->setLastname('Marc');
        $jmarc->setFirstname('Jeremy');
        $jmarc->setSkills([$id]);
        $jmarc->setService('Pôle Public');
        $jmarc->setSites([1]);
        $jmarc->setDeparture(\DateTime::createFromFormat('Y-m-d', '2022-11-01'));
        $entityManager->persist($jmarc);

        $bmarley = new Agent();
        $bmarley->setLogin('bmarley');
        $bmarley->setLastname('Marley');
        $bmarley->setFirstname('Bob');
        $bmarley->setSkills([$id]);
        $bmarley->setService('Pôle Public');
        $bmarley->setSites([1]);
        $bmarley->setArrival(\DateTime::createFromFormat('Y-m-d', '2022-11-01'));
        $entityManager->persist($bmarley);

        $entityManager->flush();

        $this->logInAgent($kboivin, $kboivin->getACL());
        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);
        $this->createWeekPlanningFor($jdupont);
        $this->createWeekPlanningFor($jdoe);
        $this->createWeekPlanningFor($mgeorges);
        $this->createWeekPlanningFor($emartin);
        $this->createWeekPlanningFor($jmarc);
        $this->createWeekPlanningFor($bmarley);

        // Create Absence
        $builder->delete(Absence::class);

        $start = \DateTime::createFromFormat("d/m/Y", "31/10/2022");
        $end = \DateTime::createFromFormat("d/m/Y", "02/11/2022");

        $jdupont_off = $this->builder->build(Absence::class, array(
            'debut' => $start,
            'fin' => $end,
            'perso_id' => $jdupont->getId(),
            'valide_n1' => 1,
            'valide' =>1,
            'supprime' => 0,
            'groupe' => 1
        ));

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $json = $this->client->getResponse()->getContent();
        $contextmenu = json_decode($json, true);

        $this->assertSame($post->getName(), $contextmenu['position_name'], 'Position name');
        $this->assertSame((string) $post->getId(), $contextmenu['position_id'], 'Position ID');
        $this->assertSame('2022-11-01', $contextmenu['date'], 'Date is 2022-11-01');
        $this->assertSame('08:00:00', $contextmenu['start'], 'Start is 08:00:00');
        $this->assertSame('19:30:00', $contextmenu['end'], 'End is 19:30:00');
        $this->assertSame('1', $contextmenu['site'], 'Site number is 1');
        $this->assertSame(0, $contextmenu['group_tab_hide'], 'GroupTaHide:0');
        $this->assertSame(0, $contextmenu['nb_agents'], 'nb_agents:0');
        $this->assertSame('4', $contextmenu['max_agents'], 'max_agents:4');
        $this->assertSame((string) $abreton->getId(), $contextmenu['agent_id'], 'agent_id contains abreton.id');
        $this->assertSame($abreton->getLastname(), $contextmenu['agent_name'], 'agent_name contains abreton.lastname');

        $this->assertCount(6, $contextmenu['menu1']['agents'], '6 available agents');
        $this->assertSame(
            $kboivin->getLastname() . ' ' . $kboivin->getFirstname(),
            $contextmenu['menu1']['agents'][0]['name_title'],
            'name_title contains kboivin info'
        );
        $this->assertSame(
            $abreton->getLastname() . ' ' . $abreton->getFirstname(),
            $contextmenu['menu1']['agents'][1]['name_title'],
            'name_title contains abreton info'
        );
        $this->assertSame(
            $jdevoe->getLastname() . ' ' . $jdevoe->getFirstname(),
            $contextmenu['menu1']['agents'][2]['name_title'],
            'name_title contains jdevoe info'
        );
        $this->assertSame(
            $jdoe->getLastname() . ' ' . $jdoe->getFirstname(),
            $contextmenu['menu1']['agents'][3]['name_title'],
            'name_title contains jdoe info'
        );
        $this->assertSame(
            $jmarc->getLastname() . ' ' . $jmarc->getFirstname(),
            $contextmenu['menu1']['agents'][4]['name_title'],
            'name_title contains jmarc info'
        );
        $this->assertSame(
            $bmarley->getLastname() . ' ' . $bmarley->getFirstname(),
            $contextmenu['menu1']['agents'][5]['name_title'],
            'name_title contains bmarley info'
        );

        $this->assertCount(0, $contextmenu['menu2'], 'no unavailable agents');
    }


    public function testContextMenuWithAgentsIndispo(): void
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
        $id = $post->getId();


        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->getId();
        $agentHoliday = $this->builder->build(Agent::class, array(
            'login' => 'holiday', 'nom' => 'Day', 'prenom' => 'Holy', 'postes' => [$id],
            'service' => 'Pôle Public', 'sites' => array("1"), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array(99,100)
        ));

        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => [$id],
            'service' => 'Pôle Public', 'sites' => array("1"), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->getACL());

        // Create Holiday
        $builder->delete(Holiday::class);

        $start = \DateTime::createFromFormat("d/m/Y", "18/10/2022");
        $end = \DateTime::createFromFormat("d/m/Y", "04/11/2022");

        $holiday = $builder->build(Holiday::class, array(
            'debut' => $start,
            'fin' => $end,
            'perso_id' => $agentHoliday->getId(),
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

        $json = $this->client->getResponse()->getContent();
        $contextmenu = json_decode($json, true);

        $this->assertSame($post->getName(), $contextmenu['position_name']);
        $this->assertSame((string) $post->getId(), $contextmenu['position_id']);
        $this->assertSame('2022-11-01', $contextmenu['date']);
        $this->assertSame('08:00:00', $contextmenu['start']);
        $this->assertSame('19:30:00', $contextmenu['end']);
        $this->assertSame('1', $contextmenu['site']);
        $this->assertSame(0, $contextmenu['group_tab_hide']);
        $this->assertSame(0, $contextmenu['nb_agents']);
        $this->assertSame('4', $contextmenu['max_agents']);
        $this->assertSame((string) $abreton->getId(), $contextmenu['agent_id']);
        $this->assertSame($abreton->getLastname(), $contextmenu['agent_name']);
        $this->assertSame(
            $kboivin->getLastname() . ' ' . $kboivin->getFirstname(),
            $contextmenu['menu1']['agents'][0]['name_title'],
        );
        $this->assertSame(
            $jdevoe->getLastname() . ' ' . $jdevoe->getFirstname(),
            $contextmenu['menu1']['agents'][2]['name_title'],
        );

        $this->assertSame($agentHoliday->getId(), $contextmenu['menu2']['agents'][0]['id']);
        $this->assertSame($agentHoliday->getLastname(), $contextmenu['menu2']['agents'][0]['nom']);
        $this->assertSame($agentHoliday->getFirstname(), $contextmenu['menu2']['agents'][0]['prenom']);
    }


    public function testContextMenuWithClasseParService(): void
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
        $id = $post->getId();


        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->getId();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => [$id],
            'service' => 'Pôle Public', 'sites' => array("1"), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->getACL());

        // Create WeekPlanning
        $builder->delete(WorkingHour::class);

        $this->createWeekPlanningFor($jdevoe);
        $this->createWeekPlanningFor($abreton);
        $this->createWeekPlanningFor($kboivin);

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $json = $this->client->getResponse()->getContent();
        $contextmenu = json_decode($json, true);

        $this->assertSame($post->getName(), $contextmenu['position_name']);
        $this->assertSame((string) $post->getId(), $contextmenu['position_id']);
        $this->assertSame('2022-11-01', $contextmenu['date']);
        $this->assertSame('08:00:00', $contextmenu['start']);
        $this->assertSame('19:30:00', $contextmenu['end']);
        $this->assertSame('1', $contextmenu['site']);
        $this->assertSame(1, $contextmenu['group_tab_hide']);
        $this->assertSame(0, $contextmenu['nb_agents']);
        $this->assertSame('4', $contextmenu['max_agents']);
        $this->assertSame((string) $abreton->getId(), $contextmenu['agent_id']);
        $this->assertSame($abreton->getLastname(), $contextmenu['agent_name']);

        $this->assertCount(0, $contextmenu['menu1']);

        $this->assertArrayHasKey('agents', $contextmenu['menu2']);
        $this->assertCount(5, $contextmenu['menu2']['agents']);
        $this->assertSame(
            $kboivin->getLastname() . ' ' . $kboivin->getFirstname(),
            $contextmenu['menu2']['agents'][0]['name_title'],
        );
        $this->assertSame(
            $abreton->getLastname() . ' ' . $abreton->getFirstname(),
            $contextmenu['menu2']['agents'][1]['name_title'],
        );
        $this->assertSame(
            $jdevoe->getLastname() . ' ' . $jdevoe->getFirstname(),
            $contextmenu['menu2']['agents'][2]['name_title'],
        );
        $this->assertSame(
            $jdupont->getLastname() . ' ' . $jdupont->getFirstname(),
            $contextmenu['menu2']['agents'][3]['name_title'],
        );
        $this->assertSame(
            'Tout le monde ',
            $contextmenu['menu2']['agents'][4]['name_title'],
        );
    }


    public function testContextMenuWithMultiSite(): void
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
        $id = $post->getId();

        // Create agent
        $arrivee = \DateTime::createFromFormat("d/m/Y", "01/10/2022");
        $depart = new DateTime('+ 1 year');

        $builder->delete(Agent::class);
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("2"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Pôle Public', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'postes' => [$id], 'actif' =>'Actif',
            'droits' => array(99,100), 'service' => 'Accueil', 'sites' => array("1"),
            'arrivee' => $arrivee, 'depart' => $depart,
        ));
        $ida = $abreton->getId();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'postes' => [$id],
            'service' => 'Pôle Public', 'sites' => array("1"), 'actif' =>'Actif',
            'arrivee' => $arrivee, 'depart' => $depart,
            'droits' => array("6","9","701","3","4","21","1101","1201","22","5","17","1301","25","23","201","202","203","204","401","402","403","404","601","602","603","604","301","302","303","304","1001","1002","1003","1004","901","902","903","904","801","802","803","804",6,99,100,20)
        ));

        $this->logInAgent($kboivin, $kboivin->getACL());

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
            'perso_id' => $jdevoe->getId(),
            'debut' => $start,
            'fin' => $end,
            'temps' => $workingHours,
            'valide_n1' => 1,
            'valide' => 1,
            'nb_semaine' => 1
        ));

        $crawler = $this->client->request('GET', "/planningjob/contextmenu?CSRFToken={$this->CSRFToken}&cellule=84&date=2022-11-01&debut=08%3A00%3A00&fin=19%3A30%3A00&perso_id=$ida&site=1&poste=$id&perso_nom=Breton");

        $json = $this->client->getResponse()->getContent();
        $contextmenu = json_decode($json, true);

        $this->assertSame($post->getName(), $contextmenu['position_name']);
        $this->assertSame((string) $post->getId(), $contextmenu['position_id']);
        $this->assertSame('2022-11-01', $contextmenu['date']);
        $this->assertSame('08:00:00', $contextmenu['start']);
        $this->assertSame('19:30:00', $contextmenu['end']);
        $this->assertSame('1', $contextmenu['site']);
        $this->assertSame(1, $contextmenu['group_tab_hide']);
        $this->assertSame(0, $contextmenu['nb_agents']);
        $this->assertSame('4', $contextmenu['max_agents']);
        $this->assertSame((string) $abreton->getId(), $contextmenu['agent_id']);
        $this->assertSame($abreton->getLastname(), $contextmenu['agent_name']);

        $this->assertCount(0, $contextmenu['menu1']);

        $this->assertArrayHasKey('agents', $contextmenu['menu2']);
        $this->assertCount(3, $contextmenu['menu2']['agents']);
        $this->assertSame(
            $kboivin->getLastname() . ' ' . $kboivin->getFirstname(),
            $contextmenu['menu2']['agents'][0]['name_title'],
        );
        $this->assertSame(
            $abreton->getLastname() . ' ' . $abreton->getFirstname(),
            $contextmenu['menu2']['agents'][1]['name_title'],
        );
        $this->assertSame(
            $jdupont->getLastname() . ' ' . $jdupont->getFirstname(),
            $contextmenu['menu2']['agents'][2]['name_title'],
        );
    }
}
