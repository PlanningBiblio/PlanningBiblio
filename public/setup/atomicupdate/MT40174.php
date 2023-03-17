<?php

$sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '1501' WHERE `groupe` = 'Validation des échanges';";
$sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE(droits, '1301', '1501');";
