<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251218132740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE timestamp timestamp TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE last_update last_update TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE last_check last_check TIMESTAMP NULL DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE timestamp timestamp TIMESTAMP NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE last_update last_update TIMESTAMP NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}absences_recurrentes CHANGE last_check last_check TIMESTAMP NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
