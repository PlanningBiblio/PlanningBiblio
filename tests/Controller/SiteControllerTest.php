<?php

use App\Entity\Agent;
use App\Entity\Site;
use App\Entity\SiteMail;
use Tests\PLBWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SiteControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(SiteMail::class);
        $this->builder->delete(Agent::class);
        $this->builder->delete(Site::class);
    }

    public function testIndex(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);

        $crawler = $this->client->request('GET', '/site');

        $this->assertResponseIsSuccessful('The request to access the list of sites must be successful');
        $this->assertSelectorTextContains('h3', 'Liste des sites');
        $this->assertSelectorTextContains('#tableSites', 'Site par défaut');
    }

    public function testAddPage(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);
        $this->client->request('GET', '/site/add');

        $this->assertResponseIsSuccessful('The request to access the add site form must be successful');
    }

    public function testEditPage(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);

        $site = $this->builder->build(Site::class, ['name' => 'Site à Modifier']);

        $this->client->request('GET', '/site/' . $site->getId());

        $this->assertResponseIsSuccessful('The request to edit a site must be successful');
    }

    public function testSaveNewSite(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);

        $this->client->request('POST', '/site', [
            'id' => '',
            'name' => 'Nouveau Site',
            'mail_1' => 'contact@nouveausite.fr',
            'mail_2' => 'support@nouveausite.fr',
        ]);

        $this->assertResponseRedirects('/site');

        $this->entityManager->clear();

        $site = $this->entityManager->getRepository(Site::class)->findOneBy(['name' => 'Nouveau Site']);
        $this->assertNotNull($site, 'The site should have been created in the database');

        $mails = $this->entityManager->getRepository(SiteMail::class)->findBy(['site' => $site]);
        $this->assertCount(2, $mails, 'The site should have 2 associated emails');
    }

    public function testSaveExistingSite(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);

        $site = $this->builder->build(Site::class, ['name' => 'Ancien Nom']);

        $this->client->request('POST', '/site', [
            'id' => $site->getId(),
            'name' => 'Nom Modifié',
            'mail_1' => 'newmail@site.com',
        ]);

        $this->assertResponseRedirects('/site');

        $this->entityManager->clear();
        $updatedSite = $this->entityManager->getRepository(Site::class)->find($site->getId());
        $this->assertEquals('Nom Modifié', $updatedSite->getName());
    }

    public function testDeleteDefaultSiteForbidden(): void
    {
        $kboivin = $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'nom' => 'Boivin',
            'prenom' => 'Karel',
            'droits' => [20, 99, 100]
        ]);

        $this->logInAgent($kboivin, [20, 99, 100]);

        $site1 = $this->entityManager->getRepository(Site::class)->find(1);
        if (!$site1) {
            $this->builder->build(Site::class, ['name' => 'Site par défaut']);
        }

        $this->client->request('DELETE', '/site/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}