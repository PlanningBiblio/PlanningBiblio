<?php

// Planook configuration is made to hide some information in order to propose a light version of Planno
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Planook', 'hidden', '0', 'Version Lite Planook',' Divers','','0');";