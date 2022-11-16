<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('legalNotices', 'textarea', '', 'Mentions légales (exemple : notice RGPD). La syntaxe markdown peut être utilisée pour la saisie.', 'Mentions légales', 10);";
