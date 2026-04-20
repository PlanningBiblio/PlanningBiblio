<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423091902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add site and network management';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        //Nouvelles tables
        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}network` (
            id int(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            deleteDate datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}site` (
            id int(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            network_id int(20) NOT NULL DEFAULT '1',
            deleteDate datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
             KEY `network_id` (`network_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}site_mail` (
            id int(11) NOT NULL AUTO_INCREMENT,
            site_id int(20) NOT NULL,
            mail varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
             KEY `site_id` (`site_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}config_network` (
            id int(11) NOT NULL AUTO_INCREMENT,
            network_id int(20) NOT NULL DEFAULT '1',
            config_id int(11) NOT NULL DEFAULT '0',
            value text NOT NULL,
            PRIMARY KEY (`id`),
             KEY `network_id` (`network_id`),
             KEY `config_id` (`config_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}config_technical` (
            id int(11) NOT NULL AUTO_INCREMENT,
            config_id int(11) NOT NULL DEFAULT '0',
            value text NOT NULL,
            PRIMARY KEY (`id`),
            KEY `config_id` (`config_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");


        // Initialisation
        $this->addSql("INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES('50','73','Configuration des sites','/site',NULL)");
        $this->addSql("ALTER TABLE `{$dbprefix}personnel` ADD COLUMN `network_id` INT(11) NOT NULL DEFAULT '1'");
        $this->addSql("UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `nom` IN ('Version', 'URL')");
        $this->addSql("INSERT INTO `{$dbprefix}network` (`id`, `name`) VALUES (1, 'Réseau par défaut')");


        // Migration des sites
        $this->addSql("
        INSERT INTO `{$dbprefix}site` (`id`, `name`, `network_id`, `deleteDate`)
        SELECT 
            CAST(SUBSTRING(nom, LENGTH('Multisites-site') + 1) AS UNSIGNED), valeur, 1, NULL FROM `{$dbprefix}config`
        WHERE nom LIKE 'Multisites-site%'
        AND nom NOT LIKE '%-mail'
        AND valeur IS NOT NULL
        AND TRIM(valeur) <> ''
        ");

        //Ajout d'un site par défaut si aucun site de renseiné
        $this->addSql("
        INSERT INTO `{$dbprefix}site` (`name`, `network_id`, `deleteDate`)
        SELECT 'Site par défaut', 1, NULL
        FROM DUAL
        WHERE NOT EXISTS (SELECT 1 FROM `{$dbprefix}site`)
        ");

        $this->addSql("
        INSERT INTO `{$dbprefix}site_mail` (`site_id`, `mail`)
        SELECT 
            CAST(SUBSTRING(nom, LENGTH('Multisites-site') + 1, 
                LENGTH(nom) - LENGTH('Multisites-site') - LENGTH('-mail')) AS UNSIGNED),
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(valeur, ';', numbers.n), ';', -1)) as mail
        FROM `{$dbprefix}config`
        JOIN (
            SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
        ) numbers
        ON CHAR_LENGTH(valeur) - CHAR_LENGTH(REPLACE(valeur, ';', '')) >= numbers.n - 1
        WHERE nom LIKE 'Multisites-site%-mail'
        ");

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom LIKE 'Multisites-site%'");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom = 'Multisites-nombre'");


        // Migration des configs
        $this->addSql("INSERT INTO `{$dbprefix}config_technical` (`config_id`, `value`) SELECT id, valeur FROM `{$dbprefix}config` WHERE technical = 1");
        $this->addSql("INSERT INTO `{$dbprefix}config_network` (`config_id`, `network_id`, `value`) SELECT id, 1, valeur FROM `{$dbprefix}config` WHERE technical = 0");
        $this->addSql("ALTER TABLE `{$dbprefix}config` DROP COLUMN `valeur`");

        // Ajout du site par défaut aux personnel sans site
        $this->addSql("UPDATE `{$dbprefix}personnel` SET sites = '[\"1\"]' WHERE sites = '[]'");

        // Mise en réseau
        $this->addSql("ALTER TABLE `{$dbprefix}infos` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}activites` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}postes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_abs` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_categories` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_etages` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_groupes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_services` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");

        $this->addSql("ALTER TABLE `{$dbprefix}lignes` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` ADD COLUMN `network_id` INT(20) NOT NULL DEFAULT '1'");
    }
    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}network");
        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}site");
        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}site_mail");
        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}config_network");
        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}config_technical");

        $this->addSql("ALTER TABLE `{$dbprefix}personnel` DROP COLUMN `network_id`");
        $this->addSql("DELETE FROM `{$dbprefix}menu` WHERE url='/site'");
        $this->addSql("UPDATE `{$dbprefix}config` SET `technical` = 0 WHERE `nom` IN ('Version', 'URL')");

        $this->addSql("ALTER TABLE `{$dbprefix}infos` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_infos` DROP COLUMN IF EXISTS `network_id`");

        $this->addSql("ALTER TABLE `{$dbprefix}activites` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}postes` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_abs` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_categories` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_etages` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_groupes` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_services` DROP COLUMN IF EXISTS `network_id`");
        $this->addSql("ALTER TABLE `{$dbprefix}select_statuts` DROP COLUMN IF EXISTS `network_id`");

        $this->addSql("ALTER TABLE `{$dbprefix}lignes` DROP COLUMN IF EXISTS `network_id`");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
