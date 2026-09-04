<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260804141602 extends AbstractMigration
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
            mails TEXT NOT NULL DEFAULT '[]',
            deleted_date datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES ('50', '73', 'Configuration des sites', '/site', NULL);");

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}site` (`id`, `name`, `deleted_date`)
        SELECT 
            CAST(SUBSTRING(nom, LENGTH('Multisites-site') + 1) AS UNSIGNED), valeur, NULL FROM `{$dbprefix}config`
        WHERE nom LIKE 'Multisites-site%'
        AND nom NOT LIKE '%-mail'
        AND valeur IS NOT NULL
        AND TRIM(valeur) <> ''");

        //Ajout d'un site par défaut si aucun site de renseiné
        $this->addSql("INSERT INTO `{$dbprefix}site` (`name`, `deleted_date`) SELECT 'Site par défaut', NULL FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `{$dbprefix}site`)");

        $this->addSql("UPDATE `{$dbprefix}site` s
        JOIN `{$dbprefix}config` c ON c.nom = CONCAT('Multisites-site', s.id, '-mail')
        SET s.mails = CASE 
            WHEN c.valeur IS NOT NULL AND TRIM(c.valeur) <> '' THEN 
                CONCAT('[\"', REPLACE(REPLACE(TRIM(c.valeur), ' ', ''), ';', '\",\"'), '\"]')
            ELSE '[]'
        END");

        $this->addSql("UPDATE `{$dbprefix}site` SET mails = '[]' WHERE mails IS NULL");

        $this->addSql("UPDATE `{$dbprefix}postes` SET site=1 WHERE site=0");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom LIKE 'Multisites-site%'");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom = 'Multisites-nombre'");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT INTO `{$dbprefix}config` (nom, valeur)
            SELECT
                CONCAT('Multisites-site', id),
                name
            FROM `{$dbprefix}site`
            WHERE deleted_date IS NULL
        ");

        $this->addSql("INSERT INTO `{$dbprefix}config` (nom, valeur)
        SELECT
            CONCAT('Multisites-site', site_id, '-mail'),
            GROUP_CONCAT(mail SEPARATOR ';')
        FROM `{$dbprefix}site_mail`
        GROUP BY site_id
        ");

        $this->addSql("INSERT INTO `{$dbprefix}config` (nom, valeur)
        SELECT
            'Multisites-nombre',
            COUNT(*)
        FROM `{$dbprefix}site`
        WHERE deleted_date IS NULL
        ");

        $this->addSql("DELETE FROM `{$dbprefix}menu` WHERE url = '/site'");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}site_mail`");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}site`");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
