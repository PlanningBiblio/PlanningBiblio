<?php

use App\Model\Agent;
use App\Model\HolidayInfo;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayInfoControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(HolidayInfo::class);


        $this->logInAgent($agent, array(100,401,601));

        $start = \DateTime::createFromFormat("d/m/Y", '05/10/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '10/10/2022');

        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->client->request('POST', '/holiday-info', array('debut' => '05/10/2022 00:00:00', 'fin' => '10/10/2022 00:00:00', 'texte' => 'salut', 'CSRFToken' => '00000'));
        $info = $entityManager->getRepository(HolidayInfo::class)->findOneBy(array('texte' => 'salut'));

        $this->assertEquals('2022-10-05', $info->debut()->format('Y-m-d'), "debut is ok");
        $this->assertEquals('2022-10-10', $info->fin()->format('Y-m-d'), "fin is ok");
        $this->assertEquals('salut', $info->texte(), 'info texte is salut');

    }



    public function testNewForm()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(100,401,601));

        $crawler = $this->client->request('GET', '/holiday-info/add');

        $this->assertSelectorTextContains('h3', 'Informations sur les congés');
        $this->assertSelectorTextContains('h4', 'Ajout d\'une information');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début : ','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin : ','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte : ','label 3 is Texte');

        $result = $crawler->filterXPath('//input[@class="datepicker"]');
        $this->assertEquals($result->eq(0)->attr('name'),'debut','input datepicker name is start');
        $this->assertEquals($result->eq(1)->attr('name'),'fin','input datepicker name is end');

        $result = $crawler->filterXPath('//textarea');
        $this->assertEquals($result->attr('name'),'texte','textarea name is texte');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type1 ui-corner-all"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2 ui-widget ui-button-type1 ui-corner-all ui-button-text-only"]/span');
        $this->assertEquals($result->text('Node does not exist', false), 'Annuler','a/span button is Annuler');
    }

    public function testFormEdit()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(100,401,601));

        $start = \DateTime::createFromFormat("d/m/Y", '05/10/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '10/10/2022');

        $info = new HolidayInfo();
        $info->debut($start);
        $info->fin($end);
        $info->texte('salut');


        $entityManager->persist($info);
        $entityManager->flush();

        $id = $info->id();

        $crawler = $this->client->request('GET', "/holiday-info/$id");

        $this->assertSelectorTextContains('h3', 'Informations sur les congés');

        $this->assertSelectorTextContains('h4', 'Modification des informations sur les congés');

        $result=$crawler->filter('label');
        $this->assertEquals($result->eq(0)->text('Node does not exist', false), 'Date de début : ','label 1 is Date de début');
        $this->assertEquals($result->eq(1)->text('Node does not exist', false), 'Date de fin : ','label 2 is Date de fin');
        $this->assertEquals($result->eq(2)->text('Node does not exist', false), 'Texte : ','label 3 is Texte');

        $class = $crawler->filterXPath('//input[@name="debut"]');
        $this->assertEquals($class->attr('value'),'05/10/2022','input submit start is 05/10/2022');

        $class = $crawler->filterXPath('//input[@name="fin"]');
        $this->assertEquals($class->attr('value'),'10/10/2022','input submit end is 10/10/2022');

        $class = $crawler->filterXPath('//textarea');
        $this->assertEquals($class->text('Node does not exist', false), 'salut','input submit text is salut');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type1 ui-corner-all"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2 ui-widget ui-button-type1 ui-corner-all ui-button-text-only"]/span');
        $this->assertEquals($result->text('Node does not exist', false), 'Annuler','a/span button is Annuler');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type3 ui-widget ui-button-type1 ui-corner-all ui-button-text-only"]/span');
        $this->assertEquals($class->text('Node does not exist', false), 'Supprimer','a button is Supprimer');
    }

    public function testHolidayInfoList()
    {
        global $entityManager;
        date_default_timezone_set('UTC');

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(HolidayInfo::class);
        $this->logInAgent($agent, array(100,401,601));

        $crawler = $this->client->request('GET', "/holiday-info");

        $this->assertSelectorTextContains('h3', 'Informations sur les congés');

        $result = $crawler->filterXPath('//a[@class="ui-button"]');
        $this->assertEquals('Ajouter', $result->text('Node does not exist', false), 'a is Ajouter');

        $this->assertSelectorTextContains('p', 'Aucune information enregistrée.');

        $start = new DateTime('+1 day');
        $end = new DateTime('+1 month +1 day');

        $info = new HolidayInfo();
        $info->debut($start);
        $info->fin($end);
        $info->texte('hello');

        $entityManager->persist($info);
        $entityManager->flush();

        $crawler = $this->client->request('GET', "/holiday-info");

        $this->assertSelectorTextContains('h3', 'Informations sur les congés');

        $result = $crawler->filterXPath('//a[@class="ui-button"]');
        $this->assertEquals('Ajouter', $result->text('Node does not exist', false), 'a is Ajouter');

        $result = $crawler->filterXPath('//th[@class="dataTableDateFR"]');
        $this->assertEquals($result->eq(0)->text('Node does not exist', false), 'Début','table title id Début');
        $this->assertEquals($result->eq(1)->text('Node does not exist', false), 'Fin','table title is Fin');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Edit','span logo edit title is Edit');

        $result = $crawler->filterXPath('//tbody/tr/td');
        $this->assertEquals($result->eq(1)->text(), $start->format('d/m/Y'),'date début is ok');
        $this->assertEquals($result->eq(2)->text(), $end->format('d/m/Y'),'date fin is ok');
        $this->assertEquals($result->eq(3)->text(),'hello','text info is ok');
    }
}
