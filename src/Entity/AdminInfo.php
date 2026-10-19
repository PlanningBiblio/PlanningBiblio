<?php

namespace App\Entity;

use App\Repository\AdminInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminInfoRepository::class)]
#[ORM\Table(name: 'information')]
class AdminInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $end = null;

    #[ORM\Column(type: Types::TEXT)]
    protected string $text = '';

    /**
     * Sites on which the message is displayed.
     * An empty array means "all sites", including sites created later.
     *
     * @var int[]
     */
    #[ORM\Column]
    protected array $sites = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(?\DateTimeImmutable $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeImmutable $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getSites(): array
    {
        return array_map('intval', $this->sites ?? []);
    }

    /**
     * @param array<int|string> $sites
     */
    public function setSites(array $sites): static
    {
        $sites = array_unique(array_map('intval', $sites));
        sort($sites);
        $this->sites = array_values($sites);

        return $this;
    }

    public function isForAllSites(): bool
    {
        return empty($this->sites);
    }

    public function isDisplayedOnSite(int $site): bool
    {
        return $this->isForAllSites() || in_array($site, $this->getSites(), true);
    }
}
