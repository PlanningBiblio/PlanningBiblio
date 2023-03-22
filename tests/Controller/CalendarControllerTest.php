<?php

use App\Model\Agent;
use App\Model\Absence;
use App\Model\Site;
use App\Model\WeekPlanning;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class CalendarControllerTest extends PLBWebTestCase
{
    public function testCalendarWithMultiSites()
    {
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);


        $client = static::createClient();
        $GLOBALS['config']['PlanningHebdo'] = 0;

        $agent2 = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif',
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:15:00","17:15:00","2"],
                        "1" => ["09:00:00","12:30:00","13:15:00","17:15:00","3"],
                        "2" => ["10:00:00","13:30:00","15:15:00","18:15:00","-1"],
                        "3" => ["11:00:00","14:30:00","15:15:00","18:15:00","-1"],
                        "4" => ["11:00:00","14:30:00","15:15:00","18:15:00","1"],
                    )
                ),
                'sites' => json_encode(["1", "2", "3","4"])
            )
        );

        $this->logInAgent($agent2, array(3,100));

        $site1 = new Site();
        $site1->nom('Site N°1');

        $entityManager->persist($site1);
        $entityManager->flush();

        $site2 = new Site();
        $site2->nom('Site N°2');

        $entityManager->persist($site2);
        $entityManager->flush();

        $site3 = new Site();
        $site3->nom('Site N°3');

        $entityManager->persist($site3);
        $entityManager->flush();

        $site4 = new Site();
        $site4->nom('Site N°4');

        $entityManager->persist($site4);
        $entityManager->flush();

        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->id(),
        ));

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence à Site N°2', $result->text(null, false), 'Présence à Site N°2');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text(null, false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->text(null, false), 'de 13h15 à 17h15');

        $this->assertStringContainsString('Présence à Site N°3', $result->eq(1)->text(null, false), 'Présence à Site N°3');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->eq(1)->text(null, false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->eq(1)->text(null, false), 'de 13h15 à 17h15');

        $this->assertStringContainsString('Présence sur tous les sites', $result->eq(2)->text(null, false), 'Présence sur tous les sites');
        $this->assertStringContainsString('de 10h00 à 13h30', $result->eq(2)->text(null, false), 'de 10h00 à 13h30');
        $this->assertStringContainsString('de 15h15 à 18h15', $result->eq(2)->text(null, false), 'de 15h15 à 18h15');
    }

    public function testCalendarWithPlanningHebdo(){
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Actif'));
        $this->logInAgent($agent, array(3, 100));

        $client = static::createClient();

        $GLOBALS['config']['PlanningHebdo'] = 1;

        $start = \DateTime::createFromFormat("d/m/Y H:i:s", '25/09/2022 08:00:00');
        $end = \DateTime::createFromFormat("d/m/Y H:i:s", '29/09/2022 19:00:00');
        $pl_post = $builder->build
        (
            WeekPlanning::class,
            array(
                'perso_id' => $agent->id(),
                'debut' => $start,
                'fin' => $end,
                'valide_n1' => 1,
                'valide' => 1,
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "1" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "2" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "3" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "4" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"]
                        )
                    ),
                    'nb_semaine' => 2,
            )
        );


        $client = static::createClient();
        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 29/09/2022 ', $result->text(null, false),'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text(null, false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text(null, false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text(null, false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text(null, false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text(null, false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence', $result->text(null, false), 'Présence');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text(null, false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h30 à 17h00', $result->text(null, false), 'de 13h30 à 17h00');
    }

    public function testCalendarWithAbsence()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $client = static::createClient();

        $GLOBALS['config']['PlanningHebdo'] = 0;
        $GLOBALS['config']['Absences-validation'] = 1;

        $agent2 = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif',
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:15:00","17:15:00","2"],
                        "1" => ["09:00:00","12:30:00","13:15:00","17:15:00","3"],
                        "2" => ["10:00:00","13:30:00","15:15:00","18:15:00","-1"],
                        "3" => ["11:00:00","14:30:00","15:15:00","18:15:00","-1"],
                        "4" => ["11:00:00","14:30:00","15:15:00","18:15:00","1"],
                    )
                ),
                'sites' => json_encode(["1", "2", "3","4"])
            )
        );

        $this->logInAgent($agent2, array(3,100));

        $start = \DateTime::createFromFormat("d/m/Y H:i:s", '26/09/2022 08:00:00');
        $end = \DateTime::createFromFormat("d/m/Y H:i:s", '28/09/2022 19:00:00');
        $validation = \DateTime::createFromFormat("d/m/Y H:i:s", '28/09/2022 08:00:00');
        $off = $builder->build(Absence::class, array('debut' => $start, 'motif' => 'malade', 'fin' => $end, 'perso_id' => $agent2->id(), 'validation' => $validation, 'valide' => 1, 'supprime' => 0, 'groupe' => '1'));

        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Doenv Jean du 26/09/2022 au 29/09/2022 ', $result->text(null, false),'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text(null, false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text(null, false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text(null, false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text(null, false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text(null, false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="important"]');

        $this->assertStringContainsString('Absence', $result->eq(0)->text(null, false), 'Absence');
        $this->assertStringContainsString('À partir de 08h00 : malade', $result->eq(0)->text(null, false), 'À partir de 08h00 : malade');
    }

    public function testFullCalendar()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $client = static::createClient();

        $GLOBALS['config']['PlanningHebdo'] = 0;

        $agent2 = $builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv', 'nom' => 'Doenv', 'prenom' => 'Jean', 'actif' => 'Actif',
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:15:00","17:15:00","2"],
                        "1" => ["09:00:00","12:30:00","13:15:00","17:15:00","3"],
                        "2" => ["10:00:00","13:30:00","15:15:00","18:15:00","-1"],
                        "3" => ["11:00:00","14:30:00","15:15:00","18:15:00","-1"],
                        "4" => ["11:00:00","14:30:00","15:15:00","18:15:00","1"],
                    )
                ),
                'sites' => json_encode(["1", "2", "3","4"])
            )
        );

        $this->logInAgent($agent2, array(3,100));
        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Doenv Jean du 26/09/2022 au 29/09/2022 ', $result->text(null, false),'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text(null, false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text(null, false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text(null, false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text(null, false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text(null, false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence', $result->text(null, false), 'Présence');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text(null, false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->text(null, false), 'de 13h15 à 17h15');
    }

    public function testEmptyCalendar()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Actif'));
        $this->logInAgent($agent, array(3, 100));


        $client = static::createClient();
        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '27/09/2022',
            'perso_id' => $agent->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 27/09/2022 ', $result->text(null, false),'h3 is Agenda');


        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text(null, false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text(null, false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text(null, false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text(null, false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text(null, false), 'Vendredi');

        $client = static::createClient();
        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 29/09/2022 ', $result->text(null, false),'h3 is Agenda');


        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text(null, false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text(null, false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text(null, false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text(null, false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text(null, false), 'Vendredi');
    }

    public function testDeletedAgentCalendar()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Inactif', 'supprime' => 1));
        $this->logInAgent($agent, array(3, 100));


        $client = static::createClient();
        $crawler = $client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->id(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals('Agenda', $result->text(null, false),'h3 is Agenda');
    }
}
