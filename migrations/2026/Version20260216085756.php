<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216085756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT48492: Add technical configuration options for absences CSV Import: AbsImport-Comment';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 40 WHERE `nom` = 'AbsImport-Agent';");
        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 50 WHERE `nom` = 'AbsImport-ConvertBegin';");
        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 60 WHERE `nom` = 'AbsImport-ConvertEnd';");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES 
        ('AbsImport-Comment', 'text', 'Absences Import CSV', 'Absences Import CSV', 'Commentaire pour les absences importées par fichier CSV', 1, 30);");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 30 WHERE `nom` = 'AbsImport-Agent';");
        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 40 WHERE `nom` = 'AbsImport-ConvertBegin';");
        $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = 50 WHERE `nom` = 'AbsImport-ConvertEnd';");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = 'AbsImport-Comment';");
    }
}
