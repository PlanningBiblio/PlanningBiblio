<?php

use App\Model\Agent;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase; 
use Tests\FixtureBuilder;

class AgentControllerTest extends PLBWebTestCase
{
    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';

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

    public function testAddPost()
    {
        global $entityManager;

        $GLOBALS['config']['Multisites-nombre'] = 1;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean',
            'mail' => 'jdupont@mail.fr', 'droits' => array(21,100,99,4)
        ));

        $this->logInAgent($agent, $agent->droits());

        $client = static::createClient();
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';

        $d1 = date('d') - 3;
        $d2 = date('d') + 3;
        $m = date('m');
        $Y = date('Y');
        $start = "$Y-$m-$d1";
        $end = "$Y-$m-$d2";

        $client->request(
            'POST',
            '/agent',
            array(
                'nom' => 'Boivin',
                'prenom' => 'Karel',
                'CSRFToken' => "00000",
                'login' => 'karel.boivin',
                'droits' => array(100,99),
                'mail' => 'kboivin@mail.fr',
                'statut' => 'cbjncdk',
                'categorie' => 'dadczz',
                'service' => 'zedcscq',
                'arrivee' => $start,
                'depart' => $end,
                'postes' => '',
                'action' => 'ajout',
                'actif' => 1,
                'commentaires' => '',
                'last_login' => '',
                'heures_hebdo' => '',
                'heures_travail' => '',
                'sites' => json_encode(["2", "4"]),
                'temps' => '',
                'informations' => '',
                'recup' => '',
                'supprime' => '',
                'mails_responsables' => '',
                'matricule' => '',
                'code_ics' => '',
                'url_ics' => '',
                'check_ics' => '',
                'check_hamac' => '',
                'conges_credit' => '',
                'conges_reliquat' => '',
                'conges_anticipation' => '',
                'comp_time' => '',
                'conges_annuel' => '',
                'managers' => '',
                'managed' => '',
            )
        );

        $info = $entityManager->getRepository(Agent::class)->findOneBy(array('nom' => 'Boivin'));

        $this->assertEquals('karel.boivin', $info->login(), 'login');
        $this->assertEquals('Boivin', $info->nom(), 'nom');
        $this->assertEquals('Karel', $info->prenom(), 'prenom');
        $this->assertEquals('kboivin@mail.fr', $info->mail(), 'mail');
        $this->assertEquals("$d1/$m/$Y", $info->arrivee()->format("d/m/Y"), 'arrivee');
        $this->assertEquals("$d2/$m/$Y", $info->depart()->format("d/m/Y"), 'depart');
    }

    public function testAddFormElement() {
        $GLOBALS['config']['Multisites-nombre'] = 1;
        $GLOBALS['config']['Granularite'] = 30;
        $GLOBALS['config']['LDAP-Host'] = '';
        $GLOBALS['config']['LDAP-Suffix'] = '';


        $client = static::createClient();

        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["1"]', 'droits' => array(21,100,99,4)
        ));

        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//table[@id="tableAgents"]/thead');
        $this->assertStringContainsString('Nom', $result->text());
        $this->assertStringContainsString('Prénom', $result->text());
        $this->assertStringContainsString('Heures', $result->text());
        $this->assertStringContainsString('Statut', $result->text());
        $this->assertStringContainsString('Service', $result->text());
        $this->assertStringContainsString('Arrivée', $result->text());
        $this->assertStringContainsString('Départ', $result->text());
        $this->assertStringContainsString('Accès', $result->text());

        $this->assertStringNotContainsString('Sites', $result->text());

        $this->assertDirectoryDoesNotExist('//input[@value="Import LDAP"]');

        $result = $crawler->filterXPath('//style[@class="margin-bottom:10px;"]/tr/td')->eq(5);

        $this->assertEmpty($result);

        //test multisites
        $GLOBALS['config']['Multisites-nombre'] = 4;
        $GLOBALS['config']['Grannularite'] = 1;
        $GLOBALS['config']['LDAP-Host'] = '';
        $GLOBALS['config']['LDAP-Suffix'] = '';

        $crawler = $client->request('GET', '/agent');

        $this->assertDirectoryDoesNotExist('//input[@value="Import LDAP"]');

        $result = $crawler->filterXPath('//table[@id="tableAgents"]/thead');

        $this->assertStringContainsString('Sites', $result->text());

        //test LDAP host and suffix
        $GLOBALS['config']['Multisites-nombre'] = 4;
        $GLOBALS['config']['Grannularite'] = 1;
        $GLOBALS['config']['LDAP-Host'] = '192.168.1.100';
        $GLOBALS['config']['LDAP-Suffix'] = 'dn: dc=my-domain,dc=com objectclass: dcObject objectclass: organization';

        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//input[@class="ui-button ui-button-type2"]');

        $this->assertEquals('Import LDAP', $result->attr('value'));

        //test Granularite

        $GLOBALS['config']['Granularite'] = 30;

        $result = $crawler->filterXPath('//select[@name="heures_travail"]/option');
        $this->assertEquals('1h00', $result->eq(2)->text());
        $this->assertEquals('1h30', $result->eq(3)->text());

        $GLOBALS['config']['Granularite'] = 5;

        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//select[@name="heures_travail"]/option');
        $this->assertEquals('1h00', $result->eq(2)->text());
        $this->assertEquals('1h05', $result->eq(3)->text());

        $GLOBALS['config']['Granularite'] = 15;

        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//select[@name="heures_travail"]/option');
        $this->assertEquals('1h00', $result->eq(2)->text());
        $this->assertEquals('1h15', $result->eq(3)->text());
    }

    public function testEditFormElement() {

        $GLOBALS['config']['Multisites-nombre'] = 2;
        $GLOBALS['config']['Granularite'] = 30;
        $GLOBALS['config']['LDAP-Host'] = '';
        $GLOBALS['config']['LDAP-Suffix'] = '';
        $GLOBALS['config']['Conges-Enable'] = 1;

        $client = static::createClient();

        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean',
            'sites' => '["1"]', 'droits' => array(100,99)
        ));

        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["1"]', 'droits' => array(21,100,99,4)
        ));

        $id = $jdupont->id();

        //$this->login($kboivin);

        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', "/agent/$id");

        $this->assertSelectorTextContains('h3', 'Dupont Jean');

        ///////INFOS GENERALES/////////

        $result = $crawler->filterXPath('//div[@class="ui-tabs"]/ul/li');

        $this->assertEquals('Infos générales', $result->eq(0)->text());
        $this->assertEquals('Activités', $result->eq(1)->text());
        $this->assertEquals('Heures de présence', $result->eq(2)->text());
        $this->assertEquals('Congés', $result->eq(3)->text());
        $this->assertEquals('Droits d\'accès', $result->eq(4)->text());
        $this->assertEquals('Annuler', $result->eq(5)->text());
        $this->assertEquals('Valider', $result->eq(6)->text());

        $result = $crawler->filterXPath('//table[@style="width:90%;"]');

        $this->assertStringContainsString('Nom :', $result->text());
        $this->assertStringContainsString('Prénom :', $result->text());
        $this->assertStringContainsString('E-mail :', $result->text());
        $this->assertStringContainsString('Statut :', $result->text());
        $this->assertStringContainsString('Contrat :', $result->text());
        $this->assertStringContainsString('Service de rattachement:', $result->text());
        $this->assertStringContainsString('Heures de service public par semaine:', $result->text());
        $this->assertStringContainsString('Heures de travail par semaine:', $result->text());
        $this->assertStringContainsString('Service public / Administratif :', $result->text());
        $this->assertStringContainsString('Sites :', $result->text());
        $this->assertStringContainsString('Date d\'arrivée', $result->text());
        $this->assertStringContainsString('Date de départ', $result->text());
        $this->assertStringContainsString('Matricule :', $result->text());
        $this->assertStringContainsString('E-mails des responsables :', $result->text());
        $this->assertStringContainsString('Informations :', $result->text());
        $this->assertStringContainsString('Login :', $result->text());

        $result = $crawler->filterXPath('//input[@name="nom"]');
        $this->assertEquals('Dupont', $result->attr('value'));

        $result = $crawler->filterXPath('//input[@name="prenom"]');
        $this->assertEquals('Jean', $result->attr('value'));

        $result = $crawler->filterXPath('//input[@name="sites[]"]')->eq(0);
        $this->assertNotEmpty($result->attr('checked'));

        $result = $crawler->filterXPath('//input[@name="sites[]"]')->eq(1);
        $this->assertEmpty($result->attr('checked'));

        $result = $crawler->filterXPath('//span[@id="login"]');
        $this->assertEquals($result->text(), 'jdupont');

        ///////ACTIVITES/////////

        $result = $crawler->filterXPath('//b');
        $this->assertEquals('Activités disponibles', $result->text());

        $this->assertEquals('Activités attribuées', $result->eq(1)->text());

        $result = $crawler->filterXPath('//div[@id="dispo_div"]');
        $this->assertStringContainsString('Assistance audiovisuel', $result->text());

        $result = $crawler->filterXPath('//td[@style="text-align:center;padding-top:100px;"]/input[@type="button"]');
        $this->assertEquals('Attribuer >>', $result->attr('value'));
        $this->assertEquals('Attribuer Tout >>', $result->eq(1)->attr('value'));
        $this->assertEquals('<< Supprimer', $result->eq(2)->attr('value'));
        $this->assertEquals('<< Supprimer Tout', $result->eq(3)->attr('value'));

        ///////HdP/////////

        $result = $crawler->filterXPath('//div[@id="temps"]');

        $this->assertStringContainsString('Heure d\'arrivée', $result->text());
        $this->assertStringContainsString('Début de pause', $result->text());
        $this->assertStringContainsString('Fin de pause', $result->text());
        $this->assertStringContainsString('Heure de départ', $result->text());
        $this->assertStringContainsString('Site', $result->text());
        $this->assertStringContainsString('Temps', $result->text());

        $this->assertStringContainsString('Lundi', $result->text());
        $this->assertStringContainsString('Mardi', $result->text());
        $this->assertStringContainsString('Mercredi', $result->text());
        $this->assertStringContainsString('Jeudi', $result->text());
        $this->assertStringContainsString('Vendredi', $result->text());
        $this->assertStringContainsString('Samedi', $result->text());

        //////Congés/////////

        $result = $crawler->filterXPath('//div[@id="conges"]');

        $this->assertStringContainsString('Nombre d\'heures de congés par an :', $result->text());
        $this->assertStringContainsString('Crédit d\'heures de congés actuel :', $result->text());
        $this->assertStringContainsString('Reliquat de congés :', $result->text());
        $this->assertStringContainsString('Solde débiteur :', $result->text());
        $this->assertStringContainsString('Récupérations :', $result->text());

        //////Rights/////////

        $result = $crawler->filterXPath('//div[@id="access"]/h3');

        $this->assertEquals('Absences', $result->eq(1)->text());
        $this->assertEquals('Agendas', $result->eq(2)->text());
        $this->assertEquals('Agents', $result->eq(3)->text());
        $this->assertEquals('Planning', $result->eq(4)->text());
        $this->assertEquals('Postes', $result->eq(5)->text());
        $this->assertEquals('Statistiques', $result->eq(6)->text());
        $this->assertEquals('Divers', $result->eq(7)->text());

        $result = $crawler->filterXPath('//div[@id="access"]/table/tbody/tr/td/h3');
        $this->assertEquals('Absences', $result->eq(0)->text());
        $this->assertEquals('Congés', $result->eq(1)->text());
        $this->assertEquals('Planning', $result->eq(2)->text());

    }

}