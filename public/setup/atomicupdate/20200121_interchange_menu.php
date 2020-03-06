<?php
$sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES(35, 0, 'Échanges de poste', '/interchange', 'config=statedweek_enabled')";
$sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES(35, 5, 'Voir les échanges', '/interchange', 'config=statedweek_enabled')";
$sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES(35, 10, 'Demande d\'échange', '/interchange/add', 'config=statedweek_enabled')";
$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `page`, `ordre`) VALUES('Échanges de poste', 100, '/interchange', 0)";
$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `page`, `ordre`) VALUES('Demande d\'échange', 100, '/interchange/add', 0)";

$sql[] = "CREATE TABLE `{$dbprefix}interchanges` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    planning INT(11) NOT NULL,
    requester int(11) NOT NULL,
    requested_on datetime NOT NULL,
    requester_time int(11) NOT NULL,
    asked int(11) NOT NULL,
    asked_time int(11) NOT NULL,
    accepted_by int(11) NULL DEFAULT 0,
    accepted_on datetime NULL,
    rejected_by int(11) NULL DEFAULT 0,
    rejected_on datetime NULL,
    validated_by int(11) NULL DEFAULT 0,
    validated_on datetime NULL,
    status ENUM ('ASKED','ACCEPTED', 'REJECTED', 'VALIDATED') NOT NULL DEFAULT 'ASKED',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `groupe`, `ordre`, `categorie`) VALUES('Échanges de poste', 1301, 'Validation des échanges de postes', 135, 'Semaines fixes')";

$sql[] = "UPDATE `{$dbprefix}menu` SET `titre` = 'Échanges' WHERE `titre` = 'Échanges de poste'";
