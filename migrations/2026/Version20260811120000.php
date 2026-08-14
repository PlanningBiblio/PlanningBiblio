<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add agent fields used by the planning generation algorithm (quota_pct, quotas_postes, max_sp_journee_pct, pause_inter_listes_min)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `quota_pct` FLOAT NULL DEFAULT NULL AFTER `heures_travail`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `quotas_postes` JSON NOT NULL DEFAULT '{}' AFTER `quota_pct`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `max_sp_journee_pct` FLOAT NULL DEFAULT NULL AFTER `quotas_postes`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `pause_inter_listes_min` INT NULL DEFAULT NULL AFTER `max_sp_journee_pct`;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN IF EXISTS `quota_pct`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN IF EXISTS `quotas_postes`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN IF EXISTS `max_sp_journee_pct`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN IF EXISTS `pause_inter_listes_min`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
