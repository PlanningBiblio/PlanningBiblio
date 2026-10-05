<?php

use App\Entity\Agent;
use App\Entity\Absence;
use App\Entity\AbsenceDocument;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AbsenceDocumentControllerTest extends PLBWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);
        $builder->delete(AbsenceDocument::class);

    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);
        $builder->delete(AbsenceDocument::class);
    }

    public function testDelete(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $jdoe = $builder->build(Agent::class, [
            'login' => 'jdoe',
        ]);

        $absence = $builder->build(Absence::class, ['perso_id' => $jdoe->getId(), 'groupe' => '']);
        // Use an empty filename so that deleteFile does not try to unlink a non-existing file
        $absenceDocument = $builder->build(AbsenceDocument::class, ['absence_id' => $absence->getId(), 'filename' => '']);

        $this->logInAgent($jdoe, [6, 99, 100]);

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, '#documentsList');
        $this->assertSelectorCount(1, '#documentsList > div');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion fails without CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absences/document/' . $absenceDocument->getId() . '/delete',
            []
        );
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertSelectorCount(1, '#documentsList > div');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absences/document/' . $absenceDocument->getId() . '/delete',
            ['_token' => $token]
        );
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertSelectorCount(0, '#documentsList > div');
    }
}
