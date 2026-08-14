<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260814080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add PlanningGeneration-ManualJson config option to enable/disable manual JSON generation';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('PlanningGeneration-ManualJson', 'boolean', '1', 'Génération de planning', 'Autoriser la génération de planning en collant un JSON personnalisé (sans passer par la génération automatique).', 1, '30');");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = 'PlanningGeneration-ManualJson';");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
