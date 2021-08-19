<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Conges-Heures','enum2','0','Permettre la saisie de congés sur quelques heures ou forcer la saisie de congés sur des journées complètes. Paramètre actif avec les options Conges-Mode=Heures et Conges-Recuperations=Dissocier', 'Congés', '[[0,\"Forcer la saisie de congés sur journées entières\"],[1,\"Permettre la saisie de congés sur quelques heures\"]]', '3');";
