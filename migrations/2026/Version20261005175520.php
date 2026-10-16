<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005175520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename table volants to detached and column perso_id to user_id.';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}volants RENAME TO detached");
        $this->addSql("ALTER TABLE {$dbprefix}detached RENAME COLUMN perso_id TO user_id");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}detached RENAME COLUMN user_id TO perso_id");
        $this->addSql("ALTER TABLE {$dbprefix}detached RENAME TO volants");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
