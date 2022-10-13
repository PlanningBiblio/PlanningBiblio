<?php

$sql[] = "DELETE s1 FROM `{$dbprefix}select_services` s1 INNER JOIN `{$dbprefix}select_services` s2 WHERE s1.id < s2.id AND s1.valeur = s2.valeur";
$sql[] = "DELETE s1 FROM `{$dbprefix}select_statuts` s1 INNER JOIN `{$dbprefix}select_statuts` s2 WHERE s1.id < s2.id AND s1.valeur = s2.valeur";
