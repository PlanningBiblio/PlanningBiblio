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

        $start = date('d/m/Y', strtotime(' -3 day'));
        $end = date('d/m/Y', strtotime(' +3 day'));

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
                'mailsResponsables' => '',
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
        $this->assertEquals($start, $info->arrivee()->format("d/m/Y"), 'arrivee');
        $this->assertEquals($end, $info->depart()->format("d/m/Y"), 'depart');
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
        $this->assertStringContainsString('Nom', $result->text(null,false));
        $this->assertStringContainsString('Prénom', $result->text(null,false));
        $this->assertStringContainsString('Heures', $result->text(null,false));
        $this->assertStringContainsString('Statut', $result->text(null,false));
        $this->assertStringContainsString('Service', $result->text(null,false));
        $this->assertStringContainsString('Arrivée', $result->text(null,false));
        $this->assertStringContainsString('Départ', $result->text(null,false));
        $this->assertStringContainsString('Accès', $result->text(null,false));

        $this->assertStringNotContainsString('Sites', $result->text(null,false));

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

        $this->assertStringContainsString('Sites', $result->text(null,false));

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
        $this->assertEquals('1h00', $result->eq(2)->text(null,false));
        $this->assertEquals('1h30', $result->eq(3)->text(null,false));

        $GLOBALS['config']['Granularite'] = 5;

        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//select[@name="heures_travail"]/option');
        $this->assertEquals('1h00', $result->eq(2)->text(null,false));
        $this->assertEquals('1h05', $result->eq(3)->text(null,false));

        $GLOBALS['config']['Granularite'] = 15;

        $crawler = $client->request('GET', '/agent');

        $result = $crawler->filterXPath('//select[@name="heures_travail"]/option');
        $this->assertEquals('1h00', $result->eq(2)->text(null,false));
        $this->assertEquals('1h15', $result->eq(3)->text(null,false));
    }

    public function testEditFormElement() {

        $GLOBALS['config']['Multisites-nombre'] = 2;
        $GLOBALS['config']['Granularite'] = 30;
        $GLOBALS['config']['LDAP-Host'] = '';
        $GLOBALS['config']['LDAP-Suffix'] = '';
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['PlanningHebdo'] = 0;

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

        $this->assertEquals('Infos générales', $result->eq(0)->text(null,false));
        $this->assertEquals('Activités', $result->eq(1)->text(null,false));
        $this->assertEquals('Heures de présence', $result->eq(2)->text(null,false));
        $this->assertEquals('Congés', $result->eq(3)->text(null,false));
        $this->assertEquals('Droits d\'accès', $result->eq(4)->text(null,false));
        $this->assertEquals('Annuler', $result->eq(5)->text(null,false));
        $this->assertEquals('Valider', $result->eq(6)->text(null,false));

        $result = $crawler->filterXPath('//table[@style="width:90%;"]');

        $this->assertStringContainsString('Nom :', $result->text(null,false));
        $this->assertStringContainsString('Prénom :', $result->text(null,false));
        $this->assertStringContainsString('E-mail :', $result->text(null,false));
        $this->assertStringContainsString('Statut :', $result->text(null,false));
        $this->assertStringContainsString('Contrat :', $result->text(null,false));
        $this->assertStringContainsString('Service de rattachement:', $result->text(null,false));
        $this->assertStringContainsString('Heures de service public par semaine:', $result->text(null,false));
        $this->assertStringContainsString('Heures de travail par semaine:', $result->text(null,false));
        $this->assertStringContainsString('Service public / Administratif :', $result->text(null,false));
        $this->assertStringContainsString('Sites :', $result->text(null,false));
        $this->assertStringContainsString('Date d\'arrivée', $result->text(null,false));
        $this->assertStringContainsString('Date de départ', $result->text(null,false));
        $this->assertStringContainsString('Matricule :', $result->text(null,false));
        $this->assertStringContainsString('E-mails des responsables :', $result->text(null,false));
        $this->assertStringContainsString('Informations :', $result->text(null,false));
        $this->assertStringContainsString('Login :', $result->text(null,false));

        $result = $crawler->filterXPath('//input[@name="nom"]');
        $this->assertEquals('Dupont', $result->attr('value'));

        $result = $crawler->filterXPath('//input[@name="prenom"]');
        $this->assertEquals('Jean', $result->attr('value'));

        $result = $crawler->filterXPath('//input[@name="sites[]"]')->eq(0);
        $this->assertNotEmpty($result->attr('checked'));

        $result = $crawler->filterXPath('//input[@name="sites[]"]')->eq(1);
        $this->assertEmpty($result->attr('checked'));

        $result = $crawler->filterXPath('//span[@id="login"]');
        $this->assertEquals($result->text(null,false), 'jdupont');

        ///////ACTIVITES/////////

        $result = $crawler->filterXPath('//b');
        $this->assertEquals('Activités disponibles', $result->text(null,false));

        $this->assertEquals('Activités attribuées', $result->eq(1)->text(null,false));

        $result = $crawler->filterXPath('//div[@id="dispo_div"]');
        $this->assertStringContainsString('Assistance audiovisuel', $result->text(null,false));

        $result = $crawler->filterXPath('//td[@style="text-align:center;padding-top:100px;"]/input[@type="button"]');
        $this->assertEquals('Attribuer >>', $result->attr('value'));
        $this->assertEquals('Attribuer Tout >>', $result->eq(1)->attr('value'));
        $this->assertEquals('<< Supprimer', $result->eq(2)->attr('value'));
        $this->assertEquals('<< Supprimer Tout', $result->eq(3)->attr('value'));

        ///////HdP/////////

        $result = $crawler->filterXPath('//div[@id="temps"]');

        $this->assertStringContainsString('Heure d\'arrivée', $result->text(null,false));
        $this->assertStringContainsString('Début de pause', $result->text(null,false));
        $this->assertStringContainsString('Fin de pause', $result->text(null,false));
        $this->assertStringContainsString('Heure de départ', $result->text(null,false));
        $this->assertStringContainsString('Site', $result->text(null,false));
        $this->assertStringContainsString('Temps', $result->text(null,false));

        $this->assertStringContainsString('Lundi', $result->text(null,false));
        $this->assertStringContainsString('Mardi', $result->text(null,false));
        $this->assertStringContainsString('Mercredi', $result->text(null,false));
        $this->assertStringContainsString('Jeudi', $result->text(null,false));
        $this->assertStringContainsString('Vendredi', $result->text(null,false));
        $this->assertStringContainsString('Samedi', $result->text(null,false));

        //////Congés/////////

        $result = $crawler->filterXPath('//div[@id="conges"]');

        $this->assertStringContainsString('Nombre d\'heures de congés par an :', $result->text(null,false));
        $this->assertStringContainsString('Crédit d\'heures de congés actuel :', $result->text(null,false));
        $this->assertStringContainsString('Reliquat de congés :', $result->text(null,false));
        $this->assertStringContainsString('Solde débiteur :', $result->text(null,false));
        $this->assertStringContainsString('Récupérations :', $result->text(null,false));

        //////Rights/////////

        $result = $crawler->filterXPath('//div[@id="access"]/h3');

        $this->assertEquals('Absences', $result->eq(1)->text(null,false));
        $this->assertEquals('Agendas', $result->eq(2)->text(null,false));
        $this->assertEquals('Agents', $result->eq(3)->text(null,false));
        $this->assertEquals('Planning', $result->eq(4)->text(null,false));
        $this->assertEquals('Postes', $result->eq(5)->text(null,false));
        $this->assertEquals('Statistiques', $result->eq(6)->text(null,false));
        $this->assertEquals('Divers', $result->eq(7)->text(null,false));

        $result = $crawler->filterXPath('//div[@id="access"]/table/tbody/tr/td/h3');
        $this->assertEquals('Absences', $result->eq(0)->text(null,false));
        $this->assertEquals('Congés', $result->eq(1)->text(null,false));
        $this->assertEquals('Planning', $result->eq(2)->text(null,false));

        $result = $crawler->filterXPath("//div[@id='access']");
        $this->assertStringContainsString('Modifier ses propres absences', $result->text(null,false));
        $this->assertStringContainsString('Enregistrement d\'absences pour plusieurs agents', $result->text(null,false));
        $this->assertStringContainsString('Gestion des absences, validation niveau 1', $result->text(null,false));
        $this->assertStringContainsString('Gestion des absences, pièces justificatives', $result->text(null,false));
        $this->assertStringContainsString('Voir les agendas de tous', $result->text(null,false));
        $this->assertStringContainsString('Voir les fiches des agents', $result->text(null,false));
        $this->assertStringContainsString('Gestion des agents', $result->text(null,false));
        $this->assertStringContainsString('Gestion des congés, validation niveau 1', $result->text(null,false));
        $this->assertStringContainsString('Gestion des congés, validation niveau 2', $result->text(null,false));
        $this->assertStringContainsString('Modification des plannings', $result->text(null,false));
        $this->assertStringContainsString('Griser les cellules des plannings', $result->text(null,false));
        $this->assertStringContainsString('Modification des commentaires des plannings', $result->text(null,false));
        $this->assertStringContainsString('Configuration des tableaux', $result->text(null,false));
        $this->assertStringContainsString('Gestion des postes', $result->text(null,false));
        $this->assertStringContainsString('Accès aux statistiques', $result->text(null,false));
        $this->assertStringContainsString('Accès aux statistiques Présents / Absents', $result->text(null,false));
        $this->assertStringContainsString('Gestion des jours fériés', $result->text(null,false));
        $this->assertStringContainsString('Informations', $result->text(null,false));

    }

}
