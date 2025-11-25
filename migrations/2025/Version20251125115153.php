<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251125115153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix working hours start date';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $query = "UPDATE {$dbprefix}config c SET valeur = 
            CASE WHEN (SELECT COUNT(*) FROM {$dbprefix}config WHERE nom = 'nb_semaine' AND valeur > '3') >= 1
                THEN DATE_FORMAT(
                    DATE_ADD(
                        STR_TO_DATE(c.valeur, '%d/%m/%Y'),
                        INTERVAL +7 DAY
                    ),
                    '%d/%m/%Y'
                )
                ELSE c.valeur
                END
            WHERE nom = 'dateDebutPlHebdo' AND valeur <> '';";

        $this->addSql($query);
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $query = "UPDATE {$dbprefix}config c SET valeur = 
            CASE WHEN (SELECT COUNT(*) FROM {$dbprefix}config WHERE nom = 'nb_semaine' AND valeur > '3') >= 1
                THEN DATE_FORMAT(
                    DATE_SUB(
                        STR_TO_DATE(c.valeur, '%d/%m/%Y'),
                        INTERVAL +7 DAY
                    ),
                    '%d/%m/%Y'
                )
                ELSE c.valeur
                END
            WHERE nom = 'dateDebutPlHebdo' AND valeur <> '';";

        $this->addSql($query);
    }
}
