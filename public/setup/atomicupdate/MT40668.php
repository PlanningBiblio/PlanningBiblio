<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = REPLACE(`valeur`, 'Planning Biblio', 'Planno') WHERE `nom` ='Mail-Signature';";
