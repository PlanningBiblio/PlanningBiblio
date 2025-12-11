<?php

use App\Entity\Agent;
use App\Entity\AdminInfo;
use Symfony\Component\DomCrawler\Crawler;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AdminInfoControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, array(23));

        $crawler = $this->client->request('GET', '/admin/info/add');
        $extract_result = $crawler->filter('#form input[name="_token"]')->extract(array('value'));
        $token = $extract_result[0];

        $this->client->request('POST', '/admin/info', array('start' => '05/10/2021', 'end' => '10/10/2021', 'text' => 'salut', '_token' => $token));

        $info = $this->entityManager->getRepository(AdminInfo::class)->findOneBy(array('debut' => '20211005', 'fin' => '20211010'));

        $this->assertEquals('salut', $info->getComment(), 'info texte is salut');

        $this->assertEquals('20211005', $info->getStart(), 'debut is 20211005');
        $this->assertEquals('20211010', $info->getEnd(), 'fin is 20211010');
    }

    public function testNewForm()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(23));

        $this->client->request('GET', '/admin/info/add');

        $this->assertSelectorTextContains('h3', 'Messages d\'information');

        $this->assertSelectorTextContains('h4', 'Ajout d\'une information');

        $crawler = new Crawler();
        $crawler = $this->client->request('GET', '/admin/info/add');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($class->attr('href'),'/admin/info','href a>span>Annuler is admin/info');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type1"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//input[@class="datepicker"]')->eq(0);
        $this->assertEquals($result->attr('name'),'start','input datepicker name is start');

        $result = $crawler->filterXPath('//input[@class="datepicker"]')->eq(1);
        $this->assertEquals($result->attr('name'),'end','input datepicker name is end');

        $result = $crawler->filterXPath('//textarea');
        $this->assertEquals($result->attr('name'),'text','textarea name is texte');
    }

    public function testFormEdit()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, array(23));

        $info = new AdminInfo();
        $info->setStart('20221005');
        $info->setEnd('20221010');
        $info->setComment('salut');

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $id = $info->getId();

        $crawler = $this->client->request('GET', "/admin/info/$id");

        $this->assertSelectorTextContains('h3', 'Messages d\'information');

        $this->assertSelectorTextContains('h4', 'Modifications des messages d\'informations');

        $this->assertSelectorTextContains('textarea', 'salut');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type1"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//input[@name="start"]');
        $this->assertEquals($class->attr('value'),'05/10/2022','input submit start is 05/10/2022');

        $class = $crawler->filterXPath('//input[@name="end"]');
        $this->assertEquals($class->attr('value'),'10/10/2022','input submit end is 10/10/2022');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type3"]');
        $this->assertEquals($class->attr('href'),"javascript:deleteAdminInfo($id);",'href a>span>Annuler is admin/info');
    }
}
