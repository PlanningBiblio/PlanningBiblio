<?php

use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AgentControllerCreateLoginTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setParam('ICS-Server3', 0);

        $this->builder->delete(Agent::class);
    }

    public function testCreateLogin(): void
    {
        $this->setUpPantherClient();

        $this->builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(4, 21, 99, 100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $this->login($agent);

        $expectedLogins = array(
            ['firstname.lastname', 'john.doe'],
            ['firstname.lastname', 'john.doe2'],
            ['firstname.lastname', 'john.doe3'],
            ['firstname.lastname', 'john.doe4'],
            ['lastname.firstname', 'doe.john'],
            ['lastname.firstname', 'doe.john2'],
            ['lastname.firstname', 'doe.john3'],
            ['lastname.firstname', 'doe.john4'],
            ['mail', 'johnny.doe@example.com'],
            ['mail', 'johnny.doe2@example.com'],
            ['mail', 'johnny.doe3@example.com'],
            ['mail', 'johnny.doe4@example.com'],
            ['mailPrefix', 'johnny.doe'],
            ['mailPrefix', 'johnny.doe2'],
            ['mailPrefix', 'johnny.doe3'],
            ['mailPrefix', 'johnny.doe4'],
        );

        foreach ($expectedLogins as $elem) {
            // Set config
            $this->setParam('Auth-LoginLayout', $elem[0]);

            // Open the add agent form
            $crawler = $this->client->request('GET', '/agent/add');

            // Fill lastname, firstname and mail
            $crawler->filter('input[id=nom]')->sendKeys('Doe');
            $crawler->filter('input[id=prenom]')->sendKeys('John');
            $crawler->filter('input[id=mail]')->sendKeys('johnny.doe@example.com');

            // Submit the form
            $button = $crawler->filter('.ui-tab-submit');
            $button->click();
            $this->client->waitFor('#tableAgents');

            // Check the record
            $id = $this->entityManager->getRepository(Agent::class)->getMaxId();

            $crawler = $this->client->request('GET', '/agent/' . $id);
            $login = $crawler->filter('#login');
            $this->assertEquals($elem[1], $login->text());
        }
    }
}
