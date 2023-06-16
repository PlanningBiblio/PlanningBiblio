<?php

// FIXME : la requête DELETE FROM est utile pour les tests mais ne doit pas être copiée dans le fichier maj.php
$sql[] = "DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Affichage-Agent';";

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Affichage-Agent', 'color', '#FFF3B3', 'Couleur des cellules de l\'agent connecté', 'Affichage', '', NULL, '40');";
