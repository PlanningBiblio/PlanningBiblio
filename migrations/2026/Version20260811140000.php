<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create pl_generation_planning table and add the "Générer un planning" admin menu entry';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}pl_generation_planning` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `date_generation` DATETIME NOT NULL,
            `date_debut` DATE NOT NULL,
            `date_fin` DATE NOT NULL,
            `site` INT(11) NULL DEFAULT NULL,
            `json_envoye` JSON NOT NULL,
            `json_recu` JSON NULL DEFAULT NULL,
            `statut` VARCHAR(20) NOT NULL DEFAULT 'en_cours',
            `created_by` INT(11) NULL DEFAULT NULL,
            `error_message` TEXT NULL DEFAULT NULL,
            PRIMARY KEY (`id`))
            ENGINE=MyISAM
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES (50, 95, 'Générer un planning', '/admin/planning-generation', NULL);");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}pl_generation_planning`;");
        $this->addSql("DELETE FROM `{$dbprefix}menu` WHERE `url` = '/admin/planning-generation';");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
