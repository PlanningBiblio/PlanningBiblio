<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210104002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `date` DATE NULL");
        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `debut` TIME NULL");
        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `fin` TIME NULL");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `date` VARCHAR(10) NULL");
        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `debut` VARCHAR(8) NULL");
        $this->addSql("ALTER TABLE {$dbprefix}appel_dispo MODIFY `fin` VARCHAR(8) NULL");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
