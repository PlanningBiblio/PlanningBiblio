<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Auth-PasswordLength', 'text', '8', 'Nombre minimum de caractères obligatoires pour le changement de mot de passe.','Authentification', '', '20');";
