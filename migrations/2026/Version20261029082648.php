<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029082648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT 53228: Add settings Absences-documents-typesMime and Absences-documents-extensions';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql(
            "INSERT IGNORE INTO {$dbprefix}config (nom, type, valeur, commentaires, categorie, valeurs, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                'Absences-documents-typesMime',
                'textarea',
                'application/pdf, image/jpeg, image/png',
                'Types MIME autorisés pour les documents attachés aux absences',
                'Absences',
                '',
                151,
            ]
        );

        $this->addSql(
            "INSERT IGNORE INTO {$dbprefix}config (nom, type, valeur, commentaires, categorie, valeurs, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                'Absences-documents-extensions',
                'textarea',
                'pdf, jpg, png',
                'Extensions autorisées pour les documents attachés aux absences',
                'Absences',
                '',
                152,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM {$dbprefix}config WHERE nom = ?", ['Absences-documents-typesMime']);
        $this->addSql("DELETE FROM {$dbprefix}config WHERE nom = ?", ['Absences-documents-extensions']);
    }
}
