<?php
$sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN last_modified VARCHAR(255) NULL AFTER ical_key;";

