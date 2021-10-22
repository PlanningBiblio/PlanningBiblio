<?php

$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` MODIFY last_update TIMESTAMP";
$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` MODIFY last_check TIMESTAMP";

