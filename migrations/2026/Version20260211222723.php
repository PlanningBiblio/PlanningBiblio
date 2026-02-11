<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211222723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the field "etat" from the table "absences" and change "isAttach" fields to TINYINT';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences` DROP COLUMN IF EXISTS `etat`;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `pj1` `pj1` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `pj2` `pj2` TINYINT(1) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `so` `so` TINYINT(1) NOT NULL DEFAULT 0;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences` ADD COLUMN IF NOT EXISTS `etat` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `commentaires`;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `pj1` `pj1` INT(1) NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `pj2` `pj2` INT(1) NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences` CHANGE `so` `so` INT(1) NULL DEFAULT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
