<?php

use App\Model\Agent;
use App\Model\Position;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class PositionControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);


        $this->logInAgent($agent, array(5));

        $client = static::createClient();
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('csrf');

        $client->request('POST', '/position', array('nom' => 'bureau', 'activites' => [], 'categories' => [], 'site' => 1, 'bloquant' => 1, 'statistiques' => 0, 'teleworking' => 1, 'etage' => '', 'groupe' => 'admin', 'groupe_id' => '', 'obligatoire' => 'Obligatoire', 'site' => '', '_token' => $token));


        $position = $entityManager->getRepository(Position::class)->findOneBy(array('nom' => 'bureau'));

        $this->assertEquals($position->nom(), 'bureau', 'post name is bureau');
        $this->assertEquals($position->bloquant(), 1, 'post bloquant is 1');
        $this->assertEquals($position->statistiques(), 0, 'post statistique is 0');
        $this->assertEquals($position->teleworking(), 1, 'post teleworking is 1');
        $this->assertEquals($position->obligatoire(), 'Obligatoire', 'post obligatoire is Obligatoire');
        $this->assertEquals($position->groupe(), 'admin', 'post group is admin');
    }

    public function testNewForm()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(5));

        $client = static::createClient();


        $crawler = $client->request('GET', '/position/add');

        $this->assertSelectorTextContains('h3', 'Modification du poste');

        $result = $crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('name'),'nom','input for post name value is nom');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(7)->text(),'Nom du poste :','label is Nom du post');

        $result = $crawler->filterXPath('//tr/td/table');
        $this->assertStringContainsString('Etage :', $result->text(), 'label is Etage');
        $this->assertStringContainsString('Mezzanine', $result->text(),'select contains Mezzanine');
        $this->assertStringContainsString('RDC', $result->text(),'Select contains RDC');
        $this->assertStringContainsString('RDJ', $result->text(),'Select contains RDJ');
        $this->assertStringContainsString('Magasins', $result->text(),'Select contains Magasins');
        $this->assertStringContainsString('Groupe:', $result->text(), 'label is Groupe');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-add"]');
        $this->assertEquals($result->attr('title'),'Ajouter','span is Ajouter');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(13)->text(),'Obligatoire / renfort :','label is Obligatoire / renfort : ');

        $result = $crawler->filterXPath('//input[@name="obligatoire"]');
        $this->assertEquals($result->attr('value'),'Obligatoire','input submit value is Obligatoire');
        $this->assertEquals($result->eq(1)->attr('value'),'Renfort','input submit value is Renfort');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(15)->text(),'Bloquant :','label is Bloquant : ');

        $result = $crawler->filterXPath('//input[@name="bloquant"]');
        $this->assertEquals($result->attr('value'),'1','input bloquant value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input bloquant value is Valider');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(17)->text(),'Statistiques :','label is Statistiques: ');

        $result = $crawler->filterXPath('//input[@name="statistiques"]');
        $this->assertEquals($result->attr('value'),'1','input statistiques value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input statistiques value is Valider');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(19)->text(),'Compatible télétravail :','label is Compatible télétravail');

        $result = $crawler->filterXPath('//input[@name="teleworking"]');
        $this->assertEquals($result->attr('value'),'1','input teleworking value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input teleworking value is 0');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(22)->text(),'Activités :','label is Nom du post');
        $this->assertStringContainsString(' Assistance audiovisuel', $result->eq(23)->text(),'checkBox is Assistance audiovisuel');
        $this->assertStringContainsString(' Assistance autoformation', $result->eq(23)->text(),'checkBox is Assistance autoformation');
        $this->assertStringContainsString(' Communication', $result->eq(23)->text(),'checkBox is Communication');
        $this->assertStringContainsString(' Communication réserve', $result->eq(23)->text(),'checkBox is Communication réserve');
        $this->assertStringContainsString(' Inscription', $result->eq(23)->text(),'checkBox is Inscription');
        $this->assertStringContainsString(' Prêt/retour de document', $result->eq(23)->text(),'checkBox is Prêt/retour de document');
        $this->assertStringContainsString(' Prêt de matériel', $result->eq(23)->text(),'checkBox is Prêt de matériel');
        $this->assertStringContainsString(' Rangement', $result->eq(23)->text(),'checkBox is Rangement');
        $this->assertStringContainsString(' Renseignement', $result->eq(23)->text(),'checkBox is Renseignement');
        $this->assertStringContainsString(' Renseignement bibliographique', $result->eq(23)->text(),'checkBox is Renseignement bibliographique ');
        $this->assertStringContainsString(' Renseignement réserve', $result->eq(23)->text(),'checkBox is Renseignement réserve');
        $this->assertStringContainsString(' Renseignement spécialisé', $result->eq(23)->text(),'checkBox is Renseignement spécialisé');

        $this->assertEquals($result->eq(24)->text(),' Catégories* :','label is Nom du post');
        $this->assertStringContainsString(' Catégorie A', $result->eq(25)->text(),'checkBox is Catégorie A ');
        $this->assertStringContainsString(' Catégorie B', $result->eq(25)->text(),'checkBox is Catégorie B');
        $this->assertStringContainsString(' Catégorie C', $result->eq(25)->text(),'checkBox is Catégorie C');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($result->text(),'Annuler','input submit value is Annuler');

        $result = $crawler->filterXPath('//td[@class="noteBasDePage"]');
        $this->assertStringContainsString('* Si aucune catégorie n\'est sélectionnée, les agents de toutes les catégories pourront être placés sur ce poste', $result->text(),'noteBasDePage is ok');
    }

    public function testFormEdit()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);


        $this->logInAgent($agent, array(5));

        $client = static::createClient();

        $position = new Position();
        $position->nom('bureau');
        $position->groupe('administratif');
        $position->groupe_id('26');
        $position->obligatoire('bureau');
        $position->etage('Mezzanine');
        $position->activites(['communication','inscription']);
        $position->statistiques(0);
        $position->teleworking(1);
        $position->bloquant(0);
        $position->categories([]);

        $entityManager->persist($position);
        $entityManager->flush();

        $id = $position->id();

        $crawler = $client->request('GET', "/position/$id");

        $this->assertSelectorTextContains('h3', 'Modification du poste');

        $result = $crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('value'),'bureau','input for post name value is nom');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(7)->text(),'Nom du poste :','label is Nom du post');


        $result = $crawler->filterXPath('//tr/td/table');
        $this->assertStringContainsString('Etage :', $result->text(), 'label is Etage');
        $this->assertStringContainsString('Mezzanine', $result->text(),'select contains Mezzanine');
        $this->assertStringContainsString('RDC', $result->text(),'Select contains RDC');
        $this->assertStringContainsString('RDJ', $result->text(),'Select contains RDJ');
        $this->assertStringContainsString('Magasins', $result->text(),'Select contains Magasins');
        $this->assertStringContainsString('Groupe:', $result->text(), 'label is Groupe');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-add"]');
        $this->assertEquals($result->attr('title'),'Ajouter','span edit icon is Ajouter');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(13)->text(),'Obligatoire / renfort :','label is Obligatoire / renfort : ');

        $result = $crawler->filterXPath('//input[@name="obligatoire"]');
        $this->assertEquals($result->attr('value'),'Obligatoire','input obligatoire value is Obligatoire');
        $this->assertEquals($result->eq(1)->attr('value'),'Renfort','input obligatoire value is Obligatoire Renfort');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(15)->text(),'Bloquant :','label is Bloquant :');

        $result = $crawler->filterXPath('//input[@name="bloquant"]');
        $this->assertEquals($result->attr('value'),'1','input bloquant value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input bloquant value is 0');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(17)->text(),'Statistiques :','label is Statistiques : ');

        $result = $crawler->filterXPath('//input[@name="statistiques"]');
        $this->assertEquals($result->attr('value'),'1','input statistiques value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input statistiques value is 0');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(19)->text(),'Compatible télétravail :','label is Compatible télétravail');

        $result = $crawler->filterXPath('//input[@name="teleworking"]');
        $this->assertEquals($result->attr('value'),'1','input teleworking value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input teleworking value is 0');

        $result = $crawler->filterXPath('//td');
        $this->assertEquals($result->eq(22)->text(),'Activités :','label is Nom du post');
        $this->assertStringContainsString(' Assistance audiovisuel', $result->eq(23)->text(),'checkBox is Assistance audiovisuel');
        $this->assertStringContainsString(' Assistance autoformation', $result->eq(23)->text(),'checkBox is Assistance autoformation');
        $this->assertStringContainsString(' Communication', $result->eq(23)->text(),'checkBox is Communication');
        $this->assertStringContainsString(' Communication réserve', $result->eq(23)->text(),'checkBox is Communication réserve');
        $this->assertStringContainsString(' Inscription', $result->eq(23)->text(),'checkBox is Inscription');
        $this->assertStringContainsString(' Prêt/retour de document', $result->eq(23)->text(),'checkBox is Prêt/retour de document');
        $this->assertStringContainsString(' Prêt de matériel', $result->eq(23)->text(),'checkBox is Prêt de matériel');
        $this->assertStringContainsString(' Rangement', $result->eq(23)->text(),'checkBox is Rangement');
        $this->assertStringContainsString(' Renseignement', $result->eq(23)->text(),'checkBox is Renseignement');
        $this->assertStringContainsString(' Renseignement bibliographique', $result->eq(23)->text(),'checkBox is Renseignement bibliographique ');
        $this->assertStringContainsString(' Renseignement réserve', $result->eq(23)->text(),'checkBox is Renseignement réserve');
        $this->assertStringContainsString(' Renseignement spécialisé', $result->eq(23)->text(),'checkBox is Renseignement spécialisé');

        $this->assertEquals($result->eq(24)->text(),' Catégories* :','label is Nom du post');
        $this->assertStringContainsString(' Catégorie A', $result->eq(25)->text(),'checkBox is Catégorie A ');
        $this->assertStringContainsString(' Catégorie B', $result->eq(25)->text(),'checkBox is Catégorie B');
        $this->assertStringContainsString(' Catégorie C', $result->eq(25)->text(),'checkBox is Catégorie C');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($result->text(),'Annuler','input submit value is Annuler');

        $result = $crawler->filterXPath('//td[@class="noteBasDePage"]');
        $this->assertStringContainsString('* Si aucune catégorie n\'est sélectionnée, les agents de toutes les catégories pourront être placés sur ce poste', $result->text(),'noteBasDePage is ok');
    }

    public function testPositionList()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);


        $this->logInAgent($agent, array(5));

        $client = static::createClient();

        $position = new Position();
        $position->nom('bureau');
        $position->groupe('administratif');
        $position->groupe_id('26');
        $position->obligatoire('obligatoire');
        $position->etage('Mezzanine');
        $position->activites(['communication','inscription']);
        $position->statistiques(1);
        $position->teleworking(1);
        $position->bloquant(0);
        $position->categories([]);

        $entityManager->persist($position);
        $entityManager->flush();

        $crawler = $client->request('GET', "/position");

        $this->assertSelectorTextContains('h3', 'Liste des postes');

        $result = $crawler->filterXPath('//thead/tr');
        $this->assertStringContainsString('Nom du poste',$result->text(),'check label for name');
        $this->assertStringContainsString('Etage',$result->text(),'check label for Etage');
        $this->assertStringContainsString('Activités',$result->text(),'check label for activités');
        $this->assertStringContainsString('Groupe',$result->text(),'check label for Group');
        $this->assertStringContainsString('Obligatoire/renfort',$result->text(),'check label for Obligatoire');
        $this->assertStringContainsString('Bloquant',$result->text(),'check label for Bloquant');
        $this->assertStringContainsString('Statistiques',$result->text(),'check label for Sttistiques');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','Edit Icons');

        $result = $crawler->filterXPath('//tbody/tr/td');
        $this->assertEquals($result->eq(1)->text(),'bureau','post name');
        $this->assertEquals($result->eq(5)->text(),'obligatoire','post obligatoire');
        $this->assertEquals($result->eq(6)->text(),'Non','post bloquant');
        $this->assertEquals($result->eq(7)->text(),'Oui','skill statistiques');
    }
}



