<?php
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-sites', 'text', '0', 'Time it takes an agent to travel between sites (minutes)', 'Planning', 95);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-floors', 'text', '0', 'Time it takes an agent to travel between floors (minutes)', 'Planning', 96);";
