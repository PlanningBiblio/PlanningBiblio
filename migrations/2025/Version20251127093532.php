<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251127093532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add options to the "PlanningHebdo-resetCycles" setting for multi-sites management';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $configComment = 'Permettre la réinitialisation des cycles de présence.'
            . '<br/>Fonctionne seulement avec le paramètre \"PlanningHebdo\" et pour un minimum de 3 semaines par cycle.'
            . '<br/><strong>Attention</strong>, si vous utilisez 4 semaines par cycle, en activant ce paramètre, vous devrez définir la première semaine 1'
            . ' dans le paramètre \"dateDebutPlHebdo\" et le cycle ne sera pas automatiquement réinitialisé en début d\\\'année.'
            . '<br/><strong>Attention</strong>, la réinitialisation site par site ne fonctionne que si chaque agent n\\\'est associé qu\\\'à un seul site.';

        $this->addSql("UPDATE {$dbprefix}config SET `type` = 'enum2', 
            valeurs = '[[0, \"Désactivé\"], [1, \"S\'applique à tous les sites\"], [2, \"Réinitialisation site par site\"]]',
            commentaires = '$configComment' WHERE nom = 'PlanningHebdo-resetCycles';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $configComment = 'Permettre la réinitialisation des cycles de présence.'
            . '<br/>Fonctionne seulement avec le paramètre \"PlanningHebdo\" et pour un minimum de 3 semaines par cycle.'
            . '<br/>Attention, si vous utilisez 4 semaines par cycle, en activant ce paramètre, vous devrez définir la première semaine 1'
            . ' dans le paramètre \"dateDebutPlHebdo\" et le cycle ne sera pas automatiquement réinitialisé en début d\\\'année.';

        $this->addSql("UPDATE {$dbprefix}config SET `type` = 'boolean', valeurs = '', commentaires = '$configComment' WHERE nom = 'PlanningHebdo-resetCycles';");
        $this->addSql("UPDATE {$dbprefix}config SET `valeur` = '1' WHERE nom = 'PlanningHebdo-resetCycles' AND valeur <> '0';");
    }
}
