<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Information messages per site: add infos.sites (JSON, [] = all sites)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // Existing messages get the default value [] and remain displayed on all sites
        $this->addSql("ALTER TABLE `{$dbprefix}infos` ADD `sites` JSON NOT NULL DEFAULT '[]';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}infos` DROP `sites`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
