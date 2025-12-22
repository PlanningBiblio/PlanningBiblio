<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251218101900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add technical configuration options for absences CSV Import: AbsImport-Agent, AbsImport-ConvertBegin, AbsImport-ConvertEnd';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-CSV', 'boolean', '0', 'Absences Import CSV', 'Activer l\'import d\'absences par fichier CSV', 1, '1');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-Agent', 'enum', 'login', 'login,mail,matricule', 'Absences Import CSV', 'À quel attribut de l\'agent correspond la première colonne du CSV?', 1, '2');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-ConvertBegin', 'textarea', '', 'Absences Import CSV', 'Expressions régulières pour l\'heure de début, une par ligne, évaluées séquentiellement jusqu\'à la première qui matche.', 1, '3');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-ConvertEnd', 'textarea', '', 'Absences Import CSV', 'Expressions régulières pour l\'heure de fin, une par ligne, évaluées séquentiellement jusqu\'à la première qui matche.', 1, '4');");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-CSV' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-Agent' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-ConvertBegin' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-ConvertEnd' LIMIT 1;");
    }

}
