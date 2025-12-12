<?php

use App\Model\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AbsenceControllerAbsenceStatusesTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $GLOBALS['config']['Absences-validation'] = 1;
    }

    public function testNewAbsenceWithoutRight(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Demandée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');
    }

    public function testNewAbsenceRightN1(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testNewAbsenceRightN1AndN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testNewAbsenceRightN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testNewAbsenceRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('Absences-Validation-N2', 1);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Demandée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');
    }

    public function testEditAskedAbsenceWithoutRight(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $absence_id = $this->createAbsenceFor($loggedin, 0);

        $agent_id = $loggedin->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Demandée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');
    }

    public function testEditAskedAbsenceRightN1(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 0);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testEditAskedAbsenceRightN1AndN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 0);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditAskedAbsenceRightN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 0);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditAskedAbsenceRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('Absences-Validation-N2', 1);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 0);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Demandée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');

    }

    public function testEditN1AbsenceWithoutRight(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $absence_id = $this->createAbsenceFor($loggedin, 2);

        $agent_id = $loggedin->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');
    }

    public function testEditN1AbsenceRightN1(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 2);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(3, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());

    }

    public function testEditN1AbsenceRightN1AndN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 2);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditN1AbsenceRightN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 2);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditN1AbsenceRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('Absences-Validation-N2', 1);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 2);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditN2AbsenceWithoutRight(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100)
        ));

        $absence_id = $this->createAbsenceFor($loggedin, 1);

        $agent_id = $loggedin->id();

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Acceptée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');
    }

    public function testEditN2AbsenceRightN1(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, -1);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Refusée', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');

    }

    public function testEditN2AbsenceRightN1AndN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 1);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditN2AbsenceRightN2(): void
    {
        $this->setParam('Absences-Validation-N2', 0);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, 1);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    public function testEditN2AbsenceRightN2WithAbsencesValidationN2(): void
    {
        $this->setParam('Absences-Validation-N2', 1);

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createAbsenceFor($jdevoe, -1);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $this->client->request('GET', "/absence-statuses?ids[]=$agent_id&module=absence&id=$absence_id");

        $statuses_elements = $crawler->filter('select option');

        $this->assertCount(5, $statuses_elements);
        $this->assertEquals('Demandée', $statuses_elements->eq(0)->html());
        $this->assertEquals('Acceptée (En attente de validation hiérarchique)', $statuses_elements->eq(1)->html());
        $this->assertEquals('Refusée (En attente de validation hiérarchique)', $statuses_elements->eq(2)->html());
        $this->assertEquals('Acceptée', $statuses_elements->eq(3)->html());
        $this->assertEquals('Refusée', $statuses_elements->eq(4)->html());

    }

    private function createAbsenceFor($agent, $status = 0)
    {
        $date = new DateTime('now + 3 day');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->id());
        $absence->commentaires = '';
        $absence->motif = 'AbsenceControllerAbsenceStatusesTest';
        $absence->valide = $status;
        $absence->CSRFToken = $this->CSRFToken;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';

        $absence->add();

        return $absence->id;
    }
}
