<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260813090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add conflicts column to pl_generation_planning (agents not imported due to an unavailability filed during generation)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}pl_generation_planning` ADD `conflicts` LONGTEXT DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}pl_generation_planning` DROP COLUMN `conflicts`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
