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

        $y = date('Y');
        $m = date('m');
        $d = date('d');
        $_SESSION['oups']['CSRFToken'] = '00000';

        $crawler = $client->request('GET', "/index", array('date' => "$y-$m-$d", 'CSRFToken' => '00000'));

        $result = $crawler->filterXPath('//div[@class="decalage-gauche"]/p');
        $this->assertEquals($result->text('Node does not exist', false),"Le planning n'est pas prêt.",'test index with no planning');

        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$y");

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

        $crawler = $client->request('GET', "/index", array('date' => "$y-$m-$d", 'CSRFToken' => '00000'));

        $result = $crawler->filterXPath('//div[@class="decalage-gauche"]/font');
        $this->assertEquals($result->text('Node does not exist', false),"Le planning du $d/$m/$y n'est pas validé !",'test index with no lock planning');
    }
}
