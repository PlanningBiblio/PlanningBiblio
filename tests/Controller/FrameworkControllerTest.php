<?php

use App\Model\Absence;
use App\Model\Agent;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionTabAffectation;
use App\Model\PlanningPositionLines;
use App\Model\PlanningPositionTab;
use App\Model\PlanningPositionTabGroup;
use App\PlanningBiblio\Framework;
use Symfony\Component\DomCrawler\Crawler;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class FrameworkControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testListTable()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );

        $d = date("d");
        $m = date("m");
        $Y = date("Y") + 1;
        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $tab1 = $this->builder->build(
            PlanningPositionTab::class,
            array(
                'tableau' => '1',
                'nom' => 'tab1',
                'site' => '1',
                'supprime' => $date,
            )
        );
        $tab2 = $this->builder->build(
            PlanningPositionTab::class,
            array(
                'tableau' => '1',
                'nom' => 'tab2',
                'site' => '1',
                'supprime' => $date,
            )
        );
        $tab3 = $this->builder->build(
            PlanningPositionTab::class,
            array(
                'tableau' => '1',
                'nom' => 'tab3',
                'site' => '1',
                'supprime' => $date,
            )
        );

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->eq(0)->text(),"Gestion des tableaux");

        $result = $crawler->filterXPath('//h4');
        $this->assertEquals($result->eq(0)->text(),"Liste des tableaux");

        $result = $crawler->filterXPath('//td[@id="td-tableau-1-nom"]');
        $this->assertEquals($result->text(),"Tableau 1");

        $result = $crawler->filterXPath('//select[@id="tableauxSupprimes"]');
        $this->assertStringContainsString("tab1", $result->text());
        $this->assertStringContainsString("tab2", $result->text());
        $this->assertStringContainsString("tab3", $result->text());

        $result = $crawler->filterXPath('//a[@href="/framework/add"]');
        $this->assertStringContainsString("Nouveau tableau", $result->text());

        $result = $crawler->filterXPath('//div[@id="table-list_wrapper"]/div/div[@class="dataTables_length"]/label');
        $this->assertStringContainsString("Afficher",$result->text());
        $this->assertStringContainsString("10",$result->text());
        $this->assertStringContainsString("25",$result->text());
        $this->assertStringContainsString("50",$result->text());
        $this->assertStringContainsString("75",$result->text());
        $this->assertStringContainsString("100",$result->text());
        $this->assertStringContainsString("All",$result->text());

        $result = $crawler->filterXPath('//div[@id="tableaux-listes"]/form/div/div[@class="dt-buttons ui-buttonset"]/button');
        $this->assertEquals($result->eq(0)->text(),"Copier");
        $this->assertEquals($result->eq(1)->text(),"Excel");
        $this->assertEquals($result->eq(2)->text(),"CSV");
        $this->assertEquals($result->eq(3)->text(),"PDF");
        $this->assertEquals($result->eq(4)->text(),"Imprimer");

    }

    public function testListLine()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );


        $db = new \db();
        $db->CSRFToken = '00000';
        $db->insert("lignes", array("nom"=>'ligne 1'));
        $db->insert("lignes", array("nom"=>'ligne 2'));

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(),"Gestion des tableaux");

        $result = $crawler->filterXPath('//h4');
        $this->assertEquals($result->eq(2)->text(),"Lignes de séparation");

        $result = $crawler->filterXPath('//td[@id="td-ligne-5-nom"]');
        $this->assertEquals($result->text(),"ligne 1");

        $result = $crawler->filterXPath('//td[@id="td-ligne-6-nom"]');
        $this->assertEquals($result->text(),"ligne 2");

        $result = $crawler->filterXPath('//div[@id="tableaux-separations"]/p/input');
        $this->assertEquals($result->attr('value'),"Nouvelle ligne");

        $result = $crawler->filterXPath('//div[@id="tableaux-separations"]/div[@id="table-separations_wrapper"]/div/div[@class="dataTables_length"]/label');
        $this->assertStringContainsString("Afficher",$result->text());
        $this->assertStringContainsString("10",$result->text());
        $this->assertStringContainsString("25",$result->text());
        $this->assertStringContainsString("50",$result->text());
        $this->assertStringContainsString("75",$result->text());
        $this->assertStringContainsString("100",$result->text());
        $this->assertStringContainsString("All",$result->text());

        $result = $crawler->filterXPath('//div[@id="tableaux-separations"]/div[@id="table-separations_wrapper"]/div[@class="dt-buttons ui-buttonset"]/button');
        $this->assertEquals($result->eq(0)->text(),"Copier");
        $this->assertEquals($result->eq(1)->text(),"Excel");
        $this->assertEquals($result->eq(2)->text(),"CSV");
        $this->assertEquals($result->eq(3)->text(),"PDF");
        $this->assertEquals($result->eq(4)->text(),"Imprimer");

    }

    public function testListGroup()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );


        $db = new \db();
        $db->CSRFToken = '00000';
        $id = $db->insert("pl_poste_tab", array(
            'nom' => 'tab1',
            'site' => '1',
            )
        );

        $db->update("pl_poste_tab", array("tableau"=>$id), array("nom"=>'tab1'));

        $db = new \db();
        $db->CSRFToken = '00000';
        $db->insert("pl_poste_tab_grp", array(
            'nom' => 'group1',
            'lundi' => $id,
            'mardi' => $id,
            'mercredi' => $id,
            'jeudi' => $id,
            'vendredi' => $id,
            'samedi' => $id,
            )
        );
        $db->insert("pl_poste_tab_grp", array(
            'nom' => 'group2',
            'lundi' => $id,
            'mardi' => $id,
            'mercredi' => $id,
            'jeudi' => $id,
            'vendredi' => $id,
            'samedi' => $id,
            )
        );

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(),"Gestion des tableaux");

        $result = $crawler->filterXPath('//h4');
        $this->assertEquals($result->eq(1)->text(),"Groupes");

        $result = $crawler->filterXPath('//td[@id="td-groupe-1-nom"]');
        $this->assertEquals($result->text(),"group1");

        $result = $crawler->filterXPath('//td[@id="td-groupe-2-nom"]');
        $this->assertEquals($result->text(),"group2");

        $result = $crawler->filterXPath('//div[@id="tableaux-groupes"]/p/input');
        $this->assertEquals($result->attr('value'),"Nouveau groupe");

        $result = $crawler->filterXPath('//div[@id="tableaux-groupes"]/div[@id="table-groups_wrapper"]/div/div[@class="dataTables_length"]/label');
        $this->assertStringContainsString("Afficher",$result->text());
        $this->assertStringContainsString("10",$result->text());
        $this->assertStringContainsString("25",$result->text());
        $this->assertStringContainsString("50",$result->text());
        $this->assertStringContainsString("75",$result->text());
        $this->assertStringContainsString("100",$result->text());
        $this->assertStringContainsString("All",$result->text());

        $result = $crawler->filterXPath('//div[@id="tableaux-groupes"]/div[@id="table-groups_wrapper"]/div[@class="dt-buttons ui-buttonset"]/button');
        $this->assertEquals($result->eq(0)->text(),"Copier");
        $this->assertEquals($result->eq(1)->text(),"Excel");
        $this->assertEquals($result->eq(2)->text(),"CSV");
        $this->assertEquals($result->eq(3)->text(),"PDF");
        $this->assertEquals($result->eq(4)->text(),"Imprimer");
    }

    public function testGroupAdd()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(PlanningPositionTab::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );

        $db = new \db();
        $db->CSRFToken = '00000';
        $id = $db->insert("pl_poste_tab", array(
            'nom' => 'tab1',
            'site' => '1',
            )
        );

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework-group/add");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(),"Nouveau groupe");

        $result = $crawler->filterXPath('//table[@class="tableauFiches"]/tbody/tr/td');
        $this->assertEquals($result->text(),"Nom du groupe");

        $result = $crawler->filterXPath('//td[@style="padding-top:20px;text-align:justify;"]');
        $this->assertEquals($result->text(),"Choisissez les tableaux que vous souhaitez affecter à chacun des jours de la semaine");

        $table_list = $this->getSelectValues('lundi');
        $this->assertCount(2, $table_list);

        $this->assertTrue(in_array(0, $table_list), 'Tab1');
    }

    public function testEditAffectedTable()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );

        $d = date("d");
        $m = date("m");
        $Y = date("Y");
        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $db = new \db();
        $db->CSRFToken = '00000';
        $id = $db->insert("pl_poste_tab", array(
            'nom' => 'tab1',
            'site' => '1',
            )
        );

        $db->update("pl_poste_tab", array("tableau"=>$id), array("nom"=>'tab1'));

        $d = date("d");
        $m = date("m");
        $Y = date("Y");
        $date = \DateTime::createFromFormat("d/m/Y H:i:s", "$d/$m/$Y 00:00:00");

        $tab1_affectation = $this->builder->build(
            PlanningPositionTabAffectation::class,
            array(
                'site' => '1',
                'date' => $date,
                'tableau' => $id,
            )
        );

        $planning_position = $this->builder->build(
            PlanningPosition::class,
            array(
                'perso_id' => $agent->id(),
                'date' => $date,
                'debut' => \DateTime::createFromFormat("H:i:s", "08:00:00"),
                'fin' => \DateTime::createFromFormat("H:i:s", "18:00:00"),
                'absent' => 0,
                'supprime' => 0,
                'grise' => 0,
                'site' => '1',
            )
        );

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework/$id");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(),'Configuration du tableau "tab1"');

        $result = $crawler->filterXPath('//ul[@class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all"]/li');
        $this->assertEquals($result->eq(0)->text(),'Infos générales');
        $this->assertEmpty($result->eq(0)->attr('aria-disabled'));



        $this->assertEquals($result->eq(1)->text(),'Horaires');
        $this->assertEquals($result->eq(1)->attr('aria-disabled'),'true');

        $this->assertEquals($result->eq(2)->text(),'Lignes');
        $this->assertEquals($result->eq(2)->attr('aria-disabled'),'true');
    }

    public function testEditNoAffectedTable()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';
        $this->setParam('Multisites-nombre', 1);

        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(PlanningPosition::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
                'droits' => array(99,100,22)
            )
        );

        $d = date("d")-2;
        $m = date("m");
        $Y = date("Y");
        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $db = new \db();
        $db->CSRFToken = '00000';
        $id = $db->insert("pl_poste_tab", array(
            'nom' => 'tab1',
            'site' => '1',
            )
        );

        $db->update("pl_poste_tab", array("tableau"=>$id), array("nom"=>'tab1'));

        $this->login($agent);

        $crawler = $this->client->request('GET', "/framework/$id");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(),'Configuration du tableau "tab1"');

        $result = $crawler->filterXPath('//ul[@class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all"]/li');
        $this->assertEquals($result->eq(0)->text(),'Infos générales');
        $this->assertEmpty($result->eq(0)->attr('aria-disabled'));

        $this->assertEquals($result->eq(1)->text(),'Horaires');
        $this->assertEmpty($result->eq(1)->attr('aria-disabled'));

        $this->assertEquals($result->eq(2)->text(),'Lignes');
        $this->assertEmpty($result->eq(2)->attr('aria-disabled'));
    }
}
