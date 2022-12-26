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

    public function testListClosingDay()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $_SESSION['oups']['CSRFToken'] = '00000';
        $GLOBALS['config']['Multisites-nombre'] = 1;

        $client = static::createClient();

        $agent = $builder->build(
            Agent::class,
            array(
                'login' => 'agent_test',
            )
        );
        $this->logInAgent($agent, array(99, 25, 100));

        $d1 = date("d")+3;
        $d2 = date("d")+6;
        $m = date("m");
        $Y = date("Y");
        if(date('n')<9){
            $Y1 = $Y-1;
            $Y2 = $Y+1;
        } else{
            $Y1 = $Y+1;
            $Y2 = $Y+2;
        }

        $date1 = \DateTime::createFromFormat("d/m/Y", "$d1/$m/$Y");
        $date2 = \DateTime::createFromFormat("d/m/Y", "$d2/$m/$Y");
        if(date('n')<9){
            $annee = "$Y1-$Y";
        } else{
            $annee = "$Y-$Y1";
        }

        if(date('n')<9){
            $annee2 = "$Y-$Y2";
        } else{
            $annee2 = "$Y1-$Y2";
        }

        $builder->delete(PublicHoliday::class);

        $public_holiday_1 = $builder->build(
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
        $public_holiday_2 = $builder->build(
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
        $this->assertEquals($result->text(null,false),"Jours fériés et jours de fermeture");

        $result = $crawler->filterXPath('//form[@name="form1"]');
        $this->assertStringContainsString("Sélectionnez l'année à paramétrer",$result->text(null,false));
        $this->assertStringContainsString($annee,$result->text(null,false));
        $this->assertStringContainsString($annee2,$result->text(null,false));

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