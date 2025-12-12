<?php

use App\Entity\Agent;
use App\Entity\Position;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class PositionControllerTest extends PLBWebTestCase
{
    public function testAdd(): void
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);

        $this->logInAgent($agent, array(5));

        $crawler = $this->client->request('GET', '/position/add');
        $extract_result = $crawler->filter('input[name="_token"]')->extract(array('value'));
        $token = $extract_result[0];

        $this->client->request('POST', '/position', array('nom' => 'bureau', 'activites' => [], 'categories' => [], 'bloquant' => 1, 'statistiques' => 0, 'teleworking' => 1, 'etage' => '', 'groupe' => 'admin', 'groupe_id' => '', 'obligatoire' => 'Obligatoire', 'site' => '', '_token' => $token));

        $position = $entityManager->getRepository(Position::class)->findOneBy(array('nom' => 'bureau'));

        $this->assertEquals($position->getName(), 'bureau', 'post name is bureau');
        $this->assertEquals($position->isBlocking(), 1, 'post bloquant is 1');
        $this->assertEquals($position->isStatistics(), false, 'post statistique is false');
        $this->assertEquals($position->isTeleworking(), true, 'post teleworking is true');
        $this->assertEquals($position->getMandatory(), 'Obligatoire', 'post obligatoire is Obligatoire');
        $this->assertEquals($position->getGroup(), 'admin', 'post group is admin');
    }

    public function testNewForm(): void
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));


        $this->logInAgent($agent, array(5));

        $crawler = $this->client->request('GET', '/position/add');

        $this->assertSelectorTextContains('h3', 'Modification du poste');

        $result = $crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('name'),'nom','input for post name value is nom');

        $result = $crawler->filterXPath('//tr/td/table');
        $this->assertStringContainsString('Nom du poste :', $result->text('Node does not exist', false), 'label is Nom du post');
        $this->assertStringContainsString('Etage :', $result->text('Node does not exist', false), 'label is Etage');
        $this->assertStringContainsString('Mezzanine', $result->text('Node does not exist', false), 'select contains Mezzanine');
        $this->assertStringContainsString('RDC', $result->text('Node does not exist', false), 'Select contains RDC');
        $this->assertStringContainsString('RDJ', $result->text('Node does not exist', false), 'Select contains RDJ');
        $this->assertStringContainsString('Magasins', $result->text('Node does not exist', false), 'Select contains Magasins');
        $this->assertStringContainsString('Groupe:', $result->text('Node does not exist', false), 'label is Groupe');
        $this->assertStringContainsString('Obligatoire / renfort :',$result->text('Node does not exist', false), 'label is Obligatoire / renfort : ');
        $this->assertStringContainsString('Bloquant :',$result->text('Node does not exist', false), 'label is Bloquant : ');
        $this->assertStringContainsString('Quota de SP :',$result->text('Node does not exist', false), 'label is Quota de SP : ');
        $this->assertStringContainsString('Statistiques :',$result->text('Node does not exist', false), 'label is Statistiques: ');
        $this->assertStringContainsString('Compatible télétravail :',$result->text('Node does not exist', false), 'label is Compatible télétravail');

        $this->assertStringContainsString('Activités :',$result->eq(1)->text('Node does not exist', false), 'Activités :');
        $this->assertStringContainsString(' Assistance audiovisuel', $result->eq(1)->text('Node does not exist', false), 'checkBox is Assistance audiovisuel');
        $this->assertStringContainsString(' Assistance autoformation', $result->eq(1)->text('Node does not exist', false), 'checkBox is Assistance autoformation');
        $this->assertStringContainsString(' Communication', $result->eq(1)->text('Node does not exist', false), 'checkBox is Communication');
        $this->assertStringContainsString(' Communication réserve', $result->eq(1)->text('Node does not exist', false), 'checkBox is Communication réserve');
        $this->assertStringContainsString(' Inscription', $result->eq(1)->text('Node does not exist', false), 'checkBox is Inscription');
        $this->assertStringContainsString(' Prêt/retour de document', $result->eq(1)->text('Node does not exist', false), 'checkBox is Prêt/retour de document');
        $this->assertStringContainsString(' Prêt de matériel', $result->eq(1)->text('Node does not exist', false), 'checkBox is Prêt de matériel');
        $this->assertStringContainsString(' Rangement', $result->eq(1)->text('Node does not exist', false), 'checkBox is Rangement');
        $this->assertStringContainsString(' Renseignement', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement');
        $this->assertStringContainsString(' Renseignement bibliographique', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement bibliographique ');
        $this->assertStringContainsString(' Renseignement réserve', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement réserve');
        $this->assertStringContainsString(' Renseignement spécialisé', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement spécialisé');
        $this->assertStringContainsString(' Catégories* :',$result->eq(1)->text('Node does not exist', false), 'label is Nom du post');
        $this->assertStringContainsString(' Catégorie A', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie A ');
        $this->assertStringContainsString(' Catégorie B', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie B');
        $this->assertStringContainsString(' Catégorie C', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie C');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-add"]');
        $this->assertEquals($result->attr('title'),'Ajouter','span is Ajouter');

        $result = $crawler->filterXPath('//input[@name="obligatoire"]');
        $this->assertEquals($result->attr('value'),'Obligatoire','input submit value is Obligatoire');
        $this->assertEquals($result->eq(1)->attr('value'),'Renfort','input submit value is Renfort');

        $result = $crawler->filterXPath('//input[@name="bloquant"]');
        $this->assertEquals($result->attr('value'),'1','input bloquant value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input bloquant value is Valider');

        $result = $crawler->filterXPath('//input[@name="statistiques"]');
        $this->assertEquals($result->attr('value'),'1','input statistiques value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input statistiques value is Valider');

        $result = $crawler->filterXPath('//input[@name="teleworking"]');
        $this->assertEquals($result->attr('value'),'1','input teleworking value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input teleworking value is 0');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($result->text('Node does not exist', false), 'Annuler','input submit value is Annuler');

        $result = $crawler->filterXPath('//td[@class="noteBasDePage"]');
        $this->assertStringContainsString('* Si aucune catégorie n\'est sélectionnée, les agents de toutes les catégories pourront être placés sur ce poste', $result->text('Node does not exist', false), 'noteBasDePage is ok');
    }

    public function testFormEdit(): void
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);


        $this->logInAgent($agent, array(5));

        $position = new Position();
        $position->setName('bureau');
        $position->setGroup('administratif');
        $position->setGroupId('26');
        $position->setMandatory('Renfort');
        $position->setFloor('Mezzanine');
        $position->setActivities(['communication','inscription']);
        $position->setStatistics(0);
        $position->setTeleworking(1);
        $position->setBlocking(0);
        $position->setCategories([]);

        $entityManager->persist($position);
        $entityManager->flush();

        $id = $position->getId();

        $crawler = $this->client->request('GET', "/position/$id");

        $this->assertSelectorTextContains('h3', 'Modification du poste');

        $result = $crawler->filterXPath('//input[@class="ui-widget-content ui-corner-all"]');
        $this->assertEquals($result->attr('value'),'bureau','input for post name value is nom');

        $result = $crawler->filterXPath('//form[@action="/position"]');
        $this->assertStringContainsString('Nom du poste :',$result->text('Node does not exist', false), 'label is Nom du post');


        $result = $crawler->filterXPath('//tr/td/table');
        $this->assertStringContainsString('Etage :', $result->text('Node does not exist', false), 'label is Etage');
        $this->assertStringContainsString('Mezzanine', $result->text('Node does not exist', false), 'select contains Mezzanine');
        $this->assertStringContainsString('RDC', $result->text('Node does not exist', false), 'Select contains RDC');
        $this->assertStringContainsString('RDJ', $result->text('Node does not exist', false), 'Select contains RDJ');
        $this->assertStringContainsString('Magasins', $result->text('Node does not exist', false), 'Select contains Magasins');
        $this->assertStringContainsString('Groupe:', $result->text('Node does not exist', false), 'label is Groupe');
        $this->assertStringContainsString('Obligatoire / renfort :',$result->text('Node does not exist', false), 'label is Obligatoire / renfort : ');
        $this->assertStringContainsString('Bloquant :',$result->text('Node does not exist', false), 'label is Bloquant : ');
        $this->assertStringContainsString('Quota de SP :',$result->text('Node does not exist', false), 'label is Quota de SP : ');
        $this->assertStringContainsString('Statistiques :',$result->text('Node does not exist', false), 'label is Statistiques: ');
        $this->assertStringContainsString('Compatible télétravail :',$result->text('Node does not exist', false), 'label is Compatible télétravail');

        $this->assertStringContainsString('Activités :',$result->eq(1)->text('Node does not exist', false), 'Activités :');
        $this->assertStringContainsString(' Assistance audiovisuel', $result->eq(1)->text('Node does not exist', false), 'checkBox is Assistance audiovisuel');
        $this->assertStringContainsString(' Assistance autoformation', $result->eq(1)->text('Node does not exist', false), 'checkBox is Assistance autoformation');
        $this->assertStringContainsString(' Communication', $result->eq(1)->text('Node does not exist', false), 'checkBox is Communication');
        $this->assertStringContainsString(' Communication réserve', $result->eq(1)->text('Node does not exist', false), 'checkBox is Communication réserve');
        $this->assertStringContainsString(' Inscription', $result->eq(1)->text('Node does not exist', false), 'checkBox is Inscription');
        $this->assertStringContainsString(' Prêt/retour de document', $result->eq(1)->text('Node does not exist', false), 'checkBox is Prêt/retour de document');
        $this->assertStringContainsString(' Prêt de matériel', $result->eq(1)->text('Node does not exist', false), 'checkBox is Prêt de matériel');
        $this->assertStringContainsString(' Rangement', $result->eq(1)->text('Node does not exist', false), 'checkBox is Rangement');
        $this->assertStringContainsString(' Renseignement', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement');
        $this->assertStringContainsString(' Renseignement bibliographique', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement bibliographique ');
        $this->assertStringContainsString(' Renseignement réserve', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement réserve');
        $this->assertStringContainsString(' Renseignement spécialisé', $result->eq(1)->text('Node does not exist', false), 'checkBox is Renseignement spécialisé');
        $this->assertStringContainsString(' Catégories* :',$result->eq(1)->text('Node does not exist', false), 'label is Nom du post');
        $this->assertStringContainsString(' Catégorie A', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie A ');
        $this->assertStringContainsString(' Catégorie B', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie B');
        $this->assertStringContainsString(' Catégorie C', $result->eq(1)->text('Node does not exist', false), 'checkBox is Catégorie C');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-add"]');
        $this->assertEquals($result->attr('title'),'Ajouter','span edit icon is Ajouter');


        $result = $crawler->filterXPath('//input[@name="obligatoire"]');
        $this->assertEquals($result->attr('value'),'Obligatoire','input obligatoire value is Obligatoire');
        $this->assertEquals($result->eq(1)->attr('value'),'Renfort','input obligatoire value is Obligatoire Renfort');

        $result = $crawler->filterXPath('//input[@name="bloquant"]');
        $this->assertEquals($result->attr('value'),'1','input bloquant value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input bloquant value is 0');

        $result = $crawler->filterXPath('//input[@name="statistiques"]');
        $this->assertEquals($result->attr('value'),'1','input statistiques value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input statistiques value is 0');

        $result = $crawler->filterXPath('//input[@name="quota_sp"]');
        $this->assertEquals($result->attr('value'),'1','input quota_sp value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input quota_sp value is 0');

        $result = $crawler->filterXPath('//input[@name="teleworking"]');
        $this->assertEquals($result->attr('value'),'1','input teleworking value is 1');
        $this->assertEquals($result->eq(1)->attr('value'),'0','input teleworking value is 0');

        $result = $crawler->filterXPath('//input[@class="ui-button"]');
        $this->assertEquals($result->attr('value'),'Valider','input submit value is Valider');

        $result = $crawler->filterXPath('//a[@class="ui-button ui-button-type2"]');
        $this->assertEquals($result->text('Node does not exist', false), 'Annuler','input submit value is Annuler');

        $result = $crawler->filterXPath('//td[@class="noteBasDePage"]');
        $this->assertStringContainsString('* Si aucune catégorie n\'est sélectionnée, les agents de toutes les catégories pourront être placés sur ce poste', $result->text('Node does not exist', false), 'noteBasDePage is ok');
    }

    public function testPositionList(): void
    {
        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(Position::class);

        $this->logInAgent($agent, array(5));

        $position = new Position();
        $position->setName('bureau');
        $position->setGroup('administratif');
        $position->setGroupId('26');
        $position->setMandatory('Renfort');
        $position->setFloor('Mezzanine');
        $position->setActivities(['communication','inscription']);
        $position->setStatistics(1);
        $position->setTeleworking(1);
        $position->setBlocking(0);
        $position->setCategories([]);

        $entityManager->persist($position);
        $entityManager->flush();

        $crawler = $this->client->request('GET', "/position");

        $this->assertSelectorTextContains('h3', 'Liste des postes');

        $result = $crawler->filterXPath('//thead/tr');
        $this->assertStringContainsString('Nom du poste',$result->text('Node does not exist', false), 'check label for name');
        $this->assertStringContainsString('Etage',$result->text('Node does not exist', false), 'check label for Etage');
        $this->assertStringContainsString('Activités',$result->text('Node does not exist', false), 'check label for activités');
        $this->assertStringContainsString('Groupe',$result->text('Node does not exist', false), 'check label for Group');
        $this->assertStringContainsString('Obligatoire/renfort',$result->text('Node does not exist', false), 'check label for Obligatoire');
        $this->assertStringContainsString('Bloquant',$result->text('Node does not exist', false), 'check label for Bloquant');
        $this->assertStringContainsString('Statistiques',$result->text('Node does not exist', false), 'check label for Sttistiques');

        $result = $crawler->filterXPath('//span[@class="pl-icon pl-icon-edit"]');
        $this->assertEquals($result->attr('title'),'Modifier','Edit Icons');

    }
}
