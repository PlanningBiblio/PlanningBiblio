<?php
$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '2.0,3.0,4.0' WHERE `nom` = 'CAS-Version';";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '2.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '2';";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '3.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '3';";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '4.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '4';";