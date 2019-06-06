<?php
$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `password` `password` VARCHAR(255) NOT NULL DEFAULT '';";
