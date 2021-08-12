<?php

$sql[] = "DELETE FROM `{$dbprefix}config` WHERE nom LIKE 'Multisites-site%-cycles' LIMIT 10;";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '1,2,3,4,5,6,7,8,9,10' WHERE `nom` = 'nb_semaine';";

