<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Service\AbsenceReasonProvisioner;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * MT55630:
 * - new parameter Absences-ShowReasonDetails (display of the reason details in the calendars and absence lists);
 * - comments of the import reason parameters ([SUMMARY], automatic creation of the reasons, private events);
 * - creation of the reasons configured for the imports, so that the existing imported absences find their reason.
 */
final class Version20261105100000 extends AbstractMigration
{
    private const SUMMARY_HELP = 'Indiquez [SUMMARY] pour utiliser le titre de l\'événement comme motif : il est retenu s\'il correspond à un motif de la liste des motifs d\'absence (sans tenir compte des majuscules), sinon l\'absence est enregistrée avec le motif « Autre » et le titre dans « Précisez le motif ». Les événements privés sont importés avec le titre « [Private] », sans description.';
    private const CREATION_HELP = 'Ce motif est créé automatiquement dans la liste des motifs d\'absence s\'il n\'existe pas.';

    public function getDescription(): string
    {
        return 'MT55630: parameter Absences-ShowReasonDetails, [SUMMARY] for the imports, automatic creation of the configured absence reasons';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // New parameter
        $comment = 'Afficher la précision du motif (champ « Précisez le motif ») à la place du motif « Autre » dans les agendas, dans la liste des absents sous les plannings et dans la page Statistiques / Présents et absents.<br/>'
            . 'Ce champ contient la précision saisie avec le motif « Autre », ou, pour les absences importées (CSV, ICS, Microsoft 365), le titre de l\'événement lorsqu\'il ne correspond à aucun motif d\'absence.<br/>'
            . '<b>Attention – confidentialité</b> : ces écrans sont consultables par l\'ensemble des agents qui ont accès aux plannings et aux agendas. Les précisions et les titres d\'événements seront donc visibles par tous ces agents. '
            . 'N\'activez ce paramètre que si les agents en sont informés et que ces informations ne contiennent pas de données personnelles ou confidentielles (santé, situation familiale, etc.). '
            . 'L\'établissement reste responsable du respect du RGPD pour les informations ainsi affichées.';

        $this->addSql(
            "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre`) VALUES ('Absences-ShowReasonDetails', 'boolean', '0', 'Absences', ?, '36')",
            [$comment]
        );

        // Comments of the import reason parameters
        foreach ($this->newComments() as $name => $text) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = ? WHERE `nom` = ?", [$text, $name]);
        }

        // Creation of the configured reasons (same rules as App\Service\AbsenceReasonProvisioner)
        $config = $this->connection->fetchAllKeyValue("SELECT `nom`, `valeur` FROM `{$dbprefix}config`");
        $existingReasons = $this->connection->fetchFirstColumn("SELECT `valeur` FROM `{$dbprefix}select_abs`");
        $rank = (int) $this->connection->fetchOne("SELECT COALESCE(MAX(`rang`), 0) FROM `{$dbprefix}select_abs`");

        foreach (AbsenceReasonProvisioner::reasonsToCreate($config, $existingReasons) as $reason) {
            $this->addSql(
                "INSERT INTO `{$dbprefix}select_abs` (`valeur`, `rang`, `type`, `notification_workflow`, `teleworking`) VALUES (?, ?, 0, 'A', 0)",
                [$reason, ++$rank]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Absences-ShowReasonDetails'");

        foreach ($this->previousComments() as $name => $text) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = ? WHERE `nom` = ?", [$text, $name]);
        }

        // The reasons created by up() are kept: absences may use them.
    }

    /**
     * @return array<string, string>
     */
    private function newComments(): array
    {
        return [
            'ICS-Pattern1' => 'Motif d\'absence pour les événements importés du 1<sup>er</sup> serveur. Ex : Agenda personnel. ' . self::CREATION_HELP . '<br/>' . self::SUMMARY_HELP,
            'ICS-Pattern2' => 'Motif d\'absence pour les événements importés du 2<sup>ème</sup> serveur. Ex : Congés. ' . self::CREATION_HELP . '<br/>' . self::SUMMARY_HELP,
            'ICS-Pattern3' => 'Motif d\'absence pour les événements importés depuis l\'URL définie dans la fiche des agents. Ex : Agenda personnel. ' . self::CREATION_HELP . '<br/>' . self::SUMMARY_HELP,
            'Hamac-motif' => 'Motif pour les absences importées depuis un fichier CSV. Ex : Import CSV. ' . self::CREATION_HELP . '<br/>'
                . 'Indiquez [SUMMARY] pour utiliser le libellé de l\'absence (2<sup>e</sup> colonne du fichier) comme motif : il est retenu s\'il correspond à un motif de la liste des motifs d\'absence (sans tenir compte des majuscules), sinon l\'absence est enregistrée avec le motif « Autre » et le libellé dans « Précisez le motif ».',
            'MSGraph-AbsenceReason' => 'Absence reason to use for imported events. Optional, "Outlook" by default. The reason is automatically created in the list of absence reasons if it does not exist.<br/>'
                . 'Use [SUMMARY] to use the subject of the events as reason: it is used if it matches a reason of the list (case-insensitive), otherwise the absence is saved with the reason "Autre" and the subject in the reason details. Private events are imported with the subject "[Private]".',
            'AbsImport-Reason' => 'Motif pour les absences importées par fichier CSV. ' . self::CREATION_HELP,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function previousComments(): array
    {
        return [
            'ICS-Pattern1' => 'Motif d\'absence pour les événements importés du 1<sup>er</sup> serveur. Ex: Agenda Personnel',
            'ICS-Pattern2' => 'Motif d\'absence pour les événements importés du 2<sup>ème</sup> serveur. Ex: Congés',
            'ICS-Pattern3' => 'Motif d\'absence pour les événements importés depuis l\'URL définie dans la fiche des agents. Ex: Agenda personnel',
            'Hamac-motif' => 'Motif pour les absences importés depuis Hamac. Ex: Hamac',
            'MSGraph-AbsenceReason' => 'Absence Reason to use for imported events. Optional, "Outlook" by default.',
            'AbsImport-Reason' => 'Motif pour les absences importées par fichier CSV',
        ];
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
