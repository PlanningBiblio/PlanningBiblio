<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('ICS-Interval', 'text', '365', 'Restriction de la période à exporter : renseigner le nombre de jours à rechercher dans le passé. Les événements à venir sont toujours exportés. Si le champ n\'est pas renseigné, tous les événements seront recherchés.', 'ICS', '', NULL, '80');";

