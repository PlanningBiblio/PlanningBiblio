<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260801015124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change login length, from 100 to 180';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}personnel` MODIFY `login` VARCHAR(180) NOT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}personnel` MODIFY `login` VARCHAR(100) NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
