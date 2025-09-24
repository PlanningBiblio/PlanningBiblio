<?php

use App\Entity\Agent;
use App\Entity\AbsenceInfo;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class AbsenceInfoControllerTest extends PLBWebTestCase
{

    public function testAdd(): void
    {
        $entityManager = $this->entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceInfo::class);

        $this->logInAgent($agent, array(201));

        $crawler = $this->client->request('GET', '/absences/info/add');
        $extract_result = $crawler->filter('#form input[name="_token"]')->extract(array('value'));
        $token = $extract_result[0];

        $this->client->request('POST', '/absences/info', array('start' => '05/10/2022', 'end' => '10/10/2022', 'text' => 'salut', '_token' => $token));

        $info = $entityManager->getRepository(AbsenceInfo::class)->findOneBy(array('texte' => 'salut'));
        $this->assertEquals('05/10/2022', $info->getStart()->format('d/m/Y'), 'debut is ok');
        $this->assertEquals('10/10/2022', $info->getEnd()->format('d/m/Y'), 'fin is ok');

        $this->assertEquals('salut', $info->getComment(), 'info texte is salut');

    }

    public function testNewForm(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(201));

        $crawler = $this->client->request('GET', '/absences/info/add');

        $this->assertSelectorTextContains('h3', 'Informations sur les absences');

        $this->assertSelectorTextContains('h4', 'Ajout d\'une information');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $result = $crawler->filterXPath('//input[@class="datepicker"]');
        $this->assertEquals($result->eq(0)->attr('name'),'start','input datepicker name is start');
        $this->assertEquals($result->eq(1)->attr('name'),'end','input datepicker name is end');

        $result = $crawler->filterXPath('//textarea');
        $this->assertEquals($result->attr('name'),'text','textarea name is texte');

        $class = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($class->text('Node does not exist', false), 'Annuler','a button is Annuler');
    }

    public function testFormEdit(): void
    {
        $entityManager = $this->entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceInfo::class);

        $this->logInAgent($agent, array(201));

        $start = \DateTime::createFromFormat("d/m/Y", '05/10/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '10/10/2022');

        $info = new AbsenceInfo();
        $info->setStart($start);
        $info->setEnd($end);
        $info->setComment('salut');

        $entityManager->persist($info);
        $entityManager->flush();

        $id = $info->getId();

        $crawler = $this->client->request('GET', "/absences/info/$id");

        $this->assertSelectorTextContains('h3', 'Informations sur les absences');

        $this->assertSelectorTextContains('h4', 'Modifications des informations sur les absences');

        $result=$crawler->filter('label');
        $this->assertEquals($result->eq(0)->text('Node does not exist', false), 'Date de début','label 1 is Date de début');
        $this->assertEquals($result->eq(1)->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');
        $this->assertEquals($result->eq(2)->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//input[@name="start"]');
        $this->assertEquals($class->attr('value'),'05/10/2022','input submit start is 05/10/2022');

        $class = $crawler->filterXPath('//input[@name="end"]');
        $this->assertEquals($class->attr('value'),'10/10/2022','input submit end is 10/10/2022');

        $class = $crawler->filterXPath('//textarea');
        $this->assertEquals($class->text('Node does not exist', false), 'salut','input submit text is salut');

        $class = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($class->text('Node does not exist', false), 'Annuler','a button is Annuler');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type3"]');
        $this->assertEquals($class->text('Node does not exist', false), 'Supprimer','a button is Supprimer');
    }

    public function testAbsenceInfoList(): void
    {
        $entityManager = $this->entityManager;
        date_default_timezone_set('UTC');

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceInfo::class);
        $this->logInAgent($agent, array(201));

        $crawler = $this->client->request('GET', "/absences/info");

        $this->assertSelectorTextContains('h3', 'Informations sur les absences');

        $result = $crawler->filterXPath('//a[@class="ui-button"]');
        $this->assertEquals('Ajouter', $result->text('Node does not exist', false), 'a is Ajouter');

        $this->assertSelectorTextContains('p', 'Aucune information enregistrée');

        $start = new DateTime('+1 day');
        $end = new DateTime('+1 month +1 day');

        $info = new AbsenceInfo();
        $info->setStart($start);
        $info->setEnd($end);
        $info->setComment('hello');

        $entityManager->persist($info);
        $entityManager->flush();

        $crawler = $this->client->request('GET', "/absences/info");

        $this->assertSelectorTextContains('h3', 'Informations sur les absences');

        $result = $crawler->filterXPath('//a[@class="ui-button"]');
        $this->assertEquals('Ajouter', $result->text('Node does not exist', false), 'a is Ajouter');

        $result = $crawler->filterXPath('//table[@id="AbsenceInfoTable"]');
        $this->assertStringContainsString('Début',$result->text('Node does not exist', false), 'table title id Début');
        $this->assertStringContainsString('Fin',$result->text('Node does not exist', false), 'table title is Fin');

        $this->assertStringContainsString('Texte',$result->text('Node does not exist', false), 'table title is Texte');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Edit','span logo edit title is Edit');

        $result = $crawler->filterXPath('//tbody/tr/td');
        $this->assertEquals($result->eq(1)->text('Node does not exist', false), $start->format('d/m/Y'),'date début is ok');
        $this->assertEquals($result->eq(2)->text('Node does not exist', false), $end->format('d/m/Y'),'date fin is ok');
        $this->assertEquals($result->eq(3)->text('Node does not exist', false), 'hello','text info is ok');
    }
}
