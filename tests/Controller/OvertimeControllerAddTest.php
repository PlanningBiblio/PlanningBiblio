<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class OvertimeControllerAddTest extends PLBWebTestCase
{
    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

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

    public function testAdd()
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

        $result = $crawler->filterXPath('//body/div[@class="popup-background ui-dialog ui-widget ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: none',$result->attr('style'),'check display: none');

        $button = $crawler->filterXPath('//button[@id="dialog-button"]');
        $button->click();

        $result = $crawler->filterXPath('//body/div[@class="popup-background ui-dialog ui-widget ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: block',$result->attr('style'),'check display: block');

        $result = $crawler->filterXPath('//p[@class="validateTips"]');
        $this->assertEquals('Veuillez sélectionner le jour concerné par votre demande et le nombre d\'heures supplémentaires et un saisir un commentaire.', $result->text());

        $result = $crawler->filterXPath('//table[@class="tableauFiches"]/tbody/tr/td/label');
        $this->assertEquals('Date', $result->text(),'Date is a form label');
        $this->assertEquals('Heures', $result->eq(1)->text(),'Heures is a form label');
        $this->assertEquals('Commentaire', $result->eq(2)->text(),'Commentaire is form label');

        $button = $crawler->selectButton('Enregistrer');
        $button->click();

        $result = $crawler->filterXPath('//body/div[@class="popup-background ui-dialog ui-widget ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable"]');
        $this->assertStringContainsString('display: block', $result->attr('style'),'check display: none');

    }
}
