<?php

use App\Model\Agent;
use App\Model\AdminInfo;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class AdminInfoControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);
        

        $this->logInAgent($agent, array(23));

        $client = static::createClient();
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('csrf');

        $client->request('POST', '/admin/info', array('start' => '05/10/2021', 'end' => '10/10/2021', 'text' => 'salut', '_token' => $token));
        

        $info = $entityManager->getRepository(AdminInfo::class)->findOneBy(array('debut' => '20211005', 'fin' => '20211010'));


        $this->assertEquals('salut', $info->texte(), 'info texte is salut');
        
        $this->assertEquals('20211005', $info->debut(), 'debut is 20211005');
        $this->assertEquals('20211010', $info->fin(), 'fin is 20211010');
    

    }

    public function testNewForm()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        

        $this->logInAgent($agent, array(23));

        $client = static::createClient();
        
        
        $crawler = $client->request('GET', '/admin/info/add');

        $this->assertSelectorTextContains('h3', 'Messages d\'information');

        $this->assertSelectorTextContains('h4', 'Ajout d\'une information');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text(),'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text(),'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text(),'Texte','label 3 is Texte');

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
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);
        

        $this->logInAgent($agent, array(23));

        $client = static::createClient();

        $info = new AdminInfo();
        $info->debut('20221005');
        $info->fin('20221010');
        $info->texte('salut');

        $entityManager->persist($info);
        $entityManager->flush();

        $id = $info->id();
        
        $client->request('GET', "/admin/info/$id");

        $this->assertSelectorTextContains('h3', 'Messages d\'information');


        $this->assertSelectorTextContains('h4', 'Modifications des messages d\'informations');


        $this->assertSelectorTextContains('textarea', 'salut');
 
        
        $crawler = new Crawler();
        $crawler = $client->request('GET', "/admin/info/$id");

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text(),'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text(),'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text(),'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type1"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//input[@name="start"]');
        $this->assertEquals($class->attr('value'),'05/10/2022','input submit start is 05/10/2022');

        $class = $crawler->filterXPath('//input[@name="end"]');
        $this->assertEquals($class->attr('value'),'10/10/2022','input submit end is 10/10/2022');

        $class = $crawler->filterXPath('//a[@class="ui-button ui-button-type3"]');
        $this->assertEquals($class->attr('href'),"javascript:deleteAdminInfo($id);",'href a>span>Annuler is admin/info');
    }

    public function testAdminInfoList()
    {
        global $entityManager;
        date_default_timezone_set('UTC');

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);
        $this->logInAgent($agent, array(23));

	$client = static::createClient();
	$crawler = $client->request('GET', "/admin/info");
	
	$this->assertSelectorTextContains('h3', 'Messages d\'information');
	
	$result = $crawler->filterXPath('//div');
        $this->assertStringContainsString('Ajouter', $result->eq(8)->text(),'a>span>Ajouter is admin/info/add');
	$this->assertStringContainsString('Aucune information enregistrée.', $result->eq(7)->text(),  'text no info is Aucune information enregistrée.');	

        $d = date("d")+1;
        $m_1 = date("m");
        $m_2 = date("m")+1;
        $y = date("Y");

        $info = new AdminInfo();
        $info->debut("$y$m_1$d\n");
        $info->fin("$y$m_2$d\n");
        $info->texte('hello');

        $entityManager->persist($info);
        $entityManager->flush();

    
        $crawler = $client->request('GET', "/admin/info");

        $this->assertSelectorTextContains('h3', 'Messages d\'information');

	$result = $crawler->filterXPath('//div');
        $this->assertStringContainsString('Ajouter', $result->eq(8)->text(),'a>span>Ajouter is admin/info/add');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type1"]');
        $this->assertEquals($result->attr('href'),'/admin/info/add','a>span>Ajouter is admin/info/add');
        
        $result = $crawler->filterXPath('//th[@class="tableSort"]');
        $this->assertEquals($result->eq(0)->text(),'Début','table title id Début');

        $result = $crawler->filterXPath('//th[@class="tableSort"]');
        $this->assertEquals($result->eq(1)->text(),'Fin','table title is Fin');
        $this->assertEquals($result->eq(2)->text(),'Texte','table title is Texte');
	
	$result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
	$this->assertEquals($result->attr('title'),'Edit','span logo edit title is Edit');

        $result = $crawler->filterXPath('//tbody/tr/td');
	$this->assertEquals($result->eq(1)->text(),"$d/$m_1/$y",'date début is ok');
        $this->assertEquals($result->eq(2)->text(),"$d/$m_2/$y",'date fin is ok');
	$this->assertEquals($result->eq(3)->text(),'hello','text info is ok');
	
    }
}  





