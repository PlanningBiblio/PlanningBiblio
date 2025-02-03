<?php

use App\Model\Agent;
use App\Model\Manager;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AgentControllerLDIFTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testLDIFImport()
    {
        $this->setParam('LDIF-File', __DIR__ . '/../../data/ldif_sample.ldif');
        $this->setParam('LDIF-ID-Attribute', 'uid');
        $this->setParam('LDIF-Matricule', 'supannempid');
        $this->setParam('Multisites-nombre', '1');
        $this->setUpPantherClient();

        $this->builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(4, 21, 99, 100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $this->login($agent);

        // Test if the import LDIF button is displayed
        $crawler = $this->client->request('GET', '/agent');
        $button = $crawler->selectButton('Import LDIF');
        $this->assertTrue($button->count() == 1, 'Import LDIF Button exists');

        // Click the button and test the display of the page "Import LDIF"
        $button->click();
        $crawler = $this->client->refreshCrawler();
        $this->assertSelectorTextContains('h3', 'Importation des agents à partir d\'un fichier LDIF');

        // Search people from the LDIF file (@ to search everybody)
        $crawler->filter('input[name=searchTerm]')->sendKeys('@');
        $button = $crawler->selectButton('Rechercher');
        $button->click();
        $crawler = $this->client->refreshCrawler();

        // Check the table header
        $result = $crawler->filter('#tableAgentImport thead th');
        $this->assertEquals('Nom', $result->eq(1)->text());
        $this->assertEquals('Prénom', $result->eq(2)->text());
        $this->assertEquals('e-mail', $result->eq(3)->text());
        $this->assertEquals('Login', $result->eq(4)->text());
        $this->assertEquals('Matricule', $result->eq(5)->text());

        // Check if 3 people are present in the table
        $result = $crawler->filter('#tableAgentImport tbody tr');
        $count = $result->count();
        $this->assertEquals($count, 3);

        // Check if these people are present Sally Brown, John Doe and Robert Smith
        $result = $crawler->filter('#tableAgentImport tbody td');
        $this->assertEquals('Brown', $result->eq(1)->text());
        $this->assertEquals('Sally', $result->eq(2)->text());
        $this->assertEquals('sbrown2@example.com', $result->eq(3)->text());
        $this->assertEquals('sbrown20', $result->eq(4)->text());
        $this->assertEquals('11111112', $result->eq(5)->text());
        $this->assertEquals('Doe', $result->eq(7)->text());
        $this->assertEquals('John', $result->eq(8)->text());
        $this->assertEquals('john.doe2@example.com', $result->eq(9)->text());
        $this->assertEquals('jdoe', $result->eq(10)->text());
        $this->assertEquals('11111113', $result->eq(11)->text());
        $this->assertEquals('Smith', $result->eq(13)->text());
        $this->assertEquals('Robert', $result->eq(14)->text());
        $this->assertEquals('robert.smith@example.com', $result->eq(15)->text());
        $this->assertEquals('rjsmith', $result->eq(16)->text());
        $this->assertEquals('11111111', $result->eq(17)->text());

        // Control the checkboxes values
        $result = $crawler->filter('input[type=checkbox]');
        $this->assertEquals('sbrown20', $result->eq(1)->extract(array('value'))[0]);
        $this->assertEquals('jdoe', $result->eq(2)->extract(array('value'))[0]);
        $this->assertEquals('rjsmith', $result->eq(3)->extract(array('value'))[0]);

        // Select Sally Brown and John Doe and sumbit the form
        $result->eq(1)->click();
        $result->eq(2)->click();
        $crawler->selectButton('Importer')->click();
        $crawler = $this->client->refreshCrawler();

        // Check if there is only one person left in the table
        $count = $crawler->filter('#tableAgentImport tbody tr')->count();
        $this->assertEquals($count, 1);

        // Check if this person is Robert Smith
        $result = $crawler->filter('#tableAgentImport tbody td');
        $this->assertEquals('Smith', $result->eq(1)->text());
        $this->assertEquals('Robert', $result->eq(2)->text());
        $this->assertEquals('robert.smith@example.com', $result->eq(3)->text());
        $this->assertEquals('rjsmith', $result->eq(4)->text());
        $this->assertEquals('11111111', $result->eq(5)->text());

        // Check if Sally Brown and John Doe are in the agent list
        $crawler = $this->client->request('GET', '/agent');
        $result = $crawler->filter('#tableAgents tbody td');
        $this->assertEquals('Brown', $result->eq(10)->text());
        $this->assertEquals('Sally', $result->eq(11)->text());
        $this->assertEquals(date('d/m/Y'), $result->eq(15)->text());
        $this->assertEquals('Doe', $result->eq(19)->text());
        $this->assertEquals('John', $result->eq(20)->text());
        $this->assertEquals(date('d/m/Y'), $result->eq(24)->text());
  
        // Edit Sally's record
        $editLinks = $crawler->filter('.pl-icon-edit');
        $editLinks->eq(1)->click();
        $crawler = $this->client->refreshCrawler();

        // Check Sally's record
        $this->assertEquals('Brown',
            $crawler->filter('#nom')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('Sally',
            $crawler->filter('#prenom')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('sbrown2@example.com',
            $crawler->filter('#mail')
                ->extract(array('value'))[0]
        );
        $this->assertEquals(date('d/m/Y'),
            $crawler->filter('input[name=arrivee]')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('11111112',
            $crawler->filter('input[name=matricule]')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('sbrown20',
            $crawler->filter('#login')
                ->text()
        );

        // Edit John's record
        $crawler = $this->client->request('GET', '/agent');
        $editLinks = $crawler->filter('.pl-icon-edit');
        $editLinks->eq(2)->click();
        $crawler = $this->client->refreshCrawler();

        // Check John's record
        $this->assertEquals('Doe',
            $crawler->filter('#nom')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('John',
            $crawler->filter('#prenom')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('john.doe2@example.com',
            $crawler->filter('#mail')
                ->extract(array('value'))[0]
        );
        $this->assertEquals(date('d/m/Y'),
            $crawler->filter('input[name=arrivee]')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('11111113',
            $crawler->filter('input[name=matricule]')
                ->extract(array('value'))[0]
        );
        $this->assertEquals('jdoe',
            $crawler->filter('#login')
                ->text()
        );

    }
}
