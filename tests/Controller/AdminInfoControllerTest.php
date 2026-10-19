<?php

use App\Entity\Agent;
use App\Entity\AdminInfo;
use Symfony\Component\DomCrawler\Crawler;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AdminInfoControllerTest extends PLBWebTestCase
{
    public function testAdminInfoList(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, ['login' => 'jdevoe']);
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, [23]);

        $info = new AdminInfo();
        $info->setStart(new DateTimeImmutable('2022-10-05'));
        $info->setEnd(new DateTimeImmutable('2022-10-10'));
        $info->setComment('Test comment');
        $this->entityManager->persist($info);

        $info2 = new AdminInfo();
        $info2->setStart(new DateTimeImmutable('2023-10-05'));
        $info2->setEnd(new DateTimeImmutable('2023-10-10'));
        $info2->setComment('Test comment 2');
        $this->entityManager->persist($info2);

        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/admin/info?start=06/10/2022');
        $rowCount = $crawler->filter('table > tbody > tr')->count();
        $this->assertEquals(2, $rowCount);

        $crawler = $this->client->request('GET', '/admin/info?start=06/10/2023');
        $rowCount = $crawler->filter('table > tbody > tr')->count();
        $this->assertEquals(1, $rowCount);

        $crawler = $this->client->request('GET', '/admin/info?start=01/01/2000&end=06/10/2022');
        $rowCount = $crawler->filter('table > tbody > tr')->count();
        $this->assertEquals(1, $rowCount);

        $crawler = $this->client->request('GET', '/admin/info?start=01/01/2000&end=06/10/2021');
        $rowCount = $crawler->filter('table > tbody > tr')->count();
        $this->assertEquals(0, $rowCount);
    }

    public function testAdd(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, array(23));

        $crawler = $this->client->request('GET', '/admin/info/add');
        $extract_result = $crawler->filter('#form input[name="_token"]')->extract(array('value'));
        $token = $extract_result[0];

        $this->client->request('POST', '/admin/info', array('start' => '05/10/2021', 'end' => '10/10/2021', 'text' => 'salut', '_token' => $token));

        $info = $this->entityManager->getRepository(AdminInfo::class)->findOneBy([
            'debut' => new DateTimeImmutable('2021-10-05'),
            'fin' => new DateTimeImmutable('2021-10-10'),
        ]);

        $this->assertEquals('salut', $info->getComment(), 'info texte is salut');

        $this->assertEquals('2021-10-05', $info->getStart()->format('Y-m-d'), 'debut is 2021-10-05');
        $this->assertEquals('2021-10-10', $info->getEnd()->format('Y-m-d'), 'fin is 2021-10-10');
        $this->assertSame([], $info->getSites(), 'mono-site: message is for all sites');
    }

    public function testNewForm(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(23));

        $this->client->request('GET', '/admin/info/add');

        $this->assertSelectorTextContains('h1', 'Messages d\'information');

        $this->assertSelectorTextContains('h4', 'Ajouter une information');

        $crawler = new Crawler();
        $crawler = $this->client->request('GET', '/admin/info/add');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//a[@class="btn btn-secondary"]');
        $this->assertEquals($class->attr('href'),'/admin/info','button href a>span>Annuler is admin/info');

        $class = $crawler->filterXPath('//input[@class="btn btn-primary"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit button value is Valider');

        $result = $crawler->filterXPath('//input[contains(@class, "start-date")]');
        $this->assertEquals($result->attr('name'),'start','input datepicker name is start');
        $this->assertEquals('', $result->attr('value'), 'start date is empty when adding');

        $result = $crawler->filterXPath('//input[contains(@class, "end-date")]');
        $this->assertEquals($result->attr('name'),'end','input datepicker name is end');
        $this->assertEquals('', $result->attr('value'), 'end date is empty when adding');

        $result = $crawler->filterXPath('//textarea');
        $this->assertEquals($result->attr('name'),'text','textarea name is texte');

        $this->assertSelectorCount(0, 'input[name="sites[]"]', 'mono-site: no site checkbox');
    }

    public function testFormEdit(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, array(23));

        $info = new AdminInfo();
        $info->setStart(new DateTimeImmutable('2022-10-05'));
        $info->setEnd(new DateTimeImmutable('2022-10-10'));
        $info->setComment('salut');

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $id = $info->getId();

        $crawler = $this->client->request('GET', "/admin/info/$id");

        $this->assertSelectorTextContains('h1', 'Messages d\'information');

        $this->assertSelectorTextContains('h4', 'Modifier une information');

        $this->assertSelectorTextContains('textarea', 'salut');

        $result=$crawler->filter('label')->eq(0);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de début','label 1 is Date de début');

        $result=$crawler->filter('label')->eq(1);
        $this->assertEquals($result->text('Node does not exist', false), 'Date de fin','label 2 is Date de fin');

        $result = $crawler->filter('label')->eq(2);
        $this->assertEquals($result->text('Node does not exist', false), 'Texte','label 3 is Texte');

        $class = $crawler->filterXPath('//input[@class="btn btn-primary"]');
        $this->assertEquals($class->attr('value'),'Valider','input submit button value is Valider');

        $class = $crawler->filterXPath('//input[@name="start"]');
        $this->assertEquals($class->attr('value'),'05/10/2022','input submit start is 05/10/2022');

        $class = $crawler->filterXPath('//input[@name="end"]');
        $this->assertEquals($class->attr('value'),'10/10/2022','input submit end is 10/10/2022');

        $class = $crawler->filterXPath('//a[@class="btn btn-danger"]');
        $this->assertEquals($class->attr('href'),"javascript:deleteAdminInfo($id);",'button value is Supprimer');

        $class = $crawler->filterXPath('//a[@class="btn btn-secondary"]');
        $this->assertEquals($class->attr('href'),'/admin/info','button href a>span>Annuler is admin/info');

    }

    public function testDelete(): void
    {
        $entityManager = $this->entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);

        $this->logInAgent($agent, array(23));

        $start = new DateTimeImmutable('+1 day');
        $end = new DateTimeImmutable('+1 month +1 day');

        $info = new AdminInfo();
        $info->setStart($start);
        $info->setEnd($end);
        $info->setComment('to be deleted');

        $entityManager->persist($info);
        $entityManager->flush();

        $id = $info->getId();
        $listUrl = sprintf('/admin/info?start=%s&end=%s', $start->format('d/m/Y'), $end->format('d/m/Y'));

        $crawler = $this->client->request('GET', $listUrl);
        $this->assertSelectorCount(1, '#AdminInfoTable tbody tr');

        // Assert deletion fails without CSRF token
        $this->client->request('POST', '/admin/info/delete', array('id' => $id));
        $this->assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', $listUrl);
        $this->assertSelectorCount(1, '#AdminInfoTable tbody tr');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $this->client->request('POST', '/admin/info/delete', array('_token' => $token, 'id' => $id));
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', $listUrl);
        $this->assertSelectorCount(0, '#AdminInfoTable tbody tr');

        $entityManager->clear();
        $this->assertNull($entityManager->getRepository(AdminInfo::class)->find($id), 'info is deleted');
    }

    protected function tearDown(): void
    {
        // Restore mono-site configuration for the other tests
        if ($this->config) {
            $this->setSites(1);
        }

        parent::tearDown();
    }

    public function testNewFormMultiSites(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $crawler = $this->client->request('GET', '/admin/info/add');

        $this->assertSelectorCount(3, 'input[name="sites[]"]', '3 site checkboxes');
        $this->assertSelectorCount(3, 'input[name="sites[]"]:checked', 'all sites are checked when adding');
        $this->assertSelectorTextContains('#info-sites legend', 'Sites');
        $this->assertSelectorTextContains('label[for="site-2"]', 'Site B');
    }

    public function testAddMultiSitesAllChecked(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $this->postInfo(['1', '2', '3']);

        $info = $this->findInfo();
        $this->assertNotNull($info, 'info is created');
        $this->assertSame([], $info->getSites(), 'all sites checked are stored as [] (all sites)');
    }

    public function testAddMultiSitesSomeChecked(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $this->postInfo(['3', '1']);

        $info = $this->findInfo();
        $this->assertNotNull($info, 'info is created');
        $this->assertSame([1, 3], $info->getSites());
    }

    public function testAddMultiSitesUnknownSiteIsIgnored(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $this->postInfo(['2', '9']);

        $info = $this->findInfo();
        $this->assertSame([2], $info->getSites(), 'unknown site 9 is ignored');
    }

    public function testAddMultiSitesNoneChecked(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $this->postInfo([]);

        $this->assertResponseRedirects('/admin/info/add');
        $this->assertNull($this->findInfo(), 'info is not created without site');
    }

    public function testFormEditMultiSites(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $info = new AdminInfo();
        $info->setStart(new DateTimeImmutable('2022-10-05'));
        $info->setEnd(new DateTimeImmutable('2022-10-10'));
        $info->setComment('salut');
        $info->setSites([2]);
        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/info/' . $info->getId());

        $this->assertSelectorCount(1, 'input[name="sites[]"]:checked', 'only one site is checked');
        $this->assertSelectorExists('#site-2:checked');
        $this->assertSelectorNotExists('#site-1:checked');
        $this->assertSelectorNotExists('#site-3:checked');
    }

    public function testEditMultiSitesNoneCheckedRedirectsToForm(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $info = new AdminInfo();
        $info->setStart(new DateTimeImmutable('2022-10-05'));
        $info->setEnd(new DateTimeImmutable('2022-10-10'));
        $info->setComment('salut');
        $info->setSites([2]);
        $this->entityManager->persist($info);
        $this->entityManager->flush();
        $id = $info->getId();

        $this->postInfo([], $id);

        $this->assertResponseRedirects("/admin/info/$id");

        $this->entityManager->clear();
        $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
        $this->assertSame([2], $info->getSites(), 'sites are unchanged');
    }

    public function testListMultiSites(): void
    {
        $this->setSites(3);
        $this->logInAdmin();

        $info = new AdminInfo();
        $info->setStart(new DateTimeImmutable('2022-10-05'));
        $info->setEnd(new DateTimeImmutable('2022-10-10'));
        $info->setComment('for all sites');
        $this->entityManager->persist($info);

        $info2 = new AdminInfo();
        $info2->setStart(new DateTimeImmutable('2022-10-06'));
        $info2->setEnd(new DateTimeImmutable('2022-10-10'));
        $info2->setComment('for sites B and C');
        $info2->setSites([2, 3]);
        $this->entityManager->persist($info2);

        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/admin/info?start=01/10/2022&end=31/10/2022');

        $this->assertSelectorTextContains('#AdminInfoTable thead', 'Sites');

        $rows = $crawler->filter('#AdminInfoTable tbody tr');
        $this->assertEquals(2, $rows->count());
        $this->assertContains($rows->eq(0)->filter('td')->eq(4)->text(), ['Tous les sites', 'All sites']);
        $this->assertEquals('Site B, Site C', $rows->eq(1)->filter('td')->eq(4)->text());
    }

    public function testEditUnknownIdReturns404(): void
    {
        $this->logInAdmin();

        $this->client->request('GET', '/admin/info/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAddWithInvalidStartDate(): void
    {
        $this->logInAdmin();

        $crawler = $this->client->request('GET', '/admin/info/add');
        $token = $crawler->filter('#form input[name="_token"]')->attr('value');

        $this->client->request('POST', '/admin/info', array('start' => 'invalid', 'end' => '', 'text' => 'salut', '_token' => $token));

        $this->assertResponseRedirects('/admin/info/add');
        $this->assertSame(0, $this->entityManager->getRepository(AdminInfo::class)->count([]), 'info is not created');
    }

    private function setSites(int $number): void
    {
        $this->config->setParam('Multisites-nombre', $number);

        foreach (['A', 'B', 'C'] as $i => $letter) {
            $this->config->setParam('Multisites-site' . ($i + 1), $i < $number && $number > 1 ? "Site $letter" : '');
        }
    }

    private function logInAdmin(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(AdminInfo::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(23));
    }

    private function postInfo(array $sites, ?int $id = null): void
    {
        $crawler = $this->client->request('GET', $id ? "/admin/info/$id" : '/admin/info/add');
        $token = $crawler->filter('#form input[name="_token"]')->attr('value');

        $data = array(
            'id' => $id,
            'start' => '05/10/2021',
            'end' => '10/10/2021',
            'text' => 'multi-sites',
            '_token' => $token,
        );

        if (!empty($sites)) {
            $data['sites'] = $sites;
        }

        $this->client->request('POST', '/admin/info', $data);
    }

    private function findInfo(): ?AdminInfo
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(AdminInfo::class)->findOneBy([
            'debut' => new DateTimeImmutable('2021-10-05'),
            'fin' => new DateTimeImmutable('2021-10-10'),
        ]);
    }
}
