<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731161022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add JSON roles column to the personnel table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // Add the roles column as JSON (not null with an empty array default comment)
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS roles JSON NOT NULL DEFAULT '[]' COMMENT '(DC2Type:json)' AFTER `login`;");

        $this->addSql("UPDATE `{$dbprefix}personnel` SET roles = '[\"ROLE_USER\",\"ROLE_ADMIN\"]' WHERE id = 1;");
        $this->addSql("UPDATE `{$dbprefix}personnel` SET roles = '[\"ROLE_USER\"]' WHERE id > 2;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // Allows reverting the migration if necessary
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP roles;");
    }
}
