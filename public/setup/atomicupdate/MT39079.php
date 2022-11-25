<?php

$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `droits` `droits` TEXT COLLATE utf8mb4_unicode_ci NOT NULL;";