<?php
// MT39693: Show closingday in menu only if Conges-Enable is enabled
$sql[] = "UPDATE `{$dbprefix}menu` SET `condition` = 'config!=Planook&config=Conges-Enable' WHERE `url` = '/closingday' LIMIT 1;";
