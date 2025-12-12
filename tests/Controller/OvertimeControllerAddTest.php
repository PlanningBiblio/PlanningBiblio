<?php

use App\Model\Agent;
use App\Model\Manager;
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

        $result = $crawler->filterXPath('//body/div[@class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: none',$result->attr('style'),'check display: none');

        $button = $crawler->filterXPath('//button[@id="dialog-button"]');
        $button->click();

        $result = $crawler->filterXPath('//body/div[@class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: block',$result->attr('style'),'check display: block');

        $result = $crawler->filterXPath('//p[@class="validateTips"]');
        $this->assertEquals('Veuillez sélectionner le jour concerné par votre demande et le nombre d\'heures supplémentaires et un saisir un commentaire.', $result->text());

        $result = $crawler->filterXPath('//table[@class="tableauFiches"]/tbody/tr/td/label');
        $this->assertEquals('Date', $result->text(),'Date is a form label');
        $this->assertEquals('Heures', $result->eq(1)->text(),'Heures is a form label');
        $this->assertEquals('Commentaire', $result->eq(2)->text(),'Commentaire is form label');

        $button = $crawler->selectButton('Enregistrer');
        $button->click();

        $result = $crawler->filterXPath('//body/div[@class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: block', $result->attr('style'),'check display: none');

    }
}
