<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-Encoding', 'enum', 'UTF-8', 'UTF-8,ISO-8859-1', 'Encodage de caractères du fichier source', 'LDIF', 40);";

$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = 'uid,samaccountname,supannaliaslogin,employeenumber' WHERE `nom` = 'LDIF-ID-Attribute';";

$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `matricule` `matricule` VARCHAR(100) NULL DEFAULT NULL;";
