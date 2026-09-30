<?php

use App\Entity\Agent;
use App\Entity\AbsenceBlock;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AbsenceBlockControllerTest extends PLBWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(AbsenceBlock::class);

        $jdoe = $builder->build(Agent::class, [
            'login' => 'jdoe',
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(AbsenceBlock::class);
    }

    public function testDelete(): void
    {
        global $entityManager;

        $this->config->setParam('Absences-blocage', '1');

        $jdoe = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoe']);

        $builder = new FixtureBuilder();

        $absenceBlock = $builder->build(AbsenceBlock::class, []);

        $this->logInAgent($jdoe, [99, 100, 301]);

        $crawler = $this->client->request('GET', '/absence/block');
        $this->assertSelectorCount(1, '#AbsenceBlockTable tbody tr');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion fails without CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absence/block/delete',
            ['id' => $absenceBlock->getId()]
        );
        $this->assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', '/absence/block');
        $this->assertSelectorCount(1, '#AbsenceBlockTable tbody tr');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absence/block/delete',
            ['_token' => $token, 'id' => $absenceBlock->getId()]
        );
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/absence/block');
        $this->assertSelectorCount(0, '#AbsenceBlockTable tbody tr');
    }
}
