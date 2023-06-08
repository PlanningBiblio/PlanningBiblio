<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Affichage-Agent', 'text', '#FFF3B3', 'Couleur des cellules de l\'agent connecté. Au format hexadécimal, exemple : #FFF3B3.', 'Affichage', '', NULL, '40');";
