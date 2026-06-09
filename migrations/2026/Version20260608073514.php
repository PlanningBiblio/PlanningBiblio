<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608073514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}network` (
            id int(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            deletedDate datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("INSERT INTO `{$dbprefix}network` (`id`, `name`) VALUES (1, 'Réseau par défaut')");


        // Mise en réseau de nombreux éléments
        $this->addSql("ALTER TABLE `{$dbprefix}site` ADD COLUMN `network_id` INT(11) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN `network_id` INT(11) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}infos` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}activites` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}postes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_categories` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_etages` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_groupes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_services` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}lignes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_infos` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}network`;");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}infos` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}activites` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}postes` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_categories` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_etages` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_groupes` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_services` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}lignes` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` DROP COLUMN IF EXISTS `network_id`;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_infos` DROP COLUMN IF EXISTS `network_id`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
