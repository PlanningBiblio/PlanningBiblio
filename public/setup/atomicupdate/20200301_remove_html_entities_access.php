<?php

$sql[] = "UPDATE `{$dbprefix}acces` SET `categorie` = REPLACE(`categorie`, '&eacute;', 'é');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&eacute;', 'é');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&egrave;', 'è');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&apos;', \"'\");";
$sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&Eacute;', 'É');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&eacute;', 'é');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&egrave;', 'è');";
$sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&apos;', \"'\");";