<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250827085945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace ENUM fields';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences_recurrentes` CHANGE `end` `end` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` CHANGE `supprime` `supprime` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}pl_poste` CHANGE `absent` `absent` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}pl_poste` CHANGE `supprime` `supprime` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}pl_poste` CHANGE `grise` `grise` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}pl_poste_lignes` CHANGE `type` `type` VARCHAR(6) NOT NULL DEFAULT 'poste';");

        $this->addSql("UPDATE `{$dbprefix}absences_recurrentes` SET `end` = 0 WHERE `end` = 1;");
        $this->addSql("UPDATE `{$dbprefix}absences_recurrentes` SET `end` = 1 WHERE `end` > 1;");

        $this->addSql("UPDATE `{$dbprefix}personnel` SET `supprime` = 0 WHERE `supprime` = 1;");
        $this->addSql("UPDATE `{$dbprefix}personnel` SET `supprime` = 1 WHERE `supprime` = 2;");
        $this->addSql("UPDATE `{$dbprefix}personnel` SET `supprime` = 2 WHERE `supprime` > 2;");

        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `absent` = 0 WHERE `absent` = 1;");
        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `absent` = 1 WHERE `absent` = 2;");
        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `absent` = 2 WHERE `absent` > 2;");

        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `supprime` = 0 WHERE `supprime` = 1;");
        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `supprime` = 1 WHERE `supprime` > 1;");

        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `grise` = 0 WHERE `grise` = 1;");
        $this->addSql("UPDATE `{$dbprefix}pl_poste` SET `grise` = 1 WHERE `grise` > 1;");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Can not rollback change ENUM to TINYINT');
    }
}
