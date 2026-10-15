<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Data cleanup before converting tables to InnoDB and adding foreign keys.
 * Based on the database audit of 2026-10-15, decisions D1 and D2:
 * - D1: remove model lines pointing to deleted model tabs.
 * - Remove structures of deleted plannings (tableau no longer in pl_poste_tab).
 * - D2: recreate separation lines that are still referenced but missing.
 */
final class Version20261015090001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InnoDB preparation: cleanup of orphaned rows (audit 2026-10-15, decisions D1 and D2)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // D1: model lines pointing to deleted model tabs
        $this->addSql("DELETE FROM `{$dbprefix}pl_poste_modeles`
            WHERE model_id NOT IN (SELECT id FROM `{$dbprefix}pl_poste_modeles_tab`);");

        // Structures of deleted plannings (tableau no longer in pl_poste_tab)
        $this->addSql("DELETE FROM `{$dbprefix}pl_poste_cellules`
            WHERE tableau NOT IN (SELECT tableau FROM `{$dbprefix}pl_poste_tab`);");
        $this->addSql("DELETE FROM `{$dbprefix}pl_poste_lignes`
            WHERE tableau NOT IN (SELECT tableau FROM `{$dbprefix}pl_poste_tab`);");
        $this->addSql("DELETE FROM `{$dbprefix}pl_poste_horaires`
            WHERE tableau NOT IN (SELECT tableau FROM `{$dbprefix}pl_poste_tab`);");

        // D2: recreate separation lines that are still referenced but missing
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}lignes` (id, nom)
            SELECT t.ligne, CONCAT('Ligne ', t.ligne)
            FROM (
                SELECT ligne FROM `{$dbprefix}pl_poste_lignes` WHERE ligne <> 0
                UNION
                SELECT ligne FROM `{$dbprefix}pl_poste_cellules` WHERE ligne <> 0
            ) t
            WHERE t.ligne NOT IN (SELECT id FROM `{$dbprefix}lignes`);");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Deleted rows cannot be restored.');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
