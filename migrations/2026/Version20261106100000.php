<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * MT52381: display of the comments of the absences in the calendars and absence lists.
 */
final class Version20261106100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52381: parameters Absences-CommentReasons, Absences-CommentDisplay, Absences-CommentMaxLength, Absences-CommentViews';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        foreach ($this->parameters() as $parameter) {
            $this->addSql(
                "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre`) VALUES (?, ?, ?, ?, 'Absences', ?, ?)",
                $parameter
            );
        }
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        foreach ($this->parameters() as $parameter) {
            $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` = ?", [$parameter[0]]);
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}>
     */
    private function parameters(): array
    {
        $reasons = 'Motifs d\'absence pour lesquels le commentaire saisi dans l\'absence est affiché dans les agendas, dans la liste des absents sous les plannings et dans la page Statistiques / Présents et absents (voir aussi Absences-CommentDisplay, Absences-CommentMaxLength et Absences-CommentViews).<br/>'
            . 'Saisissez les motifs tels qu\'ils sont enregistrés dans les absences, séparés par des points-virgules (exemple : Réunion;Formation;Autre). Les majuscules et les espaces en début et en fin ne sont pas pris en compte.<br/>'
            . 'Saisissez [All] pour afficher les commentaires de <b>toutes</b> les absences, quel que soit leur motif.<br/>'
            . 'Laissez ce champ vide pour ne pas afficher les commentaires (valeur par défaut).<br/>'
            . '<b>Attention – confidentialité</b> : ces écrans sont consultables par l\'ensemble des agents qui ont accès aux plannings et aux agendas. Les commentaires des absences concernées seront visibles par tous ces agents.<br/>'
            . '<b>Avec [All], tous les commentaires deviennent visibles, y compris ceux des congés, des absences pour raison de santé ou pour motif familial, ainsi que les commentaires saisis avant l\'activation de ce paramètre.</b> '
            . 'Avant d\'utiliser [All], vérifiez le contenu des commentaires existants et informez les agents et les gestionnaires que leurs commentaires seront visibles ; à défaut, indiquez seulement les motifs dont les commentaires ne contiennent aucune information personnelle ou confidentielle.<br/>'
            . 'L\'établissement reste responsable du respect du RGPD pour les informations ainsi affichées.';

        $display = 'Mode d\'affichage des commentaires des absences dont le motif est défini dans Absences-CommentReasons :<br/>'
            . '– « Après le motif » : le commentaire suit le motif (exemple : « Réunion : COPIL ») ;<br/>'
            . '– « À la place du motif » : le commentaire remplace le motif (exemple : « COPIL ») ; adapté aux motifs génériques comme « Autre » ou aux absences importées d\'un agenda ;<br/>'
            . '– « Symbole ⓘ » : un symbole ⓘ suit le motif (exemple : « Réunion ⓘ ») ; le commentaire s\'affiche dans une bulle au survol, à la prise de focus ou au clic.<br/>'
            . 'Les absences sans commentaire affichent leur motif seul.';

        $maxLength = 'Nombre maximal de caractères du commentaire affiché après le motif ou à la place du motif (paramètres Absences-CommentReasons et Absences-CommentDisplay). Valeur par défaut : 30.<br/>'
            . 'Au-delà, le commentaire est tronqué et suivi de « … » ; le commentaire complet s\'affiche dans une bulle, au survol, à la prise de focus ou au clic. Seule la première ligne du commentaire est affichée ; la bulle contient le commentaire entier.<br/>'
            . 'Sans effet avec l\'affichage par symbole ⓘ, où le commentaire n\'est visible que dans la bulle.';

        $views = 'Écrans sur lesquels les commentaires des absences sont affichés (motifs définis dans Absences-CommentReasons). Sur les écrans non cochés, seul le motif est affiché.<br/>'
            . 'La confidentialité des commentaires s\'applique à chaque écran coché : voir le commentaire du paramètre Absences-CommentReasons.';

        return [
            ['Absences-CommentReasons', 'textarea', '', '', $reasons, '37'],
            ['Absences-CommentDisplay', 'enum2', 'append', json_encode([['append', 'Après le motif'], ['replace', 'À la place du motif'], ['icon', 'Symbole ⓘ']], JSON_UNESCAPED_UNICODE), $display, '38'],
            ['Absences-CommentMaxLength', 'number', '30', '{"min":5,"max":200,"step":1}', $maxLength, '39'],
            ['Absences-CommentViews', 'checkboxes', '["calendar","planning","attendees"]', json_encode([['calendar', 'Agenda'], ['planning', 'Liste des absents sous les plannings'], ['attendees', 'Statistiques / Présents et absents']], JSON_UNESCAPED_UNICODE), $views, '40'],
        ];
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
