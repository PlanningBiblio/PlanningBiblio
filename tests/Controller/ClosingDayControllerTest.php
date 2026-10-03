<?php

use App\Entity\Agent;
use App\Entity\ClosingDay;
use Doctrine\ORM\EntityManagerInterface;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class ClosingDayControllerTest extends PLBWebTestCase
{
    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(ClosingDay::class);
    }

    public function testListClosingDay(): void
    {
        $this->builder->delete(Agent::class);
        $this->builder->delete(ClosingDay::class);

        $client = static::createClient();

        $agent = $this->builder->build(Agent::class, ['login' => 'agent_test']);
        $this->logInAgent($agent, [99, 25, 100]);

        $date1 = new DateTime('+3 days');
        $date2 = new DateTime('+6 days');

        $year1 = (date('n') < 9) ? (date('Y') - 1) . '-' . date('Y') : date('Y') . '-' . (date('Y') + 1);
        $year2 = (date('n') < 9) ? date('Y') . '-' . (date('Y') + 1) : (date('Y') + 1) . '-' . (date('Y') + 2);

        $closingDay1 = $this->builder->build(ClosingDay::class, [
            'annee' => $year1,
            'jour' => $date1,
            'nom' => 'closingDay1',
            'fermeture' => '0',
            'ferie' => '1',
            'commentaire' => 'test closing day',
        ]);

        $id1 = $closingDay1->getId();

        $closingDay2 = $this->builder->build(ClosingDay::class, [
            'annee' => $year1,
            'jour' => $date2,
            'nom' => 'closingDay2',
            'fermeture' => '0',
            'ferie' => '1',
            'commentaire' => 'test closing day 2',
        ]);

        $id2 = $closingDay2->getId();

        $crawler = $client->request('GET', '/closingday');

        $result = $crawler->filterXPath('//h1');
        $this->assertEquals('Jours fériés et jours de fermeture', $result->text('Node does not exist', false));

        $result = $crawler->filterXPath('//form[@name="form1"]');
        $this->assertStringContainsString("Sélectionnez l'année à paramétrer", $result->text('Node does not exist', false));
        $this->assertStringContainsString($year1, $result->text('Node does not exist', false));
        $this->assertStringContainsString($year2, $result->text('Node does not exist', false));

        $result = $crawler->filterXPath("//input[@value='closingDay1']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='closingDay2']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='test closing day']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='test closing day 2']");
        $this->assertNotEmpty($result);

        $result = $crawler->filterXPath("//input[@value='Valider']");
        $this->assertNotEmpty($result);
    }

    public function testUpdateClosingDay(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->builder->delete(Agent::class);
        $this->builder->delete(ClosingDay::class);

        $this->setUpPantherClient();

        $agent = $this->builder->build(Agent::class, ['login' => 'agent_test', 'droits' => [99, 25, 100]]);
        $this->login($agent);

        $year = (date('n') < 9) ? (date('Y') - 1) . '-' . date('Y') : date('Y') . '-' . (date('Y') + 1);
        $year1 = (date('n') < 9) ? (date('Y') - 1) : date('Y');

        $crawler = $this->client->request('GET', '/closingday');
        $this->client->waitFor('.btn-primary', 5);

        $checkbox = $this->client->getCrawler()->filterXPath('(//input[@type="text" and @value="Pâques"]/ancestor::tr//input[@type="checkbox"])[1]');
        $checkbox->click();
        $checkbox = $this->client->getCrawler()->filterXPath('(//input[@type="text" and @value="Pâques"]/ancestor::tr//input[@type="checkbox"])[2]');
        $checkbox->click();
        $checkbox = $this->client->getCrawler()->filterXPath('(//input[@type="text" and @value="Fête du travail"]/ancestor::tr//input[@type="checkbox"])[2]');
        $checkbox->click();
        $checkbox = $this->client->getCrawler()->filterXPath('(//input[@type="text" and @value="Lundi Pentecôte"]/ancestor::tr//input[@type="checkbox"])[1]');
        $checkbox->click();

        $this->client->getCrawler()->filter('.btn-primary')->click();

        $holidays = $entityManager->getRepository(ClosingDay::class)->findBy(['annee' => $year], ['jour' => 'ASC']);

        $this->assertCount(13, $holidays);
        $this->assertEquals($year, $holidays[0]->getYear());
        $this->assertEquals(new DateTime($year1 . '-11-01'), $holidays[0]->getDate());
        $this->assertEquals('La Toussaint', $holidays[0]->getName());
        $this->assertEquals('Ajouté automatiquement', $holidays[0]->getComment());
        $this->assertTrue($holidays[0]->isPublicHoliday());
        $this->assertFalse($holidays[0]->isClosed());
        $this->assertFalse($holidays[4]->isPublicHoliday());
        $this->assertTrue($holidays[4]->isClosed());
        $this->assertTrue($holidays[6]->isPublicHoliday());
        $this->assertTrue($holidays[6]->isClosed());
        $this->assertFalse($holidays[10]->isPublicHoliday());
        $this->assertFalse($holidays[10]->isClosed());
    }

    public function testUpdateClosingDayWrongCSRF(): void
    {
        $this->builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, ['login' => 'agent_test', 'droits' => [99, 25, 100]]);
        $this->logInAgent($agent, $agent->getACL());

        $crawler = $this->client->request('POST', '/closingday', ['_token' => 'fake_token']);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302, 'Wrong CSRF Token returns 302');
    }
}
