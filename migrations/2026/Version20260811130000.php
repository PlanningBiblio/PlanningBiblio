<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create pl_poste_effectif_attendu table (expected staff per position/timeslot, used by the planning generation algorithm)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}pl_poste_effectif_attendu` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `numero` INT(11) NOT NULL,
            `tableau` INT(11) NOT NULL,
            `ligne` INT(11) NOT NULL,
            `colonne` INT(11) NOT NULL,
            `nb_attendu` INT(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `numero_tableau_ligne_colonne` (`numero`, `tableau`, `ligne`, `colonne`))
            ENGINE=MyISAM
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}pl_poste_effectif_attendu`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
