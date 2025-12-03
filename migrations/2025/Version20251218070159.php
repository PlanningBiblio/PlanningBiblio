<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251218070159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE depart depart DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE arrivee arrivee DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE last_login last_login DATETIME NULL DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE depart depart DATE NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE arrivee arrivee DATE NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}personnel CHANGE last_login last_login DATETIME NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
