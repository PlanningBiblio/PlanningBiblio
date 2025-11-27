<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251127101304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the "site" column to the "working_hour_cycles" table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}working_hour_cycles ADD COLUMN IF NOT EXISTS `sites` MEDIUMTEXT NOT NULL DEFAULT '[]';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}working_hour_cycles DROP COLUMN IF EXISTS `sites`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
