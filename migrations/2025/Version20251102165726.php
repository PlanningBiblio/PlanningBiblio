<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251102165726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create working_hour_cycles table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $configComment = 'Permettre la réinitialisation des cycles de présence.'
            . '<br/>Fonctionne seulement avec le paramètre \"PlanningHebdo\" et pour un minimum de 3 semaines par cycle.'
            . '<br/>Attention, si vous utilisez 4 semaines par cycle, en activant ce paramètre, vous devrez définir la première semaine 1'
            . ' dans le paramètre \"dateDebutPlHebdo\" et le cycle ne sera pas automatiquement réinitialisé en début d\\\'année.';

        $this->addSql("CREATE TABLE IF NOT EXISTS {$dbprefix}working_hour_cycles (id int(11) NOT NULL AUTO_INCREMENT, 
            date DATE NOT NULL DEFAULT CURDATE(), 
            week INT(11) NOT NULL DEFAULT 0, 
            PRIMARY KEY (`id`)) ENGINE=MyISAM 
            DEFAULT CHARSET=utf8mb4 
            COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("INSERT INTO {$dbprefix}config (nom, `type`, valeur, commentaires, categorie, valeurs, technical, ordre) VALUES 
            ('PlanningHebdo-resetCycles', 'boolean', '0', '$configComment',
            'Heures de présence', '', 0, 45);");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}working_hour_cycles;");

        $this->addSql("DELETE FROM {$dbprefix}config WHERE nom = 'PlanningHebdo-resetCycles';");
    }
}
