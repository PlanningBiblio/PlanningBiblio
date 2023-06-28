<?php

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab` ADD `copy` INT(11) DEFAULT NULL, ADD `updated_at` DATETIME DEFAULT NULL;";
