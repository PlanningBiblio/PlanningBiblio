<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113145413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update the order of the parameters in the "Holiday" category, update the comment for the Conges-demi-journee parameter';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = ? WHERE `nom` = ?", [
            'Autorise la saisie de congés en demi-journée. Si le calcul des crédits est en heures, ce paramètre n\'agit que si le paramètre "Conges-Heures" a la valeur "Forcer la saisie des congés sur journées entières',
            'Conges-demi-journees',
        ]);

        foreach (self::ORDERS as $order) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = ? WHERE `nom` = ?", [$order[2], $order[0]]);
        }   
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = ? WHERE `nom` = ?", [
            'Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour',
            'Conges-demi-journees',
        ]);

        foreach (self::ORDERS as $order) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `ordre` = ? WHERE `nom` = ?", [$order[1], $order[0]]);
        }   
    }

    private const ORDERS = [
        ['Conges-Enable'                , 1  , 10  ],
        ['Conges-Mode'                  , 2  , 20  ],
        ['Conges-Recuperations'         , 3  , 30  ],
        ['Conges-validation'            , 4  , 40  ],
        ['Conges-Validation-N2'         , 5  , 50  ],
        ['Conges-tous'                  , 6  , 60  ],
        ['Conges-Heures'                , 3  , 70  ],
        ['Conges-demi-journees'         , 8  , 80  ],
        ['Conges-fullday-switching-time', 9  , 90  ],
        ['Conges-fullday-reference-time', 10 , 100 ],
        ['Conges-transfer-comp-time'    , 16 , 110 ],
        ['Conges-planningVide'          , 11 , 120 ],
        ['Conges-apresValidation'       , 12 , 130 ],
        ['Conges-Rappels'               , 7  , 140 ],
        ['Conges-Rappels-Jours'         , 8  , 150 ],
        ['Conges-Rappels-N1'            , 13 , 160 ],
        ['Conges-Rappels-N2'            , 15 , 170 ],
        ['Recup-Uneparjour'             , 19 , 180 ],
        ['Recup-SamediSeulement'        , 20 , 190 ],
        ['Recup-DeuxSamedis'            , 30 , 200 ],
        ['Recup-Agent'                  , 40 , 210 ],
        ['Recup-DelaiDefaut'            , 40 , 220 ],
        ['Recup-DelaiTitulaire1'        , 50 , 230 ],
        ['Recup-DelaiTitulaire2'        , 60 , 240 ],
        ['Recup-DelaiContractuel1'      , 70 , 250 ],
        ['Recup-DelaiContractuel2'      , 80 , 260 ],
        ['Recup-notifications1'         , 100, 270 ],
        ['Recup-notifications2'         , 100, 280 ],
        ['Recup-notifications3'         , 100, 290 ],
        ['Recup-notifications4'         , 100, 300 ],
    ];
}
