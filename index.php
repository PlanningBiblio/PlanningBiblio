<?php
/**
Planning Biblio, Version 2.8.05
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
    require_once(__DIR__.'/setup/maj.php');
}
// Sinon, on continue
else {
    require_once(__DIR__.'/include/feries.php');
    require_once(__DIR__.'/plugins/plugins.php');
    if (isset($_SESSION['login_id'])) {
        require_once(__DIR__.'/include/cron.php');
    }
}

// Si pas de session, redirection vers la page d'authentification
if (empty($_SESSION['login_id'])) {
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
        include_once(__DIR__.'/authentification.php');
        exit;
    }
}

# Start using twigized script
$checker = new PlanningBiblio\LegacyCodeChecker();
if ($checker->isTwigized($page)) {
    include(__DIR__.'/'.$page);
    exit;
}

include(__DIR__.'/include/header.php');
if ($show_menu) {
    include(__DIR__.'/include/menu.php');
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
    include(__DIR__.'/'.$page);
} else {
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
}
if ($show_menu) {
    include(__DIR__.'/include/footer.php');
}
