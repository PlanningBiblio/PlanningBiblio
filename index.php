<?php
/**
Planning Biblio, Version 2.8.03
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : index.php
Création : mai 2011
Dernière modification : 29 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page principale,
Vérifie si la base de données doit être mise à jour, inclus les pages de configuration et
de fonctions communes, vérifie les droits à la page demandée en argument et l'inclus si l'utilisateur
est autorisé à la consulter

Inclut au départ les fichiers config.php, doctype.php et header.php
Inclut à la fin le fichier footer.php
*/

include_once('init.php');
include_once('init_menu.php');
include_once('init_templates.php');

// Vérification de la version de la base de données
// Si la version est différente, mise à jour de la base de données
if ($version!=$config['Version']) {
    include "setup/maj.php";
}
// Sinon, on continue
else {
    include "include/feries.php";
    include "plugins/plugins.php";
    if (isset($_SESSION['login_id'])) {
        include "include/cron.php";
    }
}

// Si pas de session, redirection vers la page d'authentification
if (!array_key_exists("login_id", $_SESSION)) {
    // Action executée dans un popup alors que la session a été perdue, on affiche
    if (!$show_menu) {
        echo "<div style='margin:60px 30px;'>\n";
        echo "<center>\n";
        echo "Votre session a expiré.<br/><br/>\n";
        echo "<a href='authentification.php' target='_top'>Cliquez ici pour vous reconnecter</a>\n";
        echo "<center></div>\n";
        exit;
    } else {
        // Session perdue, on affiche la page d'authentification
        $redirURL="index.php?".$_SERVER['QUERY_STRING'];
        include_once "authentification.php";
        exit;
    }
}

# Start using twigized script
$checker = new PlanningBiblio\LegacyCodeChecker();
if ($checker->isTwigized($page)) {
    include $page;
    exit;
}

include "include/header.php";
if ($show_menu) {
    include "include/menu.php";
}

// Sécurité CSRFToken
echo <<<EOD
<form name='CSRFForm' action='#' method='get'>
<input type='hidden' name='CSRFSession' id='CSRFSession' value='$CSRFSession' />
</form>
EOD;

if ($content_planning) {
    echo "<div id='content-planning'>\n";
} else {
    echo "<div id='content'>\n";
}

if ($authorized) {
    include $page;
} else {
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
}
if ($menu) {
    include "include/footer.php";
}
