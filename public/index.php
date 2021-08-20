<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : index.php
Création : mai 2011
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
include_once(__DIR__.'/init_menu.php');
include_once('init_templates.php');

require_once(__DIR__.'/include/feries.php');
if (isset($_SESSION['login_id'])) {
    require_once(__DIR__.'/include/cron.php');
}

// Si pas de session, redirection vers la page d'authentification
if (empty($_SESSION['login_id'])) {
    // Action executée dans un popup alors que la session a été perdue, on affiche
    if (!$show_menu) {
        echo "<div style='margin:60px 30px;'>\n";
        echo "<center>\n";
        echo "Votre session a expiré.<br/><br/>\n";
        echo "<a href='{$config['URL']}/login' target='_top'>Cliquez ici pour vous reconnecter</a>\n";
        echo "<center></div>\n";
        exit;
    } else {
        // Session perdue, on affiche la page d'authentification
        $noCAS = false;
        if (isset($_GET['noCAS'])) {
            $noCAS = true;
            unset($_GET['noCAS']);
        }

        $login_params = array();
        $login_params['redirURL'] = 'index.php?' . http_build_query($_GET);
        if ($noCAS) {
            $login_params['noCAS'] = '';
        }
        // FIXME Here, $config['URL'] should not be set yet.
        $login_url = "{$config['URL']}/login?" . http_build_query($login_params);
        header("Location: $login_url");
        exit;
    }
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
<form name='Config' action='#' method='get'>
  <input type='hidden' name='granularity' id='granularity' value="{$config['Granularite']}" />
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
