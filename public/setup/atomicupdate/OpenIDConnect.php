<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = 'SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect', `commentaires` = 'Méthode d\'authentification' WHERE `nom` = 'Auth-Mode';";


