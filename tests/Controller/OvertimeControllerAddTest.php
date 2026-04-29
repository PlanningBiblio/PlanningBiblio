<?php

use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class OvertimeControllerAddTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testAdd(): void
    {
        $this->setUpPantherClient();

        $barrrrr = $this->builder->build(Agent::class, array(
            'login' => 'barrrrrrrrrrrrrrrrrrr', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(100)
        ));

        // Login with agent having rights for absences
        $this->login($barrrrr);

        $crawler = $this->client->request('GET', "/overtime");

        $result = $crawler->filterXPath('//h3[@class="noprint"]');
        $this->assertEquals('Heures supplémentaires', $result->text(),'h3 is Récupérations');

        $this->assertSelectorTextContains('h4', 'Liste des demandes d\'heures supplémentaires');

        $result = $crawler->filterXPath('//div[@id="add-overtime-modal"]');
        $this->assertEquals('true', $result->attr('aria-hidden'), 'check aria-hidden');

        $button = $crawler->filterXPath('//button[@id="add-overtime-button"]');
        $button->click();

        $result = $crawler->filterXPath('//div[@id="add-overtime-modal"]');
        $this->assertEquals('true', $result->attr('aria-modal'), 'check aria-modal');

        $result = $crawler->filterXPath('//p');
        $this->assertEquals('Veuillez sélectionner le jour concerné par votre demande et le nombre d\'heures supplémentaires, puis saisir un commentaire.', $result->text());

        $result = $crawler->filterXPath('//form/div/label');
        $this->assertEquals('Date', $result->text(),'Date is a form label');
        $this->assertEquals('Heures', $result->eq(1)->text(),'Heures is a form label');
        $this->assertEquals('Commentaire', $result->eq(2)->text(),'Commentaire is form label');

        $button = $crawler->selectButton('Annuler');
        $button->click();

        $result = $crawler->filterXPath('//div[@id="add-overtime-modal"]');
        $this->assertEquals('true', $result->attr('aria-hidden'), 'check aria-hidden');

    }
}
