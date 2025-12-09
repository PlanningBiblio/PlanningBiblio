<?php

use App\Model\ConfigParam;
use App\Model\Agent;
use App\Model\PublicHoliday;

use App\PlanningBiblio\ClosingDay;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ClosingDayControllerTest extends PLBWebTestCase
{

    public function testListClosingDay(): void
    {
        $this->builder->delete(Agent::class);

        $client = static::createClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'agent_test',
            )
        );
        $this->logInAgent($agent, array(99, 25, 100));

        $date1 = new DateTime('+3 days');
        $date2 = new DateTime('+6 days');

        $y = date("Y");
        if(date('n')<9){
            $y1 = $y-1;
            $y2 = $y+1;
            $annee = "$y1-$y";
            $annee2 = "$y-$y2";
        } else{
            $y1 = $y+1;
            $y2 = $y+2;
            $annee = "$y-$y1";
            $annee2 = "$y1-$y2";
        }

        $this->builder->delete(PublicHoliday::class);

        $public_holiday_1 = $this->builder->build(
            PublicHoliday::class,
            array(
                'annee' => $annee,
                'jour' => $date1,
                'nom' => 'public_holiday_1',
                'fermeture' => '0',
                'ferie' => '1',
                'commentaire' => 'test closing day'
            )
        );
        $id1 = $public_holiday_1->id();
        $public_holiday_2 = $this->builder->build(
            PublicHoliday::class,
            array(
                'annee' => $annee,
                'jour' => $date2,
                'nom' => 'public_holiday_2',
                'fermeture' => '0',
                'ferie' => '1',
                'commentaire' => 'test closing day 2'
            )
        );
        $id2 = $public_holiday_2->id();

        $crawler = $client->request('GET', "/closingday");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text('Node does not exist', false),"Jours fériés et jours de fermeture");

        $result = $crawler->filterXPath('//form[@name="form1"]');
        $this->assertStringContainsString("Sélectionnez l'année à paramétrer",$result->text('Node does not exist', false));
        $this->assertStringContainsString($annee,$result->text('Node does not exist', false));
        $this->assertStringContainsString($annee2,$result->text('Node does not exist', false));

        $result = $crawler->filterXPath("//input[@value='public_holiday_1']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='public_holiday_2']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='test closing day']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='test closing day 2']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='Valider']");
        $this->assertNotEmpty($result);

    }
}
