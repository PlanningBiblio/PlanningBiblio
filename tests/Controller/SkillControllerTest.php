<?php

use App\Model\Agent;
use App\Model\Skill;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class SkillControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Skill::class);


        $this->logInAgent($agent, array(5));

        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('csrf');

        $this->client->request('POST', '/skill', array('nom' => 'securite', '_token' => $token));

        $skill = $entityManager->getRepository(Skill::class)->findOneBy(array('nom' => 'securite'));

        $this->assertEquals('securite', $skill->nom(), 'skill nom is securite');

    }

    public function testNewForm()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(5));

        $crawler = $this->client->request('GET', '/skill/add');

        $this->assertSelectorTextContains('h3', 'Ajout d\'une activité');

        $result=$crawler->filterXPath('//table[@class="tableauFiches"]');
        $this->assertStringContainsString(' Nom :', $result->text(null, false),'label is Nom');

        $result=$crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('name'),'nom','check input for name');

        $class = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type2"]');
        $this->assertEquals($class->attr('value'),'Annuler','input submit value is Annuler');
    }

    public function testFormEdit()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Skill::class);


        $this->logInAgent($agent, array(5));

        $skill = new Skill();
        $skill->nom('security');

        $entityManager->persist($skill);
        $entityManager->flush();

        $id = $skill->id();

        $crawler = $this->client->request('GET', "/skill/$id");

        $this->assertSelectorTextContains('h3', 'Modification de l\'activité');

        $result=$crawler->filterXPath('//table[@class="tableauFiches"]');
        $this->assertStringContainsString(' Nom :', $result->text(null, false),'label is Nom');

        $result=$crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('value'),'security','check input for name');

        $class = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit value is Valider');

        $class = $crawler->filterXPath('//input[@class="ui-button ui-button-type2"]');
        $this->assertEquals($class->attr('value'),'Annuler','input submit value is Annuler');
    }

    public function testSkillList()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Skill::class);

        $this->logInAgent($agent, array(5));

        $skill = new Skill();
        $skill->nom('security');

        $entityManager->persist($skill);
        $entityManager->flush();

        $crawler = $this->client->request('GET', "/skill");

        $this->assertSelectorTextContains('h3', 'Liste des activités');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Ajouter','check input for name');

        $result = $crawler->filterXPath('//th')->eq(1);
        $this->assertEquals($result->text(null, false),' Nom de l\'activité ','th is Nom de l\'activité');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','Edit Icons');

        $result = $crawler->filterXPath('//tbody/tr/td')->eq(1);
        $this->assertEquals($result->text(null, false),'security','skill name');
    }
}
