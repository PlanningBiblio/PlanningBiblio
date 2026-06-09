<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309123957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove config.extra column';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}config DROP COLUMN IF EXISTS extra");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}config ADD COLUMN IF NOT EXISTS extra varchar(100) DEFAULT NULL");
        $this->addSql("UPDATE {$dbprefix}config SET extra = ? WHERE nom = ?", ["onchange='mail_config();", "Mail-IsMail-IsSMTP"]);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
