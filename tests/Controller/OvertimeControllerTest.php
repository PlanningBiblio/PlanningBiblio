
<?php

use App\Model\Agent;
use App\Model\OverTime;
use App\Model\Holiday;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;


class OvertimeControllerTest extends PLBWebTestCase
{

    public function testSave()
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(OverTime::class);


        $this->logInAgent($agent, array(100));

        $_SESSION['oups']['CSRFToken'] = '00000';

        $date = new DateTime('+1 day');

        $overTime = new OverTime();
        $overTime->date($date);
        $overTime->perso_id($agent->id());
        $overTime->heures('0.5');
        $overTime->commentaires('heures supp');
        $overTime->saisie_par($agent->id());
        $overTime->modif('0');
        $overTime->valide_n1('0');
        $overTime->valide('0');

        $entityManager->persist($overTime);
        $entityManager->flush();

        $this->client->request(
            'POST',
            '/overtime',
            array(
                'id' => $overTime->id(),
                'heures' => '01:30',
                'commentaires' => 'ploup',
                'CSRFToken' => '00000'
            )
        );

        $overTime2 = $entityManager->getRepository(OverTime::class)->findOneBy(array('commentaires' => 'ploup'));
        $this->assertEquals('0.5', $overTime2->heures(), 'heures is 0.5');
        $this->assertEquals('heures supp', $overTime2->commentaires(), 'Commentaire is heures supp');
    }

    public function testIndex()
    {
        $entityManager = $this->entityManager;
        date_default_timezone_set('UTC');

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'John'));
        $this->logInAgent($agent, array(100));

        $crawler = $this->client->request('GET', "/overtime?annee=2022&perso_id=" .$agent->id());

        $result = $crawler->filterXPath('//h3[@class="noprint"]');
        $this->assertEquals('Heures supplémentaires', $result->text('Node does not exist', false), 'h3 is Heures supplémentaires');

        $result = $crawler->filterXPath('//h4[@class="noprint"]');
        $this->assertEquals('Liste des demandes d\'heures supplémentaires', $result->text('Node does not exist', false), 'h4 is Liste des demandes de récupération');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals('Rechercher', $result->attr("value"),'input value is Rechercher');

        $result = $crawler->filterXPath('//input[@class="ui-button ui-button-type2"]');
        $this->assertEquals('Réinitialiser', $result->attr("value"),'input value is Réinitialiser');

        $result = $crawler->filterXPath('//th');
        $this->assertEquals('Heures', $result->eq(2)->text('Node does not exist', false), 'Heures is table title');
        $this->assertEquals('Validation', $result->eq(3)->text('Node does not exist', false), 'Validation is table title');
        $this->assertEquals('Crédits', $result->eq(4)->text('Node does not exist', false), 'Crédits is table title');
        $this->assertEquals('Commentaires', $result->eq(5)->text('Node does not exist', false), 'Commentaires is table title');

        $result = $crawler->filterXPath('//button[@id="dialog-button"]');
        $this->assertEquals('Nouvelle demande', $result->text('Node does not exist', false), 'button text is Nouvelle demande');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEmpty($result,'span logo edit title doesnt exist');

        $y = date("Y");
        $date = new DateTime('+1 day');

        $overTime = new OverTime();
        $overTime->date($date);
        $overTime->perso_id($agent->id());
        $overTime->heures('0.5');
        $overTime->commentaires('heures supp');
        $overTime->saisie_par($agent->id());
        $overTime->modif('0');
        $overTime->valide_n1('0');
        $overTime->valide('1');

        $entityManager->persist($overTime);
        $entityManager->flush();

        if (date('m') < 9) {
            $y = $y -1;
        }

        $crawler = $this->client->request('GET', '/overtime', array(
            'annee' => "$y",
            'perso_id' => $agent->id(),
        ));

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','span logo edit title is Modifier');

        $result = $crawler->filterXPath('//tbody');
        $this->assertStringContainsString('heures supp', $result->text('Node does not exist', false), 'Commentaires is heures supp');
        $this->assertStringContainsString($date->format('d/m/Y'), $result->text('Node does not exist', false), 'Date is ok');
        $this->assertStringContainsString('0h30', $result->text('Node does not exist', false), 'heures is 0h30');

        $y2 = $y - 1;
        $crawler = $this->client->request('GET', "/overtime?annee=$y2&perso_id=" .$agent->id());

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEmpty($result,'span logo edit title doesnt exist');

        $result = $crawler->filterXPath('//h3[@class="noprint"]');
        $this->assertEquals('Heures supplémentaires', $result->text('Node does not exist', false), 'h3 is Récupérations');

        $agent_no_overtime = $builder->build(Agent::class, array('login' => 'jover'));
        $this->logInAgent($agent_no_overtime, array(100));

        $crawler = $this->client->request('GET', "/overtime?annee=2022&perso_id=" .$agent_no_overtime->id());

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEmpty($result,'span logo edit title doesnt exist');

        $result = $crawler->filterXPath('//h3[@class="noprint"]');
        $this->assertEquals('Heures supplémentaires', $result->text('Node does not exist', false), 'h3 is Récupérations');

        $overTime2 = new OverTime();
        $overTime2->date($date);
        $overTime2->perso_id($agent_no_overtime->id());
        $overTime2->heures('1.5');
        $overTime2->commentaires('plop');
        $overTime2->saisie_par($agent_no_overtime->id());
        $overTime2->modif('0');
        $overTime2->valide_n1('0');
        $overTime2->valide('1');

        $entityManager->persist($overTime2);
        $entityManager->flush();

        $crawler = $this->client->request('GET', "/overtime?annee=$y&perso_id=" .$agent_no_overtime->id());

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','span logo edit title is Modifier');

        $result = $crawler->filterXPath('//tbody');
        $this->assertStringContainsString('plop', $result->text('Node does not exist', false), 'Commentaires is plop');
        $this->assertStringContainsString($date->format('d/m/Y'), $result->text('Node does not exist', false), 'Date is ok');
        $this->assertStringContainsString('1h30', $result->text('Node does not exist', false), 'heures is 1h30');
    }

    public function testEdit()
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'J'));
        $builder->delete(OverTime::class);


        $this->logInAgent($agent, array(100));

        $start = \DateTime::createFromFormat("d/m/Y", '05/10/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '10/10/2022');

        $date = new DateTime('+1 day');

        $overTime = new OverTime();
        $overTime->date($date);
        $overTime->perso_id($agent->id());
        $overTime->heures('0.5');
        $overTime->commentaires('heures supp');
        $overTime->saisie_par($agent->id());
        $overTime->modif('0');
        $overTime->valide_n1('0');
        $overTime->valide('1');

        $entityManager->persist($overTime);
        $entityManager->flush();

        $id = $overTime->id();

        $crawler = $this->client->request('GET', "/overtime/$id");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals('Heures supplémentaires', $result->text('Node does not exist', false), 'h3 is Heures supplémentaires');

        $result = $crawler->filterXPath('//td[@class="textAlignRight"]');
        $this->assertEquals('Agent : ', $result->text('Node does not exist', false), 'table index is Agent');
        $this->assertEquals('Date concernée : ', $result->eq(1)->text('Node does not exist', false), 'table index is Date concernée');
        $this->assertEquals('Date de la demande : ', $result->eq(2)->text('Node does not exist', false), 'table index is Date de la demande');
        $this->assertEquals('Heures demandées : ', $result->eq(3)->text('Node does not exist', false), 'table index is Heures demandées');
        $this->assertEquals('Commentaires : ', $result->eq(4)->text('Node does not exist', false), 'table index is Commentaires');
        $this->assertEquals('Validation : ', $result->eq(5)->text('Node does not exist', false), 'table index is Validation');

        $result = $crawler->filterXPath('//table[@class="tableauFiches"]');
        $this->assertStringContainsString('J Devoe', $result->text('Node does not exist', false), 'Agent name is J Devoe');
        $this->assertStringContainsString('0h30', $result->text('Node does not exist', false), 'Heures is 0h30');
        $this->assertStringContainsString('heures supp', $result->text('Node does not exist', false), 'Commentaires is heures supp');
    }
}
