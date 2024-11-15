<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-Exclusion', 'enum2', '[[0, \"Les agents ayant une absence validée sont exclus des plannings.\"],[1,\"Les agents ayant des absences importées validées peuvent être ajoutés au planning.\"],[2,\"Les agents ayant des absences validées, importées ou non, peuvent être ajoutés au planning.\"]]', '0', 'Absences','160', 'Autoriser l\'affectation au planning des agents absents.');";
