<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825161538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = '';

        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql("INSERT INTO {$dbprefix}acces (nom, groupe_id, groupe, page, ordre, categorie) VALUES "
            ."('Planning Poste', '301', 'Création / modification des plannings, utilisation et gestion des modèles', '', '110', 'Planning'),"
            ."('Personnel - Password', '100', '', 'personnel/password.php', '0', ''),"
            ."('Absences - Infos', '201', 'Gestion des absences, validation niveau 1', '', '30', 'Absences'),"
            ."('Personnel - Index', '4', 'Voir les fiches des agents', '', '60', 'Agents'),"
            ."('Postes et activités', '5', 'Gestion des postes', '', '160', 'Postes'),"
            ."('Statistiques', '17', 'Accès aux statistiques', '', '170', 'Statistiques'),"
            ."('Liste des agents présents et absents', '1301', 'Accès aux statistiques Présents / Absents', '', '171', 'Statistiques'),"
            ."('Configuration avancée', '20', 'Configuration avancée', '', '0', ''),"
            ."('Personnel - Valid', '21', 'Gestion des agents', '', '70', 'Agents'),"
            ."('Gestion du personnel', '21', 'Gestion des agents', '', '70', 'Agents'),"
            ."('Configuration des horaires des tableaux', '22', 'Configuration des tableaux', 'planning/postes_cfg/horaires.php', '140', 'Planning'),"
            ."('Configuration des horaires des tableaux', '22', 'Configuration des tableaux', '', '140', 'Planning'),"
            ."('Configuration des lignes des tableaux', '22', 'Configuration des tableaux', 'planning/postes_cfg/lignes.php', '140', 'Planning'),"
            ."('Activités - Validation', '5', 'Gestion des postes', 'activites/valid.php', '160', 'Postes'),"
            ."('Configuration des tableaux - Modif', '22', 'Configuration des tableaux', '', '140', 'Planning'),"
            ."('Informations', '23', 'Informations', '', '0', ''),"
            ."('Configuration des tableaux - Modif', '22', 'Configuration des tableaux', '', '140', 'Planning'),"
            ."('Configuration des tableaux - Modif', '22', 'Configuration des tableaux', '', '140', 'Planning'),"
            ."('Configuration des tableaux - Modif', '22', 'Configuration des tableaux', '', '140', 'Planning'),"
            ."('Modification des plannings - menudiv', '1001', 'Modification des plannings', 'planning/poste/menudiv.php', '120', 'Planning'),"
            ."('Modification des plannings - majdb', '1001', 'Modification des plannings', 'planning/poste/majdb.php', '120', 'Planning'),"
            ."('Jours fériés', '25', 'Gestion des jours fériés', '', '0', ''),"
            ."('Voir les agendas de tous', '3', 'Voir les agendas de tous', '', '55', 'Agendas'),"
            ."('Modifier ses propres absences', '6', 'Modifier ses propres absences', '', '20', 'Absences'),"
            ."('Gestion des absences, validation niveau 2', '501', 'Gestion des absences, validation niveau 2', '', '40', 'Absences'),"
            ."('Gestion des absences, pièces justificatives', '701', 'Gestion des absences, pièces justificatives', '', '50', 'Absences'),"
            ."('Planning Hebdo - Admin N1', '1101', 'Gestion des heures de présence, validation niveau 1', '', '80', 'Heures de présence'),"
            ."('Planning Hebdo - Admin N2', '1201', 'Gestion des heures de présence, validation niveau 2', '', '90', 'Heures de présence'),"
            ."('Modification des commentaires des plannings', '801', 'Modification des commentaires des plannings', '', '130', 'Planning'),"
            ."('Griser les cellules des plannings', '901', 'Griser les cellules des plannings', '', '125', 'Planning'),"
            ."('Congés - Index', '100', '', 'conges/index.php', '0', ''),"
            ."('Gestion des congés, validation niveau 2', '601', 'Gestion des congés, validation niveau 2', '', '76', 'Congés'),"
            ."('Gestion des congés, validation niveau 1', '401', 'Gestion des congés, validation niveau 1', '', '75', 'Congés'),"
            ."('Enregistrement d\'absences pour plusieurs agents', '9', 'Enregistrement d\'absences pour plusieurs agents', '', '25', 'Absences');"
        );
    }

    public function down(Schema $schema): void
    {
        $dbprefix = '';

        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql("TRUNCATE {$dbprefix}acces;");
    }
}
