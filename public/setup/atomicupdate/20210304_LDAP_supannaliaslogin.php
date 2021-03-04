<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'supannaliaslogin' WHERE `valeur` = 'supannAliasLogin' AND `nom` = 'LDAP-ID-Attribute';";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = 'uid,samaccountname,supannaliaslogin' WHERE `nom` = 'LDAP-ID-Attribute';";
