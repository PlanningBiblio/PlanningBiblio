<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/help';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/calendar';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='/absences/info';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/agent';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '', `nom` = 'Postes et activités' WHERE `page`='/position';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='/config';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/skill';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/skill/add';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='/admin/info';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/admin/info/add';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='/closingday';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/holiday/index';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/holiday/new';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/holiday/edit';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='/statistics/attendeesmissing';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='/holiday';";