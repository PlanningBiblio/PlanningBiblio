<?php

use App\Model\Agent;
use App\Model\Absence;
use App\Model\PlanningPositionLock;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionTabAffectation;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class PlanningControllerTest extends PLBWebTestCase
{
    public function testPlanningNotReadyWithoutPermission()
    {
        $this->builder->delete(Agent::class);
        $this->builder->delete(PlanningPosition::class);
        $this->builder->delete(PlanningPositionTabAffectation::class);

        $client = static::createClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
            )
        );

        $this->logInAgent($agent, array(99,100));

        $crawler = $this->client->request('GET', '/');

        $result = $crawler->filter('.decalage-gauche p');
        $date = date('d/m/Y');
        $this->assertEquals("Le planning du $date n'est pas prêt.", $result->text('Node does not exist', false));

        $date = new \DateTime();
        $today = $date->format('d/m/Y');

        $pl_post_lock = $this->builder->build
        (
            PlanningPositionLock::class,
            array(
                'date' => $date,
                'tableau' => 1,
                'verrou' => 0,
                'verrou2' => 0,
                'site' => 1,
                'perso2' => 0,
            )
        );

        $pl_post_tab_affect = $this->builder->build
        (
            PlanningPositionTabAffectation::class,
            array(
                'date' => $date,
                'tableau' => 1,
                'site' => 1,
            )
        );

        $this->logInAgent($agent, array(99,100));

        $crawler = $client->request('GET', '/');

        $result = $crawler->filter('.decalage-gauche p');
        $this->assertEquals("Le planning du $today n'est pas validé !", $result->text('Node does not exist', false), 'test index with no lock planning');
    }
}
