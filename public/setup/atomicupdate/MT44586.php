<?php

$sql[] = "UPDATE `{$dbprefix}pl_poste_lignes` SET `poste` = REPLACE(`poste`, '&quot;' , '\"') WHERE `type` = 'titre';";
$sql[] = "UPDATE `${dbprefix}pl_poste_lignes` SET `poste` = REPLACE(`poste`, '&#039;' , \"'\") WHERE `type` = 'titre';";
