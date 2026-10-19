<?php

namespace App\Tests\Repository;

use App\Entity\AdminInfo;
use App\Repository\AdminInfoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdminInfoRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?AdminInfoRepository $repository = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** @var AdminInfoRepository $repository */
        $repository = $this->entityManager->getRepository(AdminInfo::class);
        $this->repository = $repository;

        $this->deleteAll();

        $this->createInfo('2026-10-05', '2026-10-10', 'All sites', []);
        $this->createInfo('2026-10-05', '2026-10-10', 'Site 1', [1]);
        $this->createInfo('2026-10-05', '2026-10-10', 'Sites 2 and 3', [2, 3]);
        $this->createInfo('2026-10-12', '2026-10-12', 'Site 2 next week', [2]);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    protected function tearDown(): void
    {
        $this->deleteAll();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repository = null;

        parent::tearDown();
    }

    public function testWithoutSiteReturnsAllMessages(): void
    {
        $result = $this->repository->findByDateRange(new DateTime('2026-10-06'));

        $this->assertSame(['All sites', 'Site 1', 'Sites 2 and 3'], $this->texts($result));
    }

    public function testFilterBySite(): void
    {
        $date = new DateTime('2026-10-06');

        $this->assertSame(['All sites', 'Site 1'], $this->texts($this->repository->findByDateRange($date, null, 1)));
        $this->assertSame(['All sites', 'Sites 2 and 3'], $this->texts($this->repository->findByDateRange($date, null, 2)));
        $this->assertSame(['All sites', 'Sites 2 and 3'], $this->texts($this->repository->findByDateRange($date, null, 3)));
        $this->assertSame(['All sites'], $this->texts($this->repository->findByDateRange($date, null, 4)), 'a site created later gets the messages for all sites');
    }

    public function testFilterBySiteOnWeekRange(): void
    {
        $start = new DateTime('2026-10-05');
        $end = new DateTime('2026-10-11');

        $this->assertSame(['All sites', 'Sites 2 and 3'], $this->texts($this->repository->findByDateRange($start, $end, 2)));

        $start = new DateTime('2026-10-12');
        $end = new DateTime('2026-10-18');

        $this->assertSame(['Site 2 next week'], $this->texts($this->repository->findByDateRange($start, $end, 2)));
        $this->assertSame([], $this->texts($this->repository->findByDateRange($start, $end, 1)));
    }

    public function testResultIsAList(): void
    {
        $result = $this->repository->findByDateRange(new DateTime('2026-10-06'), null, 2);

        $this->assertSame([0, 1], array_keys($result), 'filtered result is reindexed');
    }

    private function createInfo(string $start, string $end, string $text, array $sites): void
    {
        $info = new AdminInfo();
        $info->setStart($start);
        $info->setEnd($end);
        $info->setComment($text);
        $info->setSites($sites);
        $this->entityManager->persist($info);
    }

    private function deleteAll(): void
    {
        foreach ($this->entityManager->getRepository(AdminInfo::class)->findAll() as $info) {
            $this->entityManager->remove($info);
        }
        $this->entityManager->flush();
    }

    /**
     * @param AdminInfo[] $infos
     * @return string[]
     */
    private function texts(array $infos): array
    {
        return array_map(fn (AdminInfo $info) => $info->getComment(), $infos);
    }
}
