<?php

use App\Entity\Agent;
use App\Entity\Absence;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionTabAffectation;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class PlanningControllerTest extends PLBWebTestCase
{
    public function testPlanningNotReadyWithoutPermission(): void
    {
        $this->builder->delete(Agent::class);
        $this->builder->delete(PlanningPosition::class);
        $this->builder->delete(PlanningPositionTabAffectation::class);

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
            )
        );

        $this->logInAgent($agent, array(99,100));

        $date = new \DateTime();
        $today = date('d/m/Y');

        $crawler = $this->client->request('GET', '/');
        $result = $crawler->filter('.decalage-gauche p');
        $this->assertEquals("Le planning du $today n'est pas prêt.", $result->text('Node does not exist', false));

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

        $crawler = $this->client->request('GET', '/');
        $result = $crawler->filter('.decalage-gauche p');
        $this->assertEquals("Le planning du $today n'est pas validé !", $result->text('Node does not exist', false), 'test index with no lock planning');
    }
}
