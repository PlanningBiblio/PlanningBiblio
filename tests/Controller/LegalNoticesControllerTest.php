<?php

use App\Model\Agent;
use Symfony\Component\DomCrawler\Crawler;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class LegalNoticesControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testLegalNotices()
    {
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(99,100));

        $client = static::createClient();

        $crawler = $client->request('GET', '/legal-notices');

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Mentions légales','h3 is Mentions légales');

        $result = $crawler->filterXPath('//div[@class="footer"]');
        $this->assertStringContainsString("www.planno.fr",$result->text(null,false),'footer contains www.planno.fr');
        $this->assertStringNotContainsString("Mentions légales",$result->text(null,false),'footer does not contains Mentionns légales');

        $lg = '# Perseus et potat

        ## Mite perierunt crimenque omnes precantia deae
        
        Lorem markdownum maturior caputque purpureis servire Boreas, euntem et herbis:
        certamine Hyacinthe volui, fatemur *quoque*, male. Dat Telamon; deposuit si
        Pyramus laedar valido Ulixes, meditataque.
        
        ## Vel frustra ictu cruore
        ';

        $this->setParam('legalNotices', $lg);

        $crawler = $client->request('GET', '/legal-notices');

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Mentions légales','h3 is Mentions légales');

        $result = $crawler->filterXPath('//h1');
        $this->assertEquals($result->text(null,false),'Perseus et potat','input for post name value is nom');

        $lg = '<h1>Titre h1</h1>
        <h2>Titre h2</h2>
        <p>Lorem markdownum maturior caputque purpureis servire Boreas, euntem et herbis:
        certamine Hyacinthe volui, fatemur *quoque*, male. Dat Telamon; deposuit si
        Pyramus laedar valido Ulixes, meditataque.</p>
        ';

        $this->setParam('legalNotices', $lg);

        $crawler = $client->request('GET', '/legal-notices');

        $result = $crawler->filterXPath('//h1');
        $this->assertEquals('Titre h1',$result->text(null,false),'h1 is Titre h1');

        $result = $crawler->filterXPath('//h2');
        $this->assertEquals('Titre h2',$result->text(null,false),'h2 is Titre h2');


        $this->setUpPantherClient();

        $agent2 = $this->builder->build(Agent::class, array(
            'login' => 'agentdeux',
            'droits' => array(100)
        ));

        $_SESSION['login_id'] = '';
        $_SESSION['login_nom'] = '';
        $_SESSION['login_prenom'] = '';
        $_SESSION['oups']['Auth-Mode'] = '';
        $_SESSION['oups']['week'] = '';

        $this->login($agent2);

        $crawler = $this->client->request('GET', "/legal-notices");

        $result = $crawler->filterXPath('//div[@id="content"]');
        $this->assertStringContainsString('Titre h1',$result->text(),'h1 is Titre h1');

        $result = $crawler->filterXPath('//div[@class="footer"]');
        $this->assertStringContainsString("Mentions légales",$result->text(),'Mention légales is in the footer');;
    }
}
