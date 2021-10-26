<?php

$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` MODIFY last_update TIMESTAMP";
$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` MODIFY last_check TIMESTAMP";

$sql[] = "ALTER TABLE `{$dbprefix}absences_infos` MODIFY debut DATE";
$sql[] = "ALTER TABLE `{$dbprefix}absences_infos` MODIFY fin DATE";

$sql[] = "ALTER TABLE `{$dbprefix}pl_notifications` MODIFY date DATE";
