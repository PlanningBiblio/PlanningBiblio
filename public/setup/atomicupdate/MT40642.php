<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-File', 'text', '', '', 'Emplacement d\'un fichier LDIF pour l\'importation des agents', 'LDIF', 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-ID-Attribute', 'enum', 'uid', 'uid,samaccountname,supannaliaslogin', 'Attribut d\'authentification (OpenLDAP : uid, Active Directory : samaccountname)', 'LDIF', 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-Matricule', 'text', '', '', 'Attribut à importer dans le champ matricule (optionnel)', 'LDIF', 30);";
