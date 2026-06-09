<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225101615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add default values to the select_statuts table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `valeur` TEXT NOT NULL DEFAULT '';");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `couleur` VARCHAR(7) NOT NULL DEFAULT '';");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `categorie` INT(11) NOT NULL DEFAULT 0;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `valeur` TEXT NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `couleur` VARCHAR(7) NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` MODIFY `categorie` INT(11) NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
