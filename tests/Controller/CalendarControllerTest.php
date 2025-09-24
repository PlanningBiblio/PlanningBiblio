<?php

use App\Entity\Agent;
use App\Entity\Absence;
use App\Entity\WorkingHour;
use Symfony\Component\DomCrawler\Crawler;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class CalendarControllerTest extends PLBWebTestCase
{
    public function testCalendarWithMultiSites(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

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

        $GLOBALS['config']['Multisites-nombre'] = 4;
        $GLOBALS['config']['Multisites-site1'] = 'Site N°1';
        $GLOBALS['config']['Multisites-site2'] = 'Site N°2';
        $GLOBALS['config']['Multisites-site3'] = 'Site N°3';
        $GLOBALS['config']['Multisites-site4'] = 'Site N°4';
        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->getId(),
        ));

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence à Site N°2', $result->text('Node does not exist', false), 'Présence à Site N°2');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text('Node does not exist', false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->text('Node does not exist', false), 'de 13h15 à 17h15');

        $this->assertStringContainsString('Présence à Site N°3', $result->eq(1)->text('Node does not exist', false), 'Présence à Site N°3');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->eq(1)->text('Node does not exist', false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->eq(1)->text('Node does not exist', false), 'de 13h15 à 17h15');

        $this->assertStringContainsString('Présence sur tous les sites', $result->eq(2)->text('Node does not exist', false), 'Présence sur tous les sites');
        $this->assertStringContainsString('de 10h00 à 13h30', $result->eq(2)->text('Node does not exist', false), 'de 10h00 à 13h30');
        $this->assertStringContainsString('de 15h15 à 18h15', $result->eq(2)->text('Node does not exist', false), 'de 15h15 à 18h15');
    }

    public function testCalendarWithPlanningHebdo(): void{
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Actif'));
        $this->logInAgent($agent, array(3, 100));

        $GLOBALS['config']['PlanningHebdo'] = 1;

        $start = \DateTime::createFromFormat("d/m/Y H:i:s", '25/09/2022 08:00:00');
        $end = \DateTime::createFromFormat("d/m/Y H:i:s", '29/09/2022 19:00:00');
        $builder->build
        (
            WorkingHour::class,
            array(
                'perso_id' => $agent->getId(),
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

        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 29/09/2022 ', $result->text('Node does not exist', false), 'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text('Node does not exist', false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text('Node does not exist', false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text('Node does not exist', false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text('Node does not exist', false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text('Node does not exist', false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence', $result->text('Node does not exist', false), 'Présence');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text('Node does not exist', false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h30 à 17h00', $result->text('Node does not exist', false), 'de 13h30 à 17h00');
    }

    public function testCalendarWithAbsence(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

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
        $builder->build(Absence::class, array('debut' => $start, 'motif' => 'malade', 'fin' => $end, 'perso_id' => $agent2->getId(), 'validation' => $validation, 'valide' => 1, 'supprime' => 0, 'groupe' => '1'));

        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Doenv Jean du 26/09/2022 au 29/09/2022 ', $result->text('Node does not exist', false), 'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text('Node does not exist', false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text('Node does not exist', false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text('Node does not exist', false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text('Node does not exist', false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text('Node does not exist', false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="important"]');

        $this->assertStringContainsString('Absence', $result->eq(0)->text('Node does not exist', false), 'Absence');
        $this->assertStringContainsString('À partir de 08h00 : malade', $result->eq(0)->text('Node does not exist', false), 'À partir de 08h00 : malade');
    }

    public function testFullCalendar(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

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
        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent2->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Doenv Jean du 26/09/2022 au 29/09/2022 ', $result->text('Node does not exist', false), 'h3 is Agenda');

        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text('Node does not exist', false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text('Node does not exist', false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text('Node does not exist', false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text('Node does not exist', false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text('Node does not exist', false), 'Vendredi');

        $result = $crawler->filterXPath('//div[@class="attendance"]');
        $this->assertStringContainsString('Présence', $result->text('Node does not exist', false), 'Présence');
        $this->assertStringContainsString('de 09h00 à 12h30', $result->text('Node does not exist', false), 'de 09h00 à 12h30');
        $this->assertStringContainsString('de 13h15 à 17h15', $result->text('Node does not exist', false), 'de 13h15 à 17h15');
    }

    public function testEmptyCalendar(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Actif'));
        $this->logInAgent($agent, array(3, 100));

        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '27/09/2022',
            'perso_id' => $agent->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 27/09/2022 ', $result->text('Node does not exist', false), 'h3 is Agenda');


        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text('Node does not exist', false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text('Node does not exist', false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text('Node does not exist', false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text('Node does not exist', false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text('Node does not exist', false), 'Vendredi');

        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals(' Agenda de Devoe John du 26/09/2022 au 29/09/2022 ', $result->text('Node does not exist', false), 'h3 is Agenda');


        $result = $crawler->filterXPath('//tr[@class="center"]');
        $this->assertStringContainsString('Lundi', $result->text('Node does not exist', false), 'Lundi');
        $this->assertStringContainsString('Mardi', $result->text('Node does not exist', false), 'Mardi');
        $this->assertStringContainsString('Mercredi', $result->text('Node does not exist', false), 'Mercredi');
        $this->assertStringContainsString('Jeudi', $result->text('Node does not exist', false), 'Jeudi');
        $this->assertStringContainsString('Vendredi', $result->text('Node does not exist', false), 'Vendredi');
    }

    public function testDeletedAgentCalendar(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'actif' => 'Inactif', 'supprime' => 1));
        $this->logInAgent($agent, array(3, 100));

        $crawler = $this->client->request('GET', "/calendar", array(
            'debut' => '26/09/2022',
            'fin' => '29/09/2022',
            'perso_id' => $agent->getId(),
        ));

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals('Agenda', $result->text('Node does not exist', false), 'h3 is Agenda');
    }
}
