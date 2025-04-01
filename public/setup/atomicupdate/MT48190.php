<?php

// Menus
$sql[] = "INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES (50, 90, 'Configuration technique', '/config/technical', NULL);";
$sql[] = "UPDATE `{$dbprefix}menu` SET `titre` = 'Configuration fonctionnelle' WHERE `url` = '/config';";

// Add column technical to config table
$sql[] = "ALTER TABLE `{$dbprefix}config` ADD COLUMN IF NOT EXISTS `technical` TINYINT(1) NOT NULL DEFAULT 0 AFTER `valeurs`;";

// Add OpenID Connect params
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-Provider', 'text', '', 'OpenID Connect Provider.', 'OpenID Connect', '', 1, 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-CACert', 'text', '', 'Path to the OpenID Connect CA Certificate.', 'OpenID Connect', '', 1, 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-ClientID', 'text', '', 'OpenID Connect Client ID (not to be confused with Secret ID).', 'OpenID Connect', '', 1, 30);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-ClientSecret', 'text', '', 'OpenID Connect Secret Value (not to be confused with Secret ID).', 'OpenID Connect', '', 1, 40);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-LoginAttribute', 'text', '', 'OpenID Connect Login Attribute.', 'OpenID Connect', '', 1, 50);";

// Add MS Graph params
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-TenantID', 'text', '', 'MS Graph Tenant ID.', 'Microsoft Graph API', '', 1, 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-ClientID', 'text', '', 'MS Graph Client ID (not to be confused with Secret ID).', 'Microsoft Graph API', '', 1, 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-ClientSecret', 'text', '', 'MS Graph Secret Value (not to be confused with Secret ID).', 'Microsoft Graph API', '', 1, 30);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-LoginSuffix', 'text', '', 'Suffix that must be added to the Planno login to link with the MS login. Optional, empty by default.', 'Microsoft Graph API', '', 1, 40);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-IgnoredStatuses', 'text', 'free;tentative', 'List of statuses to ignore, separated by semicolons. Optional, \"free;tentative\" by default.', 'Microsoft Graph API', '', 1, 50);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-AbsenceReason', 'text', 'Office 365', 'Absence Reason to use for imported events. Optional, \"Outlook\" by default.', 'Microsoft Graph API', '', 1, 60);";

// Move other params
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'Authentification';";
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'CAS';";
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'ICS';";
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'LDAP';";
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'LDIF';";
$sql[] = "UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `categorie` = 'Messagerie';";

// Get params
$params = [
    ['OIDC_PROVIDER', 'OIDC-Provider'],
    ['OIDC_CACERT', 'OIDC-CACert'],
    ['OIDC_CLIENT_ID', 'OIDC-ClientID'],
    ['OIDC_CLIENT_SECRET', 'OIDC-ClientSecret'],
    ['OIDC_LOGIN_ATTRIBUTE', 'OIDC-LoginAttribute'],
    ['MS_GRAPH_TENANT_ID', 'MSGraph-TenantID'],
    ['MS_GRAPH_CLIENT_ID', 'MSGraph-ClientID'],
    ['MS_GRAPH_CLIENT_SECRET', 'MSGraph-ClientSecret'],
    ['MS_GRAPH_REASON_NAME', 'MSGraph-AbsenceReason'],
    ['MS_GRAPH_LOGIN_SUFFIX', 'MSGraph-LoginSuffix'],
    ['MS_GRAPH_IGNORED_STATUSES', 'MSGraph-IgnoredStatuses'],
];

foreach ($params as $param) {
    $env = $param[0];
    $conf = $param[1];

    if (!empty($_ENV[$env])) {
        $value = trim($_ENV[$env]);

        $db2 = new db();
        $db2->query("SELECT `valeur` FROM `{$dbprefix}config` WHERE `nom` = '$conf';");
        if (empty($db2->result[0]['valeur'])) {
            $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '$value' WHERE `nom` = '$conf';";
        }
    }
}
