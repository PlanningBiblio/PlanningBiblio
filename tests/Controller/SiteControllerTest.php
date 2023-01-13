<?php

use App\Model\Agent;
use App\Model\Site;
use App\Model\SiteMail;
use App\Model\ConfigParam;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class SiteControllerTest extends PLBWebTestCase
{
    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

        $this->builder = new FixtureBuilder();
        $this->builder->delete(Agent::class);

        $this->entityManager = $entityManager;
    }

    protected function setParam($name, $value)
    {
        $GLOBALS['config'][$name] = $value;
        $param = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $name]);

        $param->valeur($value);
        $this->entityManager->persist($param);
        $this->entityManager->flush();
    }

    public function testAdd()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(99,100));

        $client = static::createClient();
        $client->request('POST', '/site', array('nom' => 'site_un', 'mail_1' => 'jean@mail.fr', 'mail_2' => 'marc@mail.com', 'CSRFToken' => '00000'));

        $site = $GLOBALS['entityManager']->getRepository(Site::class)->findBy(array('nom' => 'site_un'));

        $this->assertEquals('site_un', $site[0]->nom(), 'site nom is site N°1');

        $db = new \db();
        $db->select2("site_mail", "*", array("site_id" => $site[0]->id()));
        $mails = $db->result;

        $this->assertEquals('jean@mail.fr', $mails[0]['mail'], 'mail is jean@mail.fr');
        $this->assertEquals('marc@mail.com', $mails[1]['mail'], 'mail is marc@mail.com');
    }

    public function testEdit()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(99,100));

        $client = static::createClient();

        $db = new \db;
        $db->CSRFToken = '00000';
        $id_site1 = $db->insert("site", array('nom' => 'site_un'));

        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jm@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jp@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jc@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jd@mail.fr'));

        $client->request('POST', '/site', array('id'=> $id_site1, 'nom' => 'site_unnn', 'mail_1' => 'jean@mail.fr', 'mail_2' => 'marc@mail.com', 'CSRFToken' => '00000'));

        $db = new \db();
        $db->select2("site", "*", array("id" => $id_site1));
        $site = $db->result;

        $this->assertEquals('site_unnn', $site[0]['nom'], 'site nom is site N°1');

        $db = new \db();
        $db->select2("site_mail", "*", array("site_id" => $site[0]['id']));
        $mails = $db->result;

        $this->assertEquals('marc@mail.com', $mails[0]['mail'], 'mail is marc@mail.com');
        $this->assertEquals('jean@mail.fr', $mails[1]['mail'], 'mail is jean@mail.fr');
    }



    public function testFormEdit()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $db = new \db;
        $db->CSRFToken = '00000';

        $this->logInAgent($agent, array(99,100));

        $client = static::createClient();

        $id_site = $db->insert("site", array('nom' => 'site_un'));

        $db->insert("site_mail", array('site_id' => $id_site, 'mail' => 'jm@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site, 'mail' => 'jp@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site, 'mail' => 'jc@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site, 'mail' => 'jd@mail.fr'));

        $crawler = $client->request('GET', "/site/$id_site");

        $this->assertSelectorTextContains('h3', 'Modification du site');

        $result=$crawler->filterXPath('//form[@id="site_form"]');
        $this->assertStringContainsString('Nom du site :', $result->text(null, false));
        $this->assertStringContainsString('Mails :', $result->text(null, false));

        $result=$crawler->filterXPath('//input[@name="nom"]');
        $this->assertEquals($result->attr('value'),'site_un');

        $result=$crawler->filterXPath('//input[@name="mail_1"]');
        $this->assertEquals($result->attr('value'),'jm@mail.fr');

        $result=$crawler->filterXPath('//input[@name="mail_2"]');
        $this->assertEquals($result->attr('value'),'jp@mail.fr');

        $result=$crawler->filterXPath('//input[@name="mail_3"]');
        $this->assertEquals($result->attr('value'),'jc@mail.fr');

        $result=$crawler->filterXPath('//input[@name="mail_4"]');
        $this->assertEquals($result->attr('value'),'jd@mail.fr');

        $class = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//a[@href="/site"]');
        $this->assertEquals($class->text(),'Annuler','input submit value is Annuler');
    }

    public function testIndex()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(99,100));

        $client = static::createClient();

        $db = new \db;
        $db->CSRFToken = '00000';
        $id_site1 = $db->insert("site", array('nom' => 'site_un'));
        $id_site2 = $db->insert("site", array('nom' => 'site_deux'));

        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jm@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jp@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jc@mail.fr'));
        $db->insert("site_mail", array('site_id' => $id_site1, 'mail' => 'jd@mail.fr'));

        $db->insert("site_mail", array('site_id' => $id_site2, 'mail' => 'pl@mail.fr'));

        $crawler = $client->request('GET', "/site");

        $this->assertSelectorTextContains('h3', 'Liste des sites');

        $result = $crawler->filterXPath('//table[@id="tableSites"]');
        $this->assertStringContainsString('Nom du site', $result->text(null, false), 'label is Nom du post');
        $this->assertStringContainsString('Mails', $result->text(null, false), 'label is Nom du post');


        $result = $crawler->filterXPath('//td[@title="jm@mail.fr; jp@mail.fr; jc@mail.fr; jd@mail.fr; "]');
        $this->assertEquals('jm@mail.fr; jp@mail.fr; jc@mail.fr ...', $result->text(null, false));

        $result = $crawler->filterXPath("//td[@id='site_name_$id_site1']");
        $this->assertEquals('site_un', $result->text(null, false));

        $result = $crawler->filterXPath("//td[@id='site_name_$id_site2']");
        $this->assertEquals('site_deux', $result->text(null, false));


        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Ajouter');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','Edit Icons');
    }
}
