<?php
$sql[] = "ALTER TABLE `{$dbprefix}postes` ADD COLUMN position VARCHAR(11) DEFAULT 'frontOffice' AFTER groupe_id;";

