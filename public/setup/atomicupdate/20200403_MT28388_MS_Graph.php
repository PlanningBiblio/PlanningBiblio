<?php
$sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN last_modified VARCHAR(255) NULL AFTER ical_key;";
$sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN external_ical_key TEXT NULL AFTER ical_key;";

