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
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-CSV', 'boolean', '0', 'Absences Import CSV', 'Activer l\'import d\'absences par fichier CSV', 1, '10');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-Reason', 'text', '', 'Absences Import CSV', 'Motif pour les absences importées par fichier CSV', 1, '20');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-Agent', 'enum', 'matricule', 'login,mail,matricule', 'Absences Import CSV', 'À quel attribut de l\'agent correspond la première colonne du CSV?', 1, '30');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-ConvertBegin', 'textarea', '/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4})$/\n/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4}) (matin)$/\n/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4}) (après-midi)$/', 'Absences Import CSV', 'Expressions régulières pour l\'heure de début, une par ligne, évaluées séquentiellement jusqu\'à la première qui matche.', 1, '40');");
        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('AbsImport-ConvertEnd', 'textarea', '/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4})$/\n/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4}) (matin)$/\n/^(\\\d{2}\\\/\\\d{2}\\\/\\\d{4}) (après-midi)$/', 'Absences Import CSV', 'Expressions régulières pour l\'heure de fin, une par ligne, évaluées séquentiellement jusqu\'à la première qui matche.', 1, '50');");

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `groupe`, `page`, `ordre`, `categorie`) VALUES ('Importation des absences depuis un fichier CSV', '1401', 'Importation des absences depuis un fichier CSV', '', '60', 'Absences');");

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES (10, 25, 'Importer des absences', '/absence/import', 'config=AbsImport-CSV');");
        }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-CSV' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-Reason' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-Agent' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-ConvertBegin' LIMIT 1;");
        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='AbsImport-ConvertEnd' LIMIT 1;");

        $this->addSql("DELETE FROM `{$dbprefix}acces` WHERE nom='Importation des absences depuis un fichier CSV';");

        $this->addSql("DELETE FROM `{$dbprefix}menu` WHERE `url`='/absence/import';");
    }

}
