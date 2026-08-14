<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260812090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create pl_poste_effectif_attendu_date table (per-date override of the expected staff for a position/timeslot)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}pl_poste_effectif_attendu_date` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `numero` INT(11) NOT NULL,
            `tableau` INT(11) NOT NULL,
            `ligne` INT(11) NOT NULL,
            `colonne` INT(11) NOT NULL,
            `date` DATE NOT NULL,
            `nb_attendu` INT(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `numero_tableau_ligne_colonne_date` (`numero`, `tableau`, `ligne`, `colonne`, `date`),
            KEY `date` (`date`))
            ENGINE=MyISAM
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}pl_poste_effectif_attendu_date`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
