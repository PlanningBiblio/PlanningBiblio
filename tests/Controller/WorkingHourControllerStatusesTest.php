<?php

use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class WorkingHourControllerStatusesTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $this->setParam('PlanningHebdo-Agents', 1);
    }

    public function testNewWorkinghoursWithoutRight(): void
    {
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/add/$agent_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Demandé', $statuses_element->html(), 'NewWorkinghoursWithoutRight show asked');
    }

    public function testNewWorkinghourqRightN1(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/add/$agent_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testNewWorkinghourRightN1AndN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/add/$agent_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testNewWorkinghourRightN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/add/$agent_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testNewWorkinghoursRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 1);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/add/$agent_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Demandé', $statuses_element->html());
    }

    public function testEditAskedWorkinghourWithoutRight(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $wh_id = $this->createWorkinghoursFor($loggedin, 0);

        $agent_id = $loggedin->getId();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Demandé', $statuses_element->html());
    }

    public function testEditAskedWorkinghourRightN1(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 0);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testEditAskedWorkinghourRightN1AndN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 0);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditAskedWorkinghourRightN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 0);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditAskedWorkinghourRightN2WithPlanningHebdoValidationN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 1);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 0);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Demandé', $statuses_element->html());

    }

    public function testEditN1WorkinghoursWithoutRight(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $wh_id = $this->createWorkinghoursFor($loggedin, 1);

        $agent_id = $loggedin->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_element->html());
    }

    public function testEditN1WorkinghoursRightN1(): void
    {
        $this->setParam('PlanningHebdo-Validation-N2', 0);
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 1);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testEditN1WorkinghoursRightN1AndN2(): void
    {        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 1);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditN1WorkinghourRightN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 1);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditN1WorkinghourRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 1);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 1);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditN2WorkinghourWithoutRight(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $wh_id = $this->createWorkinghoursFor($loggedin, 2);

        $agent_id = $loggedin->getId();

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Accepté', $statuses_element->html());
    }

    public function testEditN2WorkinghourRightN1(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 2);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_element = $crawler->filter('span#validation');

        $this->assertEquals('Accepté', $statuses_element->html());

    }

    public function testEditN2WorkingRightN1AndN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1101, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 2);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditN2WorkinghourRightN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 2);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    public function testEditN2WorkinghoursRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $this->setParam('PlanningHebdo-Validation-N2', 1);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 1201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->getId();

        $wh_id = $this->createWorkinghoursFor($jdevoe, 2);

        $this->logInAgent($loggedin, $loggedin->getACL());
        $crawler = $client->request('GET', "workinghour/$wh_id");

        $statuses_elements = $crawler->filter('select#validation option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandé', $statuses_elements->eq(0)->html());
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Accepté', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusé', $statuses_elements->eq(4)->html());

    }

    private function createWorkinghoursFor($agent, $status = 0)
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',0);
        $workinghours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            4 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            5 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
        );

        $start = new DateTime('now - 3 day');
        $end = new DateTime('now + 3 day');

        $data = array(
            'perso_id' => $agent->getId(),
            'debut' => $start->format('Y-m-d'),
            'fin' => $end->format('Y-m-d'),
            'temps' => json_encode($workinghours),
            'valide_n1' => 0,
            'valide' => 0,
            'nb_semaine' => 1
        );

        if ($status == 1) {
            $data['valide_n1'] = 1;
        }

        if ($status == 2) {
            $data['valide'] = 1;
        }

        $db = new \db();
        $db->CSRFToken = $this->CSRFToken;

        return $db->insert('planning_hebdo', $data);
    }
}
