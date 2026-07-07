<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707124101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT35331: Fix description of setting "CAS-debug"';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql(
            "UPDATE {$dbprefix}config SET commentaires = ? WHERE nom = ?",
            [
                'Activer le débogage pour CAS. Les informations seront ajoutées au fichier log de symfony (dossier var/log).',
                'CAS-Debug',
            ],
        );
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql(
            "UPDATE {$dbprefix}config SET commentaires = ? WHERE nom = ?",
            [
                'Activer le débogage pour CAS. Créé un fichier "cas_debug.txt" dans le dossier "[TEMP]"',
                'CAS-Debug',
            ],
        );
    }
}
