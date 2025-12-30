<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211190001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50832_Agent: Set the DateTime fields nullable in the conges table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `debut` `debut` DATETIME NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `fin` `fin` DATETIME NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `saisie` `saisie` TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `modification` `modification` TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `validation_n1` `validation_n1` TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `validation` `validation` TIMESTAMP NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `suppr_date` `suppr_date` TIMESTAMP NULL DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `debut` `debut` DATETIME NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `fin` `fin` DATETIME NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `saisie` `saisie` TIMESTAMP NOT NULL DEFAULT current_timestamp();");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `modification` `modification` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `validation_n1` `validation_n1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `validation` `validation` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';");
        $this->addSql("ALTER TABLE {$dbprefix}conges CHANGE `suppr_date` `suppr_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
