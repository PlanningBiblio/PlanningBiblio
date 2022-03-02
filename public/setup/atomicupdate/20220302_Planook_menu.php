<?php

// Planook configuration is made to hide some information in order to propose a light version of Planno

// Hide statistics menu
$sql[] = "UPDATE `{$dbprefix}menu` SET `condition` = 'config!=Planook' WHERE `niveau1` = '40';";

// Hide admin / information ; closing days ;  absences / informations
$sql[] = "UPDATE `{$dbprefix}menu` SET `condition`='config!=Planook' where url='/admin/info';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `condition`='config!=Planook' where url='/closingday';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `condition`='config!=Planook' where url='/absences/info';";

// Hide skills menu
$sql[] = "UPDATE `{$dbprefix}menu` SET `titre`='Les activités', `condition`='config!=Planook' where url='/skill';";

// Remove HTML entities
$sql[] = "UPDATE `{$dbprefix}menu` SET `titre`='Présents / absents' where url='/statistics/attendeesmissing';";

