<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212001048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a default value to the absences_infos table for the "text" field';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences_infos` CHANGE `texte` `texte` MEDIUMTEXT NOT NULL DEFAULT '';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences_infos` CHANGE `texte` `texte` MEDIUMTEXT NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
