<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('Planning-CommentairesToujoursActifs', 'boolean', '0', 'Planning','100', 'Afficher la zone de commentaire même si le planning n\'est pas encore commencé.');";
