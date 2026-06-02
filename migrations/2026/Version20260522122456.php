<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522122456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor multisite';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}site` (
            id int(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL DEFAULT '',
            deletedDate datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}site_mail` (
            id int(11) NOT NULL AUTO_INCREMENT,
            site_id int(20) NOT NULL,
            mail varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
             KEY `site_id` (`site_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("
        INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`)
        VALUES ('50', '73', 'Configuration des sites', '/site', NULL);
        ");

        $this->addSql("
        INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`)
        SELECT '50','73','Configuration des sites','/site',NULL FROM DUAL
        WHERE NOT EXISTS (
            SELECT 1 FROM (SELECT 1 FROM `{$dbprefix}menu` WHERE `url`='/site' LIMIT 1) AS _existing
        )");

        $this->addSql("
        INSERT IGNORE INTO `{$dbprefix}site` (`id`, `name`, `deletedDate`)
        SELECT 
            CAST(SUBSTRING(nom, LENGTH('Multisites-site') + 1) AS UNSIGNED), valeur, NULL FROM `{$dbprefix}config`
        WHERE nom LIKE 'Multisites-site%'
        AND nom NOT LIKE '%-mail'
        AND valeur IS NOT NULL
        AND TRIM(valeur) <> ''
        ");

        //Ajout d'un site par défaut si aucun site de renseiné
        $this->addSql("
        INSERT INTO `{$dbprefix}site` (`name`, `deletedDate`)
        SELECT 'Site par défaut', NULL
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
        AND NOT EXISTS (
            SELECT 1 FROM (SELECT 1 FROM `{$dbprefix}site_mail` LIMIT 1) AS _existing
        )
        ");

        $this->addSql("UPDATE `{$dbprefix}postes` SET site=1 WHERE site=0");

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom LIKE 'Multisites-site%'");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom = 'Multisites-nombre'");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("
            INSERT INTO `{$dbprefix}config` (nom, valeur)
            SELECT
                CONCAT('Multisites-site', id),
                name
            FROM `{$dbprefix}site`
            WHERE deletedDate IS NULL
        ");

        $this->addSql("
        INSERT INTO `{$dbprefix}config` (nom, valeur)
        SELECT
            CONCAT('Multisites-site', site_id, '-mail'),
            GROUP_CONCAT(mail SEPARATOR ';')
        FROM `{$dbprefix}site_mail`
        GROUP BY site_id
        ");

        $this->addSql("
        INSERT INTO `{$dbprefix}config` (nom, valeur)
        SELECT
            'Multisites-nombre',
            COUNT(*)
        FROM `{$dbprefix}site`
        WHERE deletedDate IS NULL
        ");

        $this->addSql("UPDATE `{$dbprefix}postes` SET site = 0 WHERE site = 1");
        $this->addSql("DELETE FROM `{$dbprefix}menu` WHERE url = '/site'");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}site_mail`");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}site`");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
