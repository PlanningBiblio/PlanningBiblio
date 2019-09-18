<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/ajax.update.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Met à jour les fiches des agents sélectionnés à partir de la liste des agents (fichier personnel/index.php).

Ce script est appelé par la fonction JS personnel/js/index.js : agent_list
*/

use App\Model\Agent;

require_once(__DIR__.'/../include/config.php');
require_once(__DIR__ . '/../init_ajax.php');

if (!in_array(21, $_SESSION['droits'])) {
    echo json_encode('Accès refusé');
    exit;
}

// CSFR Protection
$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);
if ( !isset($_SESSION['oups']['CSRFToken']) or $CSRFToken != $_SESSION['oups']['CSRFToken']) {
    error_log("CSRF Token Exception {$_SERVER['SCRIPT_NAME']}");
    echo json_encode("CSRF Token Exception {$_SERVER['SCRIPT_NAME']}");
    exit;
}

// Selected agents
$list = filter_input(INPUT_POST, 'list', FILTER_SANITIZE_STRING);
$list = html_entity_decode($list, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$list = json_decode($list);

// Main tab
$actif = filter_input(INPUT_POST, 'actif', FILTER_SANITIZE_STRING);
$contrat = filter_input(INPUT_POST, 'contrat', FILTER_SANITIZE_STRING);
$heures_hebdo = filter_input(INPUT_POST, 'heures_hebdo', FILTER_SANITIZE_STRING);
$heures_travail = filter_input(INPUT_POST, 'heures_travail', FILTER_SANITIZE_STRING);
$service = filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING);
$statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_STRING);

$contrat = htmlentities($contrat, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$service = htmlentities($service, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$statut = htmlentities($statut, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);

// Skills tab
$postes = filter_input(INPUT_POST, 'postes', FILTER_SANITIZE_STRING);

if ($postes != '-1') {
        $postes = explode(',', $postes);
        $postes = json_encode($postes);
}

// Update DB
$agents = $entityManager->getRepository(Agent::class)->findById($list);

foreach ($agents as $agent) {
    // Main Tab
    if ($actif != '-1') {
        $agent->actif($actif);
    }

    if ($contrat != '-1') {
        $agent->categorie($contrat);
    }

    if ($heures_hebdo != '-1') {
        $agent->heures_hebdo($heures_hebdo);
    }

    if ($heures_travail != '-1') {
        $agent->heures_travail($heures_travail);
    }

    if ($service != '-1') {
        $agent->service($service);
    }

    if ($statut != '-1') {
        $agent->statut($statut);
    }

    // Skills tab
    if ($postes != '-1') {
        $agent->postes($postes);
    }

    $entityManager->persist($agent);

}
$entityManager->flush();

echo json_encode('ok');