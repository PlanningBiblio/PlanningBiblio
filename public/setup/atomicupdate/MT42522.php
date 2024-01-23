<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Description1','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','23');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Description2','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','43');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Description3','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','48');";
