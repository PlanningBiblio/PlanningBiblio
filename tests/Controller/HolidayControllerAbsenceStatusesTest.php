<?php

use App\Model\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

require_once(__DIR__ . '/../../public/conges/class.conges.php');

class HolidayControllerAbsenceStatusesTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $GLOBALS['config']['Conges-validation'] = 1;
    }

    public function testEditN2AbsenceRightN1AndN2(): void
    {
        $this->setParam('Conges-Validation-N2', 0);

        $client = static::createClient();

        $loggedin = $this->builder->build(Agent::class, array(
            'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
            'droits' => array(99,100, 201, 501)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $agent_id = $jdevoe->id();

        $absence_id = $this->createHolidayFor($jdevoe);

        // request /absence-statuses
        $this->logInAgent($loggedin, $loggedin->droits());
        $crawler = $client->request('GET', "/absence-statuses?ids[]=$agent_id&module=holiday&id=$absence_id");

        $statuses_element = $crawler->filter('span');

        $this->assertEquals('Accepté', $statuses_element->html(), 'NewAbsenceWithoutRight show asked');

    }

    private function createHolidayFor($agent)
    {
        $date = new DateTime('now + 3 day');

        $data = array(
            'debut'         => $date->format('d/m/Y'),
            'fin'           => $date->format('d/m/Y'),
            'hre_debut'     => '',
            'hre_fin'       => '',
            'commentaires'  => 'No comment',
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
}
