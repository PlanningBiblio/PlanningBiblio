<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811150500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add PlanningGeneration-ApiUrl and PlanningGeneration-ApiKey config options for the planning generation module';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('PlanningGeneration-ApiUrl', 'text', '', 'Génération de planning', 'URL de l\'API externe de génération de planning', 1, '10');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('PlanningGeneration-ApiKey', 'text', '', 'Génération de planning', 'Clé d\'API (jeton Bearer) pour l\'API externe de génération de planning', 1, '20');");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = 'PlanningGeneration-ApiUrl';");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = 'PlanningGeneration-ApiKey';");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
