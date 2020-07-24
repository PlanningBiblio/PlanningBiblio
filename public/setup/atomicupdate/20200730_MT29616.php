<?php

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Conges-planningVide','boolean','1','Congés', 
  'Autoriser le d&eacute;p&ocirc;t de cong&eacute;s sur des plannings en cours d&apos;&eacute;laboration','90');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre`) VALUES ('Conges-apresValidation','boolean','1', 'Cong&eacute;s', 'Autoriser l&apos;enregistrement des cong&eacute;s apr&egrave;s validation des plannings', '91');";

