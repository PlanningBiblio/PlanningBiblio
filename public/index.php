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

use Symfony\Component\HttpFoundation\Request;

include_once('init.php');
include_once(__DIR__.'/init_menu.php');
include_once('init_templates.php');
include_once(__DIR__ . '/../init/common.php');

require_once(__DIR__.'/include/feries.php');
if (isset($_SESSION['login_id'])) {
    require_once(__DIR__.'/include/cron.php');
}

$base_url = plannoBaseUrl($request);

// Si pas de session, redirection vers la page d'authentification
if (empty($_SESSION['login_id'])) {
    // Action executée dans un popup alors que la session a été perdue, on affiche
    if (!$show_menu) {
        echo "<div style='margin:60px 30px;'>\n";
        echo "<center>\n";
        echo "Votre session a expiré.<br/><br/>\n";
        echo "<a href='$base_url/login' target='_top'>Cliquez ici pour vous reconnecter</a>\n";
        echo "<center></div>\n";
        exit;
    } else {
        // Session perdue, on affiche la page d'authentification
        $noCAS = false;
        if (isset($_GET['noCAS'])) {
            $noCAS = true;
            unset($_GET['noCAS']);
        }

        $get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        if (!empty($get['ticket'])) {
            $ticket = $get['ticket'];
            unset($get['ticket']);
        }

        $login_params = array();
        if (is_array($get)) {
            $login_params['redirURL'] = 'index.php?' . http_build_query($get);
        } else {
            $login_params['redirURL'] = 'index.php';
        }

        if (isset($ticket)) {
            $login_params['ticket'] = $ticket;
        }

        if ($noCAS) {
            $login_params['noCAS'] = '';
        }
        $login_url = "$base_url/login?" . http_build_query($login_params);
        header("Location: $login_url");
        exit;
    }
}

if (!$page && $path == '/') {
    header("Location: $base_url/index");
    exit();
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
