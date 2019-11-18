<?php
$sql[] = "INSERT INTO `{$dbprefix}menu` (niveau1, niveau2, titre, url) VALUES(30, 95, 'Semaines fixes', '/statedweek')";
$sql[] = "INSERT INTO `{$dbprefix}acces` (nom, groupe_id, page, ordre) VALUES('Semaine fixes', 100, '/statedweek', 0)";
