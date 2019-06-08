<?php
$sql[] = "ALTER TABLE `{$dbprefix}appel_dispo` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_notifications` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles_tab` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_verrou` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `site` `site` INT(3);";