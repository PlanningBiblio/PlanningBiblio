<?php

$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page`='/absence';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/absence/add' WHERE `url`='/absence';";

$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page`='/absences/document';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page`='/absences/documents';";
