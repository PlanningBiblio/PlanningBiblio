<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260918105035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT48942: Add Accessibility page information to the config table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $a11yRemedies = "Si vous constatez un défaut d\'accessibilité vous empêchant d\'accéder à un contenu ou à une fonctionnalité du service, que vous nous le signalez et que vous ne parvenez pas à obtenir une réponse de notre part, vous êtes en droit de faire parvenir vos doléances ou une demande de saisine au Défenseur des droits.

Plusieurs moyens sont à votre disposition :
* Écrire un message au Défenseur des droits : <a href=\"https://formulaire.defenseurdesdroits.fr\"  target=\"_blank\">formulaire.defenseurdesdroits.fr (nouvelle fenêtre)</a>
* Contacter le délégué du Défenseur des droits dans votre région : <a href=\"https://defenseurdesdroits.fr/carte-des-delegues\" target=\"_blank\">defenseurdesdroits.fr/carte-des-delegues (nouvelle fenêtre)</a>
* Envoyer un courrier par la poste, gratuitement, sans affranchissement :

Défenseur des droits  
Libre réponse 71120  
75342 Paris CEDEX 07
* Appeler le 09 69 39 00 00 (coût d\'un appel local)";


        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `technical`, `ordre`) VALUES 
                (
                'a11yEntityName',
                'text',
                '[a11yEntityName]',
                'Nom de l\'entité qui met Planno à disposition de ses utilisateurs et sur qui porte l\'obligation de publication de la page d\'accessibilité. Il s\'agit de l\'établissement utilisateur et non de l\'éditeur du logiciel.',
                'Accessibilité',
                1,
                10
                ),
                (
                'a11yMultiannualPlan',
                'text',
                '',
                'Lien vers le schéma pluriannuel de mise en accessibilité ce l\'établissement utilisateur.',
                'Accessibilité',
                1,
                20
                ),
                (
                'a11yContact',
                'textarea',
                '[a11yContact]',
                'Point de contact pour les questions d\'accessibilité. Ces informations peuvent être rédigées avec le format MarkDown.',
                'Accessibilité',
                1,
                30
                ),
                (
                'a11yRemedies',
                'textarea',
                '$a11yRemedies',
                'Voie de recours. Pour la France, la mention est imposée par l\'arrêté du 20 septembre 2019 : texte normalisé, à ne pas modifier. Ces informations peuvent être rédigées avec le format MarkDown.',
                'Accessibilité',
                1,
                40
                );
        ");

    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE `nom` IN ('a11yEntityName', 'a11yMultiannualPlan', 'a11yContact', 'a11yRemedies');");
    }
}
