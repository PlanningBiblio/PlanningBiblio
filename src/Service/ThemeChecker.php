<?php

namespace App\Service;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;

class ThemeChecker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function get(): string
    {
        $theme = $this->entityManager->getRepository(Config::class)->findOneByNom('Affichage-theme')->getValue() ?? 'default';

        if (!file_exists("themes/$theme/$theme.css")) {
            $theme = 'default';
        }

        return $theme;
    }
}
