<?php


$db = new db();
$db->query("SELECT `valeur` FROM `{$dbprefix}config` WHERE `nom` = 'Planning-Notifications';");
$value = $db->result[0]['valeur'] ? '-1' : '-2';

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-InitialNotification', 'enum2', '[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]', '$value', 'Planning','40', 'Envoyer une notification aux agents lors de la validation des plannings les concernant');";

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-ChangeNotification', 'enum2', '[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]', '$value', 'Planning','41', 'Envoyer une notification aux agents lors d\'une modification de planning les concernant');";

$sql[] = "DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Planning-Notifications';";
